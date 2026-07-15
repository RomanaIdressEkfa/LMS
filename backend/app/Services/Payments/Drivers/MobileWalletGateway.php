<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\PaymentResult;

/**
 * Bangladeshi mobile wallets — bKash, Nagad, Rocket.
 *
 * Uses the common "personal / send-money" flow small merchants use: the buyer
 * sends money to the merchant's wallet number, then the admin confirms the
 * order (via the approve endpoint). The wallet number + instructions come from
 * the gateway's saved credentials, so each wallet is configured independently.
 *
 * (A full automated API — e.g. bKash Tokenized Checkout — can later be added as
 * its own driver without touching the rest of the app.)
 */
class MobileWalletGateway implements PaymentGatewayContract
{
    public function charge(Order $order, PaymentGateway $config): PaymentResult
    {
        $creds = $config->credentials ?? [];
        $number = $creds['account_number'] ?? 'the merchant number';
        $type = $creds['account_type'] ?? 'Personal';

        $message = "Send ৳{$order->amount} to our {$config->name} ({$type}) number: {$number}. "
            ."Use your order reference {$order->reference} as the reference, then your access "
            ."is unlocked once we confirm the payment.";

        return PaymentResult::pending($message);
    }

    public function verify(Order $order, PaymentGateway $config, array $payload): bool
    {
        // Confirmation is manual (admin approves) for the send-money flow.
        return false;
    }
}
