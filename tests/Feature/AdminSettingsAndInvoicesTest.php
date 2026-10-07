<?php

namespace Tests\Feature;

use App\Livewire\Admin\Invoices as AdminInvoices;
use App\Livewire\Admin\Settings\Ai as SettingsAi;
use App\Livewire\Admin\Settings\Appearance as SettingsAppearance;
use App\Livewire\Admin\Settings\Payments as SettingsPayments;
use App\Livewire\Admin\Settings\Seo as SettingsSeo;
use App\Models\Invoice;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\User;
use App\Support\Payments;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSettingsAndInvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_payments_helper_uses_config_fallback(): void
    {
        $this->assertSame('USD', Payments::currency());
        $this->assertCount(4, Payments::methods());
        $this->assertCount(4, Payments::enabledMethods());
        $this->assertTrue(Payments::isEnabled('bkash'));
        $this->assertSame('bKash', Payments::method('bkash')['label']);
        $this->assertNotNull(Payments::method('paypal'));
        $this->assertNull(Payments::method('swift'));
    }

    public function test_gateways_hidden_by_default(): void
    {
        $this->assertCount(4, Payments::methods());
        $this->assertFalse(Payments::isGateway('stripe'));
        $this->assertCount(6, Payments::gateways());
    }

    public function test_admin_can_configure_a_gateway(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->set('payments.gateways.stripe.enabled', true)
            ->set('payments.gateways.stripe.mode', 'test')
            ->set('payments.gateways.stripe.credentials.test.secret_key', 'sk_test_123')
            ->set('payments.gateways.stripe.credentials.test.webhook_secret', 'whsec_test_123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Setting::get('payments.gateways.stripe.enabled'));
        $this->assertSame('test', Payments::gatewayMode('stripe'));
        $this->assertSame('sk_test_123', Payments::credential('stripe', 'secret_key'));
        $this->assertTrue(Payments::isGatewayAvailable('stripe'));
    }

    public function test_gateway_without_credentials_stays_hidden(): void
    {
        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => [
                'stripe' => ['enabled' => true, 'mode' => 'test', 'credentials' => ['test' => [], 'live' => []]],
            ],
        ]));

        $this->assertFalse(Payments::isGatewayAvailable('stripe'));
        $this->assertArrayNotHasKey('stripe', Payments::methods());
    }

    public function test_gateway_appears_at_checkout_when_configured(): void
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

        $methods = Payments::methods();
        $this->assertArrayHasKey('stripe', $methods);
        $this->assertSame('Stripe', $methods['stripe']['label']);
        $this->assertTrue($methods['stripe']['gateway']);
        $this->assertTrue(Payments::isGateway('stripe'));

        Setting::set('payments', array_replace_recursive(config('payments'), [
            'gateways' => ['stripe' => ['enabled' => false, 'credentials' => ['test' => ['secret_key' => 'sk_test_123'], 'live' => []]]],
        ]));

        $this->assertArrayNotHasKey('stripe', Payments::methods());
        $this->assertCount(4, Payments::methods());
    }

    public function test_payments_helper_reflects_settings_override(): void
    {
        Setting::set('payments', [
            'currency' => 'BDT',
            'methods' => [
                'bkash' => ['label' => 'bKash', 'type' => 'Mobile Money', 'account' => '+880 1700-111', 'enabled' => true],
                'nagad' => ['label' => 'Nagad', 'type' => 'Mobile Money', 'account' => '+880 1800-222', 'enabled' => false],
            ],
        ]);

        $this->assertSame('BDT', Payments::currency());
        $this->assertFalse(Payments::isEnabled('nagad'));
        $this->assertArrayNotHasKey('nagad', Payments::enabledMethods());
        $this->assertSame('+880 1700-111', Payments::method('bkash')['account']);
    }

    public function test_admin_can_save_payment_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->set('payments.currency', 'EUR')
            ->set('payments.methods.paypal.enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('EUR', Setting::get('payments.currency'));
        $this->assertFalse(Setting::get('payments.methods.paypal.enabled'));
        $this->assertSame('EUR', Payments::currency());
        $this->assertFalse(Payments::isEnabled('paypal'));
    }

    public function test_admin_can_open_configure_modal_and_run_diagnostics(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->assertSet('editingKey', 'stripe')
            ->call('runDiagnostics', 'stripe')
            ->assertSet('diagnostics.ok', false)
            ->set('payments.gateways.stripe.enabled', true)
            ->set('payments.gateways.stripe.mode', 'test')
            ->set('payments.gateways.stripe.credentials.test.secret_key', 'sk_test_123')
            ->set('payments.gateways.stripe.credentials.test.webhook_secret', 'whsec_test_123')
            ->call('runDiagnostics', 'stripe')
            ->assertSet('diagnostics.ok', true)
            ->call('closeModal')
            ->assertSet('editingKey', null);
    }

    public function test_admin_can_toggle_sandbox_mode_and_availability(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'bkash')
            ->assertSet('editingKey', 'bkash')
            ->call('toggleAvailability', 'bkash')
            ->assertSet('payments.gateways.bkash.enabled', true)
            ->assertSet('payments.gateways.bkash.mode', 'sandbox')
            ->call('toggleSandboxMode', 'bkash')
            ->assertSet('payments.gateways.bkash.mode', 'live')
            ->call('toggleSandboxMode', 'bkash')
            ->assertSet('payments.gateways.bkash.mode', 'sandbox');
    }

    public function test_gateway_directory_filters_by_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsPayments::class)
            ->call('configure', 'stripe')
            ->call('toggleAvailability', 'stripe')
            ->call('closeModal')
            ->set('status', 'enabled')
            ->assertSet('filteredGateways', fn (array $gateways) => array_keys($gateways) === ['stripe'])
            ->set('status', 'disabled')
            ->assertSet('filteredGateways', fn (array $gateways) => count($gateways) === 5);
    }

    public function test_admin_can_save_theme_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('theme_default', 'dark_orchid')
            ->set('theme_switcher', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('dark_orchid', Setting::get('theme_default'));
        $this->assertFalse(Setting::get('theme_switcher'));
    }

    public function test_admin_can_save_seo_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            ->call('manage', 'global')
            ->set('editTitle', 'Shamim Dev')
            ->set('editDescription', 'A sci-fi portfolio.')
            ->call('savePage')
            ->assertHasNoErrors();

        $this->assertSame('Shamim Dev', Setting::get('seo.meta_title'));
        $this->assertSame('A sci-fi portfolio.', Setting::get('seo.meta_description'));
    }

    public function test_admin_can_save_ai_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAi::class)
            ->set('ai_name', 'HUD Bot')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('HUD Bot', Setting::get('ai_name'));
    }

    public function test_admin_can_save_section_visibility_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('sections.hero', false)
            ->set('sections.faq', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse(Setting::get('sections.hero'));
        $this->assertFalse(Setting::get('sections.faq'));
        $this->assertTrue(Setting::get('sections.projects'));
    }

    public function test_invalid_theme_default_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('theme_default', 'neon_pink')
            ->call('save')
            ->assertHasErrors(['theme_default']);
    }

    public function test_home_page_applies_theme_and_seo_settings(): void
    {
        Setting::set('theme_default', 'dark_aurora');
        Setting::set('seo', ['meta_title' => 'SciFi Node', 'meta_description' => 'The description.']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-theme="dark_aurora"', false)
            ->assertSee('<meta name="description" content="The description." />', false)
            ->assertSee('<title>SciFi Node | ', false);
    }

    public function test_home_page_respects_section_visibility(): void
    {
        Setting::set('sections', ['faq' => false, 'about' => false, 'hero' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Frequently asked questions')
            ->assertDontSee('Engineering dependable software')
            ->assertSee('What I bring to the table');
    }

    public function test_assistant_hidden_when_disabled(): void
    {
        Setting::set('ai_enabled', false);

        $this->get(route('home'))->assertOk()->assertDontSee('Support AI');
    }

    public function test_theme_switcher_hidden_when_disabled(): void
    {
        Setting::set('theme_switcher', false);

        $this->get(route('home'))->assertOk()->assertDontSee('x-data="themeSwitcher"', false);
    }

    public function test_admin_invoices_page_renders_with_orders(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        $this->createInvoice($customer, $tier, 'INV-ADMIN-001', 'pending', 'bkash');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->assertSee('INV-ADMIN-001')
            ->assertSee('bKash')
            ->assertSee('Mark paid');
    }

    public function test_admin_can_mark_order_paid(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();
        $invoice = $this->createInvoice($customer, $tier, 'INV-MARK-001', 'pending', 'bkash');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('markPaid', $invoice->id);

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_admin_can_cancel_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();
        $invoice = $this->createInvoice($customer, $tier, 'INV-CANCEL-001', 'pending', 'nagad');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('cancel', $invoice->id);

        $this->assertSame('cancelled', $invoice->fresh()->status);
    }

    public function test_admin_can_refund_paid_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();
        $invoice = $this->createInvoice($customer, $tier, 'INV-REFUND-001', 'paid', 'paypal');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('refund', $invoice->id);

        $this->assertSame('refunded', $invoice->fresh()->status);
    }

    public function test_mark_paid_guard_rejects_non_pending(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();
        $invoice = $this->createInvoice($customer, $tier, 'INV-GUARD-001', 'cancelled', 'bkash');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('markPaid', $invoice->id);

        $this->assertSame('cancelled', $invoice->fresh()->status);
    }

    public function test_refund_guard_rejects_non_paid(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();
        $invoice = $this->createInvoice($customer, $tier, 'INV-GUARD-002', 'pending', 'binance_p2p');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('refund', $invoice->id);

        $this->assertSame('pending', $invoice->fresh()->status);
    }

    public function test_admin_can_view_invoice_details_modal(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'phone' => '+880 1700-000']);
        $tier = ServiceTier::query()->firstOrFail();
        $invoice = $this->createInvoice($customer, $tier, 'INV-VIEW-001', 'pending', 'bkash');
        $invoice->update(['notes' => 'Expedite delivery please']);

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('view', $invoice->id)
            ->assertSet('viewingId', $invoice->id)
            ->assertSee('Full order details')
            ->assertSee('INV-VIEW-001')
            ->assertSee($customer->name)
            ->assertSee('+880 1700-000')
            ->assertSee('bKash')
            ->assertSee('Expedite delivery please')
            ->call('closeView')
            ->assertSet('viewingId', null);
    }

    public function test_admin_invoices_filter_by_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();
        $this->createInvoice($customer, $tier, 'INV-FILTER-001', 'pending', 'bkash');
        $this->createInvoice($customer, $tier, 'INV-FILTER-002', 'paid', 'paypal');

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('updateStatus', 'paid')
            ->assertDontSee('INV-FILTER-001')
            ->assertSee('INV-FILTER-002');
    }

    private function createInvoice(User $user, ServiceTier $tier, string $no, string $status, string $method): Invoice
    {
        return Invoice::query()->create([
            'user_id' => $user->id,
            'service_tier_id' => $tier->id,
            'invoice_no' => $no,
            'amount' => 99.00,
            'status' => $status,
            'payment_method' => $method,
        ]);
    }
}
