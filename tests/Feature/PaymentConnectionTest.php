<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Payments as SettingsPayments;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentConnectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_test_connection_button_is_in_configure_modal(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->assertSee('Test connection');
    }

    public function test_test_connection_returns_failure_when_credentials_missing(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->call('testConnection', 'stripe')
            ->assertSet('connectionResult.success', false);
    }

    public function test_test_connection_on_stripe_with_fake_balance_api(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Http::fake([
            'api.stripe.com/v1/balance' => Http::response([
                'available' => [['amount' => 10000, 'currency' => 'usd']],
                'pending' => [],
            ]),
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->set('payments.gateways.stripe.enabled', true)
            ->set('payments.gateways.stripe.mode', 'test')
            ->set('payments.gateways.stripe.credentials.test.secret_key', 'sk_test_valid')
            ->call('testConnection', 'stripe')
            ->assertSet('connectionResult.success', true)
            ->assertSet('connectionResult.mode', 'test');
    }

    public function test_test_connection_on_stripe_with_fake_failure(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Http::fake([
            'api.stripe.com/v1/balance' => Http::response([
                'error' => ['type' => 'invalid_request_error', 'message' => 'Invalid API Key provided'],
            ], 401),
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->set('payments.gateways.stripe.enabled', true)
            ->set('payments.gateways.stripe.mode', 'test')
            ->set('payments.gateways.stripe.credentials.test.secret_key', 'sk_test_invalid')
            ->call('testConnection', 'stripe')
            ->assertSet('connectionResult.success', false)
            ->assertSet('connectionResult.mode', 'test');
    }

    public function test_test_connection_on_razorpay_with_fake_api(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Http::fake([
            'api.razorpay.com/v1/payments*' => Http::response([
                'items' => [],
                'count' => 0,
            ]),
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'razorpay')
            ->set('payments.gateways.razorpay.enabled', true)
            ->set('payments.gateways.razorpay.mode', 'test')
            ->set('payments.gateways.razorpay.credentials.test.key_id', 'rzp_test_valid')
            ->set('payments.gateways.razorpay.credentials.test.key_secret', 'valid_secret')
            ->call('testConnection', 'razorpay')
            ->assertSet('connectionResult.success', true);
    }

    public function test_test_connection_on_bkash_with_fake_api(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Http::fake([
            'tokenized.sandbox.bka.sh/*/tokenized/checkout/token/create' => Http::response([
                'id_token' => 'test_id_token_123',
                'token_type' => 'Bearer',
            ]),
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'bkash')
            ->set('payments.gateways.bkash.enabled', true)
            ->set('payments.gateways.bkash.mode', 'sandbox')
            ->set('payments.gateways.bkash.credentials.sandbox.app_key', 'test_app_key')
            ->set('payments.gateways.bkash.credentials.sandbox.app_secret', 'test_app_secret')
            ->call('testConnection', 'bkash')
            ->assertSet('connectionResult.success', true);
    }

    public function test_test_connection_on_sslcommerz_with_fake_api(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Http::fake([
            'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/test',
            ]),
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'sslcommerz')
            ->set('payments.gateways.sslcommerz.enabled', true)
            ->set('payments.gateways.sslcommerz.mode', 'sandbox')
            ->set('payments.gateways.sslcommerz.credentials.sandbox.store_id', 'test_store')
            ->set('payments.gateways.sslcommerz.credentials.sandbox.store_password', 'test_pass')
            ->call('testConnection', 'sslcommerz')
            ->assertSet('connectionResult.success', true);
    }

    public function test_test_connection_on_amarpay_with_fake_api(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Http::fake([
            'sandbox.aamarpay.com/jsonpost.php' => Http::response([
                'status' => 'SUCCESS',
                'payment_url' => 'https://sandbox.aamarpay.com/test',
            ]),
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'amarpay')
            ->set('payments.gateways.amarpay.enabled', true)
            ->set('payments.gateways.amarpay.mode', 'sandbox')
            ->set('payments.gateways.amarpay.credentials.sandbox.store_id', 'test_store')
            ->set('payments.gateways.amarpay.credentials.sandbox.signature_key', 'test_sig')
            ->call('testConnection', 'amarpay')
            ->assertSet('connectionResult.success', true);
    }

    public function test_test_connection_resets_when_editing_another_gateway(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->call('testConnection', 'stripe')
            ->call('configure', 'razorpay')
            ->assertSet('connectionResult', null);
    }
}