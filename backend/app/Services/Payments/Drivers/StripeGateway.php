<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\PaymentResult;
use Stripe\StripeClient;

/**
 * Real Stripe Checkout. Goes live as soon as an admin enables the gateway and
 * saves a secret key in its credentials. Uses hosted Checkout Sessions, so no
 * card data ever touches this server.
 */
class StripeGateway implements PaymentGatewayContract
{
    public function charge(Order $order, PaymentGateway $config): PaymentResult
    {
        $secret = $config->credentials['secret_key'] ?? null;
        if (! $secret) {
            return PaymentResult::failed('Stripe is not configured (missing secret key).');
        }

        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');

        try {
            $stripe = new StripeClient($secret);
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'client_reference_id' => $order->reference,
                'success_url' => "{$frontend}/dashboard/checkout/success?order={$order->reference}&session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "{$frontend}/dashboard/checkout/cancel?order={$order->reference}",
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($order->currency),
                        'unit_amount' => (int) round($order->amount * 100),
                        'product_data' => ['name' => $order->course->title],
                    ],
                ]],
            ]);

            $order->update(['transaction_id' => $session->id]);

            return PaymentResult::redirect($session->url);
        } catch (\Throwable $e) {
            return PaymentResult::failed('Stripe error: '.$e->getMessage());
        }
    }

    public function verify(Order $order, PaymentGateway $config, array $payload): bool
    {
        $secret = $config->credentials['secret_key'] ?? null;
        $sessionId = $payload['session_id'] ?? $order->transaction_id;
        if (! $secret || ! $sessionId) {
            return false;
        }

        try {
            $stripe = new StripeClient($secret);
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            return $session->payment_status === 'paid';
        } catch (\Throwable) {
            return false;
        }
    }
}
