<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\PaymentResult;

/**
 * Offline bank transfer. The order stays pending until an admin confirms the
 * transfer (payments.refund/manage), at which point the enrollment is created.
 */
class BankGateway implements PaymentGatewayContract
{
    public function charge(Order $order, PaymentGateway $config): PaymentResult
    {
        $instructions = $config->credentials['instructions']
            ?? 'Transfer the amount to the account shown, then wait for approval.';

        return PaymentResult::pending($instructions);
    }

    public function verify(Order $order, PaymentGateway $config, array $payload): bool
    {
        // Confirmation is manual (admin action), not an automated callback.
        return false;
    }
}
