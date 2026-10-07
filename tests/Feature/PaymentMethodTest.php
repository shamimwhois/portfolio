<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Panel\Invoices;
use App\Livewire\Sections\Packages;
use App\Livewire\Sections\Services;
use App\Models\Invoice;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\TechPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_all_supported_methods_are_configured(): void
    {
        $this->assertSame(
            ['bkash', 'nagad', 'binance_p2p', 'paypal'],
            array_keys(config('payments.methods'))
        );

        foreach (config('payments.methods') as $key => $method) {
            $this->assertArrayHasKey('label', $method);
            $this->assertArrayHasKey('account', $method);
            $this->assertArrayHasKey('instructions', $method);
        }
    }

    #[DataProvider('serviceMethodsProvider')]
    public function test_service_purchase_with_each_payment_method(string $method, string $label): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', $method)
            ->call('confirmPurchase')
            ->assertHasNoErrors()
            ->assertSee('Plan '.$tier->name.' requested via '.$label);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'payment_method' => $method,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
    }

    #[DataProvider('packageMethodsProvider')]
    public function test_package_purchase_with_each_payment_method(string $method, string $label): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Packages::class)
            ->call('purchase', $package->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', $method)
            ->call('confirmPurchase')
            ->assertHasNoErrors()
            ->assertSee('Package '.$package->title.' requested via '.$label);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'tech_package_id' => $package->id,
            'payment_method' => $method,
            'status' => 'pending',
        ]);
    }

    public function test_purchase_without_payment_method_is_rejected(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->call('confirmPurchase')
            ->assertHasErrors(['paymentMethod']);

        $this->assertSame(0, Invoice::query()->where('user_id', $customer->id)->count());
    }

    public function test_purchase_via_enabled_gateway_method_redirects(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => [
                'stripe' => [
                    'enabled' => true,
                    'mode' => 'test',
                    'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
                ],
            ],
        ]));

        \Illuminate\Support\Facades\Http::fake([
            'api.stripe.com/*' => \Illuminate\Support\Facades\Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
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
            ->assertHasNoErrors()
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'payment_method' => 'stripe',
            'gateway' => 'stripe',
            'gateway_reference' => 'cs_test_123',
            'status' => 'pending',
        ]);
    }

    public function test_purchase_with_unknown_method_is_rejected(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->set('paymentMethod', 'swift')
            ->call('confirmPurchase')
            ->assertHasErrors(['paymentMethod']);

        $this->assertSame(0, Invoice::query()->where('user_id', $customer->id)->count());
    }

    public function test_checkout_modal_shows_stepper_summary_and_payment_methods(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        // Gateways are disabled by default, so enable + configure Stripe for the
        // gateway card anatomy assertions (Secure gateway badge + redirect hint).
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => [
                'stripe' => [
                    'enabled' => true,
                    'mode' => 'test',
                    'credentials' => ['test' => ['secret_key' => 'sk_test_123', 'webhook_secret' => ''], 'live' => []],
                ],
            ],
        ]));

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->assertSee('Secure checkout')
            ->assertSee('Order summary')
            ->assertSee('Total to pay')
            ->assertSee('Order notes')
            ->assertSee('Continue to payment')
            ->assertSee($tier->name)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->assertSee('Select payment method')
            // Compact card anatomy: avatar, label, type sub-label, summary + Select badge (pre-selection).
            ->assertSee('Pay with bKash', false)
            ->assertSee('Pay with PayPal', false)
            ->assertSee('Pay with Stripe', false)
            ->assertSee('Secure gateway', false)
            ->assertSee('+880 1710-000000', false)
            ->assertSee('aria-pressed', false);
    }

    public function test_checkout_lists_binance_p2p_and_b2b_gateway_when_configured(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => [
                'binance' => [
                    'enabled' => true,
                    'mode' => 'sandbox',
                    'credentials' => ['sandbox' => [
                        'merchant_id' => '7890123',
                        'api_key' => 'bnc-pay-api-key',
                        'secret_key' => 'bnc-pay-secret',
                    ], 'live' => []],
                ],
            ],
        ]));

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->assertSee('Select payment method')
            // Manual P2P wallet method.
            ->assertSee('Binance P2P (USDT)', false)
            ->assertSee('Binance UID: 123456789', false)
            // B2B gateway card (enabled + configured).
            ->assertSee('Binance Pay (B2B)', false)
            ->assertSee('Pay with Binance Pay (B2B)', false);
    }

    public function test_checkout_confirmation_shows_invoice_and_payment_details(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'bkash')
            ->assertSee('Payment instructions')
            ->assertSee('Confirm payment')
            ->call('confirmPurchase')
            ->assertSee('Order placed')
            ->assertSee('View in panel')
            ->assertSee('INV-');
    }

    public function test_checkout_can_be_cancelled(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->assertSet('selectedTierId', $tier->id)
            ->call('cancelCheckout')
            ->assertSet('selectedTierId', null)
            ->assertSet('paymentMethod', '');

        $this->assertSame(0, Invoice::query()->where('user_id', $customer->id)->count());
    }

    public function test_service_checkout_shows_chosen_plan_summary(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->call('purchase', $tier->id)
            ->assertSee('Order summary')
            ->assertSee($tier->name)
            ->assertSee($tier->price)
            ->assertSee('Total to pay');
    }

    public function test_package_checkout_shows_chosen_package_summary(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Packages::class)
            ->call('purchase', $package->id)
            ->assertSee('Order summary')
            ->assertSee($package->title)
            ->assertSee($package->displayPrice())
            ->assertSee('Total to pay');
    }

    public function test_panel_invoices_show_payment_method_label(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Invoice::query()->create([
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'invoice_no' => 'INV-METHOD-001',
            'amount' => 49.00,
            'status' => 'pending',
            'payment_method' => 'binance_p2p',
        ]);

        Livewire::actingAs($customer)
            ->test(Invoices::class)
            ->assertSee('Binance P2P (USDT)');
    }

    public function test_admin_dashboard_lists_pending_order_with_method(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        Invoice::query()->create([
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'invoice_no' => 'INV-PENDING-001',
            'amount' => 149.00,
            'status' => 'pending',
            'payment_method' => 'bkash',
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('INV-PENDING-001')
            ->assertSee('bKash');
    }

    public static function serviceMethodsProvider(): array
    {
        return [
            ['bkash', 'bKash'],
            ['nagad', 'Nagad'],
            ['binance_p2p', 'Binance P2P (USDT)'],
            ['paypal', 'PayPal'],
        ];
    }

    public static function packageMethodsProvider(): array
    {
        return self::serviceMethodsProvider();
    }
}
