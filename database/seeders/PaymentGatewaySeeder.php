<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Gateway catalog. All disabled by default — the admin enables the ones
     * they want and pastes in credentials.
     */
    public const GATEWAYS = [
        ['key' => 'test',       'name' => 'Test (Sandbox)', 'currency' => 'USD'],
        // Bangladeshi mobile wallets
        ['key' => 'bkash',      'name' => 'bKash',       'currency' => 'BDT'],
        ['key' => 'nagad',      'name' => 'Nagad',       'currency' => 'BDT'],
        ['key' => 'rocket',     'name' => 'Rocket',      'currency' => 'BDT'],
        ['key' => 'stripe',     'name' => 'Stripe',      'currency' => 'USD'],
        ['key' => 'paypal',     'name' => 'PayPal',      'currency' => 'USD'],
        ['key' => 'razorpay',   'name' => 'Razorpay',    'currency' => 'INR'],
        ['key' => 'sslcommerz', 'name' => 'SSLCommerz',  'currency' => 'BDT'],
        ['key' => 'paddle',     'name' => 'Paddle',      'currency' => 'USD'],
        ['key' => 'flutterwave','name' => 'Flutterwave', 'currency' => 'NGN'],
        ['key' => 'mollie',     'name' => 'Mollie',      'currency' => 'EUR'],
        ['key' => 'bank',       'name' => 'Bank Transfer','currency' => 'USD'],
    ];

    public function run(): void
    {
        foreach (self::GATEWAYS as $i => $g) {
            PaymentGateway::updateOrCreate(
                ['key' => $g['key']],
                array_merge($g, [
                    // The sandbox gateway ships enabled so paid checkout works
                    // out of the box in local/dev; real gateways start off.
                    'enabled' => $g['key'] === 'test',
                    'test_mode' => true,
                    'sort_order' => $i,
                ]),
            );
        }
    }
}
