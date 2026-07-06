<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Payment gateway management — toggle gateways and store credentials.
 */
class GatewayController extends Controller
{
    public function index()
    {
        return response()->json([
            'gateways' => PaymentGateway::orderBy('sort_order')->get(),
        ]);
    }

    public function toggle(int $id)
    {
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->update(['enabled' => ! $gateway->enabled]);

        return response()->json(['gateway' => $gateway]);
    }

    public function update(Request $request, int $id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        $data = $request->validate([
            'enabled' => ['boolean'],
            'test_mode' => ['boolean'],
            'currency' => ['string', 'size:3'],
            'credentials' => ['array'],
        ]);

        $gateway->update($data);

        return response()->json(['gateway' => $gateway]);
    }
}
