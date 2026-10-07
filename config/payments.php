<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */

    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Manual methods
    |--------------------------------------------------------------------------
    |
    | Offline / manual payment channels. Payments are confirmed manually by
    | the site owner; each method carries the account details a customer
    | should pay into. These always remain available as a fallback, even when
    | real gateways are enabled.
    |
    */

    'methods' => [
        'bkash' => [
            'label' => 'bKash',
            'type' => 'Mobile Money',
            'account' => '+880 1710-000000',
            'enabled' => true,
            'instructions' => 'Send the total to the bKash number above and quote your invoice number as the payment reference.',
        ],
        'nagad' => [
            'label' => 'Nagad',
            'type' => 'Mobile Money',
            'account' => '+880 1810-000000',
            'enabled' => true,
            'instructions' => 'Send the total to the Nagad number above and quote your invoice number as the payment reference.',
        ],
        'binance_p2p' => [
            'label' => 'Binance P2P (USDT)',
            'type' => 'Crypto / P2P',
            'account' => 'Binance UID: 123456789',
            'enabled' => true,
            'instructions' => 'Send USDT via Binance P2P to the Binance UID above and quote your invoice number as the payment reference.',
        ],
        'paypal' => [
            'label' => 'PayPal',
            'type' => 'Online',
            'account' => 'payments@scifi-hud.test',
            'enabled' => true,
            'instructions' => 'Pay to the PayPal address above and quote your invoice number as the payment reference.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment gateways
    |--------------------------------------------------------------------------
    |
    | Real, server-side payment gateways. Each gateway is fully driven by its
    | own driver (app/Payments/Drivers) which talks to the provider's REST API
    | and redirects the customer to the hosted checkout page. Credentials are
    | entered per mode (test/live) from Admin → Settings → Payments and stored
    | in settings, seeded from these defaults. A gateway only appears at
    | checkout when it is enabled AND has credentials for the active mode.
    |
    */

    'gateways' => [
        'stripe' => [
            'label' => 'Stripe',
            'type' => 'Card / Online',
            'enabled' => false,
            'mode' => 'test',
            'modes' => ['test', 'live'],
            'instructions' => 'You will be redirected to Stripe to pay securely with your card.',
            'driver' => \App\Payments\Drivers\StripeGateway::class,
            'fields' => [
                ['key' => 'secret_key', 'label' => 'Secret key (sk_test_ / sk_live_)'],
                ['key' => 'webhook_secret', 'label' => 'Webhook signing secret (whsec_)', 'optional' => true],
            ],
            'credentials' => ['test' => [], 'live' => []],
        ],
        'bkash' => [
            'label' => 'bKash',
            'type' => 'Mobile Money',
            'enabled' => false,
            'mode' => 'sandbox',
            'modes' => ['sandbox', 'live'],
            'instructions' => 'You will be redirected to bKash to complete the payment from your bKash app.',
            'driver' => \App\Payments\Drivers\BkashGateway::class,
            'fields' => [
                ['key' => 'app_key', 'label' => 'App key'],
                ['key' => 'app_secret', 'label' => 'App secret'],
                ['key' => 'username', 'label' => 'Merchant username'],
                ['key' => 'password', 'label' => 'Merchant password'],
            ],
            'credentials' => ['sandbox' => [], 'live' => []],
        ],
        'razorpay' => [
            'label' => 'Razorpay',
            'type' => 'Card / UPI / Online',
            'enabled' => false,
            'mode' => 'test',
            'modes' => ['test', 'live'],
            'instructions' => 'You will be redirected to Razorpay to complete the payment.',
            'driver' => \App\Payments\Drivers\RazorpayGateway::class,
            'fields' => [
                ['key' => 'key_id', 'label' => 'Key ID'],
                ['key' => 'key_secret', 'label' => 'Key secret'],
            ],
            'credentials' => ['test' => [], 'live' => []],
        ],
        'sslcommerz' => [
            'label' => 'SSLCommerz',
            'type' => 'Card / bKash / Online (BD)',
            'enabled' => false,
            'mode' => 'sandbox',
            'modes' => ['sandbox', 'live'],
            'instructions' => 'You will be redirected to SSLCommerz to complete the payment.',
            'driver' => \App\Payments\Drivers\SslcommerzGateway::class,
            'fields' => [
                ['key' => 'store_id', 'label' => 'Store ID'],
                ['key' => 'store_password', 'label' => 'Store password'],
            ],
            'credentials' => ['sandbox' => [], 'live' => []],
        ],
        'amarpay' => [
            'label' => 'AmarPay',
            'type' => 'Card / Mobile Banking (BD)',
            'enabled' => false,
            'mode' => 'sandbox',
            'modes' => ['sandbox', 'live'],
            'instructions' => 'You will be redirected to AmarPay to complete the payment.',
            'driver' => \App\Payments\Drivers\AmarpayGateway::class,
            'fields' => [
                ['key' => 'store_id', 'label' => 'Store ID'],
                ['key' => 'signature_key', 'label' => 'Signature key'],
            ],
            'credentials' => ['sandbox' => [], 'live' => []],
        ],
        'binance' => [
            'label' => 'Binance Pay (B2B)',
            'type' => 'Crypto / Online',
            'enabled' => false,
            'mode' => 'sandbox',
            'modes' => ['sandbox', 'live'],
            'instructions' => 'You will be redirected to Binance Pay to complete your payment in crypto.',
            'driver' => \App\Payments\Drivers\BinancePayGateway::class,
            'fields' => [
                ['key' => 'merchant_id', 'label' => 'Merchant ID'],
                ['key' => 'api_key', 'label' => 'API key (Certificate SN)'],
                ['key' => 'secret_key', 'label' => 'API secret key'],
            ],
            'credentials' => ['sandbox' => [], 'live' => []],
        ],
    ],

];
