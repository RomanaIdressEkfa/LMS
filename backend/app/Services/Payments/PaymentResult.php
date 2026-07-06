<?php

namespace App\Services\Payments;

/**
 * The outcome of initiating a charge. A driver either completes the payment
 * immediately (test/wallet), needs the user redirected to a hosted checkout
 * (Stripe/PayPal), or leaves the order pending manual confirmation (bank).
 */
class PaymentResult
{
    public function __construct(
        public string $status,          // completed | redirect | pending | failed
        public ?string $redirectUrl = null,
        public ?string $transactionId = null,
        public ?string $message = null,
    ) {}

    public static function completed(?string $txn = null): self
    {
        return new self('completed', transactionId: $txn);
    }

    public static function redirect(string $url): self
    {
        return new self('redirect', redirectUrl: $url);
    }

    public static function pending(?string $message = null): self
    {
        return new self('pending', message: $message);
    }

    public static function failed(string $message): self
    {
        return new self('failed', message: $message);
    }
}
