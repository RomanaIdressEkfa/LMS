<?php

namespace App\Services\Payments;

use App\Services\Payments\Drivers\BankGateway;
use App\Services\Payments\Drivers\MobileWalletGateway;
use App\Services\Payments\Drivers\StripeGateway;
use App\Services\Payments\Drivers\TestGateway;
use InvalidArgumentException;

/**
 * Resolves a gateway key (e.g. "stripe") to its driver. Register new gateways
 * here — nothing else in the app needs to know how each one works.
 */
class PaymentManager
{
    /** @var array<string, class-string<PaymentGatewayContract>> */
    protected array $drivers = [
        'test' => TestGateway::class,
        'bank' => BankGateway::class,
        'stripe' => StripeGateway::class,
        // Bangladeshi mobile wallets (send-money flow)
        'bkash' => MobileWalletGateway::class,
        'nagad' => MobileWalletGateway::class,
        'rocket' => MobileWalletGateway::class,
    ];

    public function driver(string $key): PaymentGatewayContract
    {
        if (! isset($this->drivers[$key])) {
            throw new InvalidArgumentException("No payment driver registered for [{$key}].");
        }

        return app($this->drivers[$key]);
    }

    public function supports(string $key): bool
    {
        return isset($this->drivers[$key]);
    }
}
