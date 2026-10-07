<?php

namespace Tests\Feature;

use App\Livewire\Panel\Services;
use App\Livewire\Sections\Packages;
use App\Models\Invoice;
use App\Models\TechPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PackagePurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_purchase_redirects_to_register_with_package(): void
    {
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();
        $before = Invoice::query()->where('tech_package_id', $package->id)->count();

        Livewire::test(Packages::class)
            ->call('purchase', $package->id)
            ->assertRedirect(route('register', ['pkg' => $package->id]));

        $this->assertSame($before, Invoice::query()->where('tech_package_id', $package->id)->count());
    }

    public function test_authenticated_customer_package_purchase_creates_invoice(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Packages::class)
            ->call('purchase', $package->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'nagad')
            ->call('confirmPurchase')
            ->assertHasNoErrors()
            ->assertSee('Package '.$package->title.' requested via Nagad');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'tech_package_id' => $package->id,
            'amount' => $package->price,
            'status' => 'pending',
            'payment_method' => 'nagad',
        ]);
    }

    public function test_duplicate_package_purchase_is_blocked(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Invoice::query()->create([
            'user_id' => $customer->id,
            'tech_package_id' => $package->id,
            'invoice_no' => 'INV-DUP-001',
            'amount' => (float) $package->price,
            'status' => 'pending',
        ]);

        Livewire::actingAs($customer)
            ->test(Packages::class)
            ->call('purchase', $package->id)
            ->assertSee('already have an active license');

        $this->assertSame(1, Invoice::query()->where('user_id', $customer->id)->where('tech_package_id', $package->id)->count());
    }

    public function test_free_reference_package_cannot_be_purchased(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNull('price')->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Packages::class)
            ->call('purchase', $package->id)
            ->assertSee('reference only');

        $this->assertDatabaseMissing('invoices', ['tech_package_id' => $package->id]);
    }

    public function test_admin_can_set_selling_price_on_package(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Packages::class)
            ->call('create')
            ->set('category', 'php')
            ->set('title', 'Sellable Scaffold')
            ->set('version', '1.0')
            ->set('price', '99.5')
            ->set('billing', 'one-time')
            ->call('save')
            ->assertHasNoErrors();

        $package = TechPackage::query()->where('title', 'Sellable Scaffold')->first();
        $this->assertNotNull($package);
        $this->assertSame(99.5, (float) $package->price);
        $this->assertTrue($package->isSellable());
    }

    public function test_panel_services_shows_purchased_package(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Invoice::query()->create([
            'user_id' => $customer->id,
            'tech_package_id' => $package->id,
            'invoice_no' => 'INV-PKG-001',
            'amount' => (float) $package->price,
            'status' => 'paid',
            'purchased_at' => now(),
        ]);

        Livewire::actingAs($customer)
            ->test(Services::class)
            ->assertSee($package->title)
            ->assertSee($package->command);
    }

    public function test_invoice_item_label_resolves_for_package_purchase(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        $invoice = Invoice::query()->create([
            'user_id' => $customer->id,
            'tech_package_id' => $package->id,
            'invoice_no' => 'INV-LBL-001',
            'amount' => (float) $package->price,
            'status' => 'paid',
        ]);

        $this->assertStringContainsString($package->title, $invoice->itemLabel());
    }
}
