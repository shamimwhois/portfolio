<?php

namespace Tests\Feature;

use App\Livewire\Sections\Services;
use App\Models\Invoice;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\User;
use App\Payments\Drivers\AmarpayGateway;
use App\Payments\Drivers\BinancePayGateway;
use App\Payments\Drivers\BkashGateway;
use App\Payments\Drivers\RazorpayGateway;
use App\Payments\Drivers\SslcommerzGateway;
use App\Payments\Drivers\StripeGateway;
use App\Payments\AbstractGateway;
use App\Payments\GatewayManager;
use App\Payments\PaymentException;
use App\Payments\PaymentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function invoice(?string $method = 'stripe', ?string $gateway = 'stripe'): Invoice
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'phone' => '+8801700000000']);
        $tier = ServiceTier::query()->firstOrFail();

        return Invoice::query()->create([
            'user_id' => $user->id,
            'service_tier_id' => $tier->id,
            'invoice_no' => 'INV-GW-001',
            'amount' => 49.50,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => $method,
            'gateway' => $gateway,
            'purchased_at' => now(),
        ]);
    }

    public function test_stripe_creates_checkout_session(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'cs_test_abc',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_abc',
            ]),
        ]);

        $redirect = (new StripeGateway('stripe'))->start($this->invoice());

        $this->assertSame('https://checkout.stripe.com/c/pay/cs_test_abc', $redirect->url);
        $this->assertSame('cs_test_abc', $redirect->reference);

        Http::assertSent(function (Request $request) {
            $body = $request->data();

            return str_contains($request->url(), '/checkout/sessions')
                && $request->hasHeader('Authorization', 'Bearer sk_test_123')
                && $body['mode'] === 'payment'
                && $body['client_reference_id'] === 'INV-GW-001'
                && $body['line_items[0][price_data][unit_amount]'] === 4950;
        });
    }

    public function test_stripe_success_verifies_payment(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice();

        Http::fake([
            'api.stripe.com/*' => Http::response(['payment_status' => 'paid']),
        ]);

        $service = app(PaymentService::class);
        $service->complete($invoice, ['session_id' => 'cs_test_abc']);

        $this->assertTrue($invoice->fresh()->isPaid());
        $this->assertSame('cs_test_abc', $invoice->fresh()->gateway_reference);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_stripe_success_does_not_pay_unverified_session(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice();

        Http::fake(['api.stripe.com/*' => Http::response(['payment_status' => 'unpaid'])]);

        app(PaymentService::class)->complete($invoice, ['session_id' => 'cs_test_abc']);

        $this->assertTrue($invoice->fresh()->isPending());
    }

    public function test_stripe_webhook_with_valid_signature_marks_paid(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => 'whsec_test'], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice();

        Http::fake(['api.stripe.com/*' => Http::response(['payment_status' => 'paid'])]);

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_webhook', 'client_reference_id' => 'INV-GW-001', 'payment_status' => 'paid']],
        ]);

        $time = now()->timestamp;
        $signature = 't='.$time.',v1='.hash_hmac('sha256', $time.'.'.$payload, 'whsec_test');

        $request = \Illuminate\Http\Request::create(
            route('payment.webhook', ['gateway' => 'stripe']),
            'POST',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = app(\App\Http\Controllers\PaymentController::class)->webhook($request, 'stripe');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_stripe_webhook_rejects_bad_signature(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => 'whsec_test'], 'live' => []],
            ]],
        ]));

        $this->invoice();

        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => []]);

        $request = \Illuminate\Http\Request::create(
            route('payment.webhook', ['gateway' => 'stripe']),
            'POST',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => 't=1,v1=deadbeef', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = app(\App\Http\Controllers\PaymentController::class)->webhook($request, 'stripe');

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_bkash_tokenized_checkout_flow(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['bkash' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'app_key' => 'bkash-app-key',
                    'app_secret' => 'bkash-app-secret',
                    'username' => 'merchant',
                    'password' => 'secret',
                ], 'live' => []],
            ]],
        ]));

        Http::fake([
            'tokenized.sandbox.bka.sh/*/token/create' => Http::response(['id_token' => 'id-token-123', 'expires_in' => 3600]),
            'tokenized.sandbox.bka.sh/*/create' => Http::response([
                'paymentID' => 'TR00123ABC',
                'bkashURL' => 'https://tokenized.sandbox.bka.sh/pay/TR00123ABC',
                'statusCode' => '0000',
            ]),
            'tokenized.sandbox.bka.sh/*/execute' => Http::response([
                'paymentID' => 'TR00123ABC',
                'transactionStatus' => 'Completed',
                'trxID' => 'TRX123',
            ]),
        ]);

        $invoice = $this->invoice('bkash', 'bkash');

        $redirect = (new BkashGateway('bkash'))->start($invoice);

        $this->assertSame('https://tokenized.sandbox.bka.sh/pay/TR00123ABC', $redirect->url);
        $this->assertSame('TR00123ABC', $redirect->reference);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/tokenized/checkout/create')
            && $request->hasHeader('X-APP-Key', 'bkash-app-key')
            && $request['merchantInvoiceNumber'] === 'INV-GW-001');

        // Simulate the gateway callback then execute.
        $status = app(PaymentService::class)->complete($invoice, ['status' => 'success', 'paymentID' => 'TR00123ABC']);

        $this->assertSame('paid', $status);
        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_bkash_cancelled_callback_does_not_pay(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['bkash' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'app_key' => 'k', 'app_secret' => 's', 'username' => 'u', 'password' => 'p',
                ], 'live' => []],
            ]],
        ]));

        Http::fake();

        $invoice = $this->invoice('bkash', 'bkash');

        app(PaymentService::class)->complete($invoice, ['status' => 'cancel', 'paymentID' => 'TR00123ABC']);

        $this->assertTrue($invoice->fresh()->isPending());
    }

    public function test_razorpay_payment_link_flow(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['razorpay' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['key_id' => 'rzp_test', 'key_secret' => 'rzp_secret'], 'live' => []],
            ]],
        ]));

        Http::fake([
            'api.razorpay.com/v1/payment_links' => Http::response([
                'id' => 'plink_test_123',
                'short_url' => 'https://rzp.io/l/test123',
                'status' => 'created',
            ]),
            'api.razorpay.com/v1/payment_links/plink_test_123' => Http::response([
                'id' => 'plink_test_123',
                'status' => 'paid',
            ]),
        ]);

        $invoice = $this->invoice('razorpay', 'razorpay');

        $redirect = (new RazorpayGateway('razorpay'))->start($invoice);

        $this->assertSame('https://rzp.io/l/test123', $redirect->url);
        $this->assertSame('plink_test_123', $redirect->reference);

        Http::assertSent(function (Request $request) {
            $body = $request->data();

            return str_contains($request->url(), '/payment_links')
                && $request->hasHeader('Authorization')
                && $body['amount'] === 4950
                && $body['notes']['invoice_no'] === 'INV-GW-001';
        });

        app(PaymentService::class)->complete($invoice, ['razorpay_payment_link_id' => 'plink_test_123']);

        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_sslcommerz_checkout_and_validation(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['sslcommerz' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => ['store_id' => 'teststore', 'store_password' => 'testpass'], 'live' => []],
            ]],
        ]));

        Http::fake([
            'sandbox.sslcommerz.com/gwprocess/*' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/pay/ABC123',
            ]),
            'sandbox.sslcommerz.com/validator/*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'INV-GW-001',
            ]),
        ]);

        $invoice = $this->invoice('sslcommerz', 'sslcommerz');

        $redirect = (new SslcommerzGateway('sslcommerz'))->start($invoice);

        $this->assertSame('https://sandbox.sslcommerz.com/gwprocess/pay/ABC123', $redirect->url);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/gwprocess/v4/api.php')
            && $request['store_id'] === 'teststore'
            && $request['tran_id'] === 'INV-GW-001');

        app(PaymentService::class)->complete($invoice, ['val_id' => 'VAL123', 'tran_id' => 'INV-GW-001']);

        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_amarpay_checkout_and_verification(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['amarpay' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => ['store_id' => 'amarpaystore', 'signature_key' => 'sigkey123'], 'live' => []],
            ]],
        ]));

        Http::fake([
            'sandbox.aamarpay.com/jsonpost.php' => Http::response([
                'status' => 'SUCCESS',
                'payment_url' => 'https://sandbox.aamarpay.com/pay/XYZ789',
            ]),
            'sandbox.aamarpay.com/api/*' => Http::response(['status' => 'VALID']),
        ]);

        $invoice = $this->invoice('amarpay', 'amarpay');

        $redirect = (new AmarpayGateway('amarpay'))->start($invoice);

        $this->assertSame('https://sandbox.aamarpay.com/pay/XYZ789', $redirect->url);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/jsonpost.php')
            && $request['store_id'] === 'amarpaystore'
            && $request['tran_id'] === 'INV-GW-001');

        app(PaymentService::class)->complete($invoice, ['request_id' => 'INV-GW-001']);

        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_binance_creates_hosted_checkout_order(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        Http::fake([
            'bpay-pre.binanceapi.com/binancepay/openapi/v2/order' => Http::response([
                'status' => 'SUCCESS',
                'code' => '000000',
                'data' => [
                    'prepayId' => 'prepay123',
                    'transactionId' => 'txn123',
                    'checkoutUrl' => 'https://bpay.binance.com/web/pay/prepay123',
                    'status' => 'CREATED',
                ],
            ]),
        ]);

        $redirect = (new BinancePayGateway('binance'))->start($this->invoice('binance', 'binance'));

        $this->assertSame('https://bpay.binance.com/web/pay/prepay123', $redirect->url);
        $this->assertSame('prepay123', $redirect->reference);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/binancepay/openapi/v2/order')
                && $request->hasHeader('BinancePay-Certificate-SN', 'bnc-pay-api-key')
                && $request->hasHeader('BinancePay-Signature')
                && $request->hasHeader('BinancePay-Timestamp')
                && $request->hasHeader('BinancePay-Nonce')
                && str_contains($request->body(), '"merchantTradeNo":"INV-GW-001"')
                && str_contains($request->body(), '"totalFee":"49.50"');
        });
    }

    public function test_binance_success_queries_paid_order(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice('binance', 'binance');

        Http::fake([
            'bpay-pre.binanceapi.com/binancepay/openapi/v2/query-order' => Http::response([
                'status' => 'SUCCESS',
                'code' => '000000',
                'data' => ['status' => 'PAID', 'prepayId' => 'prepay123'],
            ]),
        ]);

        app(PaymentService::class)->complete($invoice, ['prepayId' => 'prepay123']);

        $this->assertTrue($invoice->fresh()->isPaid());
        $this->assertSame('prepay123', $invoice->fresh()->gateway_reference);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_binance_success_does_not_pay_unpaid_order(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice('binance', 'binance');

        Http::fake([
            'bpay-pre.binanceapi.com/binancepay/openapi/v2/query-order' => Http::response([
                'status' => 'SUCCESS',
                'code' => '000000',
                'data' => ['status' => 'CREATED', 'prepayId' => 'prepay123'],
            ]),
        ]);

        app(PaymentService::class)->complete($invoice, ['prepayId' => 'prepay123']);

        $this->assertTrue($invoice->fresh()->isPending());
    }

    public function test_binance_webhook_with_valid_signature_marks_paid(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice('binance', 'binance');

        Http::fake([
            'bpay-pre.binanceapi.com/binancepay/openapi/v2/query-order' => Http::response([
                'status' => 'SUCCESS',
                'code' => '000000',
                'data' => ['status' => 'PAID'],
            ]),
        ]);

        $payload = json_encode([
            'bizType' => 'PAY',
            'data' => [
                'merchantTradeNo' => 'INV-GW-001',
                'prepayId' => 'prepay_webhook',
                'transactionId' => 'txn_webhook',
                'status' => 'PAID',
                'currency' => 'USDT',
                'amount' => '49.50',
            ],
        ]);

        $signature = strtoupper(hash_hmac('sha512', $payload, 'bnc-pay-secret'));

        $request = \Illuminate\Http\Request::create(
            route('payment.webhook', ['gateway' => 'binance']),
            'POST',
            [],
            [],
            [],
            ['HTTP_BinancePay-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = app(\App\Http\Controllers\PaymentController::class)->webhook($request, 'binance');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_binance_webhook_rejects_bad_signature(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        $this->invoice('binance', 'binance');

        $payload = json_encode(['bizType' => 'PAY', 'data' => ['merchantTradeNo' => 'INV-GW-001', 'status' => 'PAID']]);

        $request = \Illuminate\Http\Request::create(
            route('payment.webhook', ['gateway' => 'binance']),
            'POST',
            [],
            [],
            [],
            ['HTTP_BinancePay-Signature' => 'DEADBEEF', 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = app(\App\Http\Controllers\PaymentController::class)->webhook($request, 'binance');

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_binance_test_connection_pings_api(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        Http::fake([
            'bpay-pre.binanceapi.com/binancepay/openapi/v2/query-order' => Http::response([
                'status' => 'FAIL',
                'code' => '400117', // order not found — credentials were accepted
                'message' => 'Order does not exist',
            ]),
        ]);

        $result = (new BinancePayGateway('binance'))->testConnection();

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Connected', $result['message']);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/binancepay/openapi/v2/query-order')
            && $request->hasHeader('BinancePay-Certificate-SN', 'bnc-pay-api-key')
            && $request->hasHeader('BinancePay-Signature'));
    }

    public function test_binance_test_connection_rejects_bad_credentials(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['binance' => [
                'enabled' => true,
                'mode' => 'sandbox',
                'credentials' => ['sandbox' => [
                    'merchant_id' => '7890123',
                    'api_key' => 'bnc-pay-api-key',
                    'secret_key' => 'bnc-pay-secret',
                ], 'live' => []],
            ]],
        ]));

        Http::fake([
            'bpay-pre.binanceapi.com/binancepay/openapi/v2/query-order' => Http::response([
                'status' => 'FAIL',
                'code' => '400001', // signature verification failed
                'message' => 'Signature verification failed',
            ]),
        ]);

        $result = (new BinancePayGateway('binance'))->testConnection();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Signature verification failed', $result['message']);
    }

    public function test_success_route_rejects_invalid_token(): void
    {
        $invoice = $this->invoice();

        $request = \Illuminate\Http\Request::create(
            route('payment.success', ['invoiceNo' => $invoice->invoice_no]),
            'GET',
            ['session_id' => 'cs_test_abc', 'token' => 'forged']
        );

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(\App\Http\Controllers\PaymentController::class)->success($request, $invoice->invoice_no);

        $this->assertTrue($invoice->fresh()->isPending());
    }

    public function test_cancel_route_rejects_invalid_token(): void
    {
        $invoice = $this->invoice('bkash', 'bkash');

        $request = \Illuminate\Http\Request::create(
            route('payment.cancel', ['invoiceNo' => $invoice->invoice_no]),
            'GET',
            ['token' => 'forged']
        );

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(\App\Http\Controllers\PaymentController::class)->cancel($request, $invoice->invoice_no);

        $this->assertSame('pending', $invoice->fresh()->status);
    }

    public function test_unconfigured_gateway_throws(): void
    {
        // No credentials at all → stripe not available.
        $this->expectException(PaymentException::class);

        (new StripeGateway('stripe'))->start($this->invoice());
    }

    public function test_gateway_manager_available_lists_configured_gateways(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_1', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        $this->assertSame(['stripe'], app(GatewayManager::class)->available());
    }

    public function test_success_route_marks_paid_and_renders_result(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        $invoice = $this->invoice();

        Http::fake(['api.stripe.com/*' => Http::response(['payment_status' => 'paid'])]);

        $request = \Illuminate\Http\Request::create(
            route('payment.success', ['invoiceNo' => $invoice->invoice_no]),
            'GET',
            ['session_id' => 'cs_test_abc', 'token' => AbstractGateway::signature($invoice)]
        );

        $view = app(\App\Http\Controllers\PaymentController::class)->success($request, $invoice->invoice_no);

        $html = $view->render();
        $this->assertStringContainsString('Payment successful', $html);
        $this->assertStringContainsString('INV-GW-001', $html);

        $this->assertTrue($invoice->fresh()->isPaid());
    }

    public function test_cancel_route_marks_cancelled(): void
    {
        $invoice = $this->invoice('bkash', 'bkash');

        $request = \Illuminate\Http\Request::create(
            route('payment.cancel', ['invoiceNo' => $invoice->invoice_no]),
            'GET',
            ['token' => AbstractGateway::signature($invoice)]
        );

        $view = app(\App\Http\Controllers\PaymentController::class)->cancel($request, $invoice->invoice_no);

        $html = $view->render();
        $this->assertStringContainsString('Payment cancelled', $html);

        $this->assertSame('cancelled', $invoice->fresh()->status);
    }

    public function test_full_gateway_checkout_via_livewire_redirects(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'cs_test_live',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_live',
            ]),
        ]);

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'stripe')
            ->call('confirmPurchase')
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_live');
    }

    public function test_gateway_failure_falls_back_to_manual_message(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => [
                'enabled' => true,
                'mode' => 'test',
                'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
            ]],
        ]));

        Http::fake(['api.stripe.com/*' => Http::response(['error' => 'invalid_api_key'], 401)]);

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'stripe')
            ->call('confirmPurchase')
            ->assertHasNoErrors()
            ->assertSee('temporarily unavailable');

        $this->assertSame(0, Invoice::query()->where('user_id', $customer->id)->count());
    }
}
