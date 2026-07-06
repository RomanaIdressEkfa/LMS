<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\PaymentResult;
use Illuminate\Support\Str;

/**
 * Sandbox gateway for development & demos. Approves payment instantly so the
 * whole checkout → enrollment loop can be exercised without real credentials.
 * Never enable this in production.
 */
class TestGateway implements PaymentGatewayContract
{
    public function charge(Order $order, PaymentGateway $config): PaymentResult
    {
        return PaymentResult::completed('TEST-'.strtoupper(Str::random(12)));
    }

    public function verify(Order $order, PaymentGateway $config, array $payload): bool
    {
        return true;
    }
}
