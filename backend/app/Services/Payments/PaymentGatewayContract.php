<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentGateway;

/**
 * Every payment gateway driver implements this. Adding a new gateway = adding
 * one class + registering it in PaymentManager. The rest of the app never
 * changes, which is what makes gateways cleanly pluggable & toggleable.
 */
interface PaymentGatewayContract
{
    /** Begin payment for an order. */
    public function charge(Order $order, PaymentGateway $config): PaymentResult;

    /** Verify a returning payment (callback/webhook) and return true if paid. */
    public function verify(Order $order, PaymentGateway $config, array $payload): bool;
}
