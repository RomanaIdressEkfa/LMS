<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private PaymentManager $payments) {}

    /** Gateways a buyer can choose from (enabled + supported by a driver). */
    public function gateways()
    {
        $gateways = PaymentGateway::where('enabled', true)
            ->orderBy('sort_order')
            ->get(['id', 'key', 'name', 'currency'])
            ->filter(fn ($g) => $this->payments->supports($g->key))
            ->values();

        return response()->json(['gateways' => $gateways]);
    }

    /** Start a purchase for a course with the chosen gateway. */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        $data = $request->validate([
            'gateway' => ['required', 'string'],
        ]);

        if ($course->status !== 'published') {
            return response()->json(['message' => 'This course is not available.'], 422);
        }
        if ($course->is_free) {
            return response()->json(['message' => 'This course is free — just enroll.'], 422);
        }
        if ($course->isEnrolled($user)) {
            return response()->json(['message' => 'You already own this course.'], 409);
        }

        $config = PaymentGateway::where('key', $data['gateway'])->where('enabled', true)->first();
        if (! $config || ! $this->payments->supports($config->key)) {
            return response()->json(['message' => 'That payment method is not available.'], 422);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'gateway' => $config->key,
            'amount' => $course->price,
            'currency' => $config->currency,
            'status' => 'pending',
        ]);

        $result = $this->payments->driver($config->key)->charge($order, $config);

        return match ($result->status) {
            'completed' => $this->fulfill($order, $result->transactionId),
            'redirect' => response()->json(['status' => 'redirect', 'redirect_url' => $result->redirectUrl, 'order' => $order->reference]),
            'pending' => response()->json(['status' => 'pending', 'message' => $result->message, 'order' => $order->reference], 202),
            default => tap(
                response()->json(['status' => 'failed', 'message' => $result->message], 422),
                fn () => $order->update(['status' => 'failed'])
            ),
        };
    }

    /** Called when the user returns from a hosted checkout (e.g. Stripe success). */
    public function confirm(Request $request, string $reference)
    {
        $order = Order::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->isPaid()) {
            return response()->json(['status' => 'paid', 'order' => $order]);
        }

        $config = PaymentGateway::where('key', $order->gateway)->firstOrFail();
        $paid = $this->payments->driver($order->gateway)->verify($order, $config, $request->all());

        if (! $paid) {
            return response()->json(['status' => 'unpaid', 'message' => 'Payment not confirmed yet.'], 402);
        }

        return $this->fulfill($order, $request->input('session_id'));
    }

    /** Admin manually confirms an offline (bank) order. */
    public function approve(Request $request, Order $order)
    {
        if ($order->isPaid()) {
            return response()->json(['message' => 'Order already paid.'], 409);
        }

        return $this->fulfill($order, 'MANUAL-'.$request->user()->id);
    }

    /** The buyer's order history. */
    public function myOrders(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('course:id,title,slug')
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Mark an order paid and create the enrollment (idempotent).
     */
    private function fulfill(Order $order, ?string $txn = null)
    {
        DB::transaction(function () use ($order, $txn) {
            $order->update([
                'status' => 'paid',
                'transaction_id' => $txn ?? $order->transaction_id,
                'paid_at' => now(),
            ]);

            Enrollment::firstOrCreate(
                ['user_id' => $order->user_id, 'course_id' => $order->course_id],
                ['amount_paid' => $order->amount, 'source' => 'purchase'],
            );
        });

        return response()->json([
            'status' => 'completed',
            'message' => 'Payment successful — you are enrolled!',
            'order' => $order->fresh()->load('course:id,title,slug'),
        ]);
    }
}
