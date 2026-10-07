<?php

namespace Tests\Feature;

use App\Livewire\Plans;
use App\Models\Invoice;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlanPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_plan_page_renders_grouped_categories_with_counts(): void
    {
        $category = ServiceCategory::query()->published()->firstOrFail();
        $tier = ServiceTier::query()->published()->firstOrFail();

        Livewire::test(Plans::class)
            ->assertSee('Plans &amp;', false)
            ->assertSee('Pricing')
            ->assertSee($category->name)
            ->assertSee($tier->name)
            ->assertSee('Most popular');
    }

    public function test_plan_category_filter_isolates_section(): void
    {
        $category = ServiceCategory::query()->published()->firstOrFail();
        $tier = ServiceTier::query()->published()->where('service_category_id', $category->id)->firstOrFail();

        Livewire::test(Plans::class)
            ->call('setCategory', (string) $category->id)
            ->assertSee($tier->name);
    }

    public function test_plan_search_filters_tiers(): void
    {
        $tier = ServiceTier::query()->published()->firstOrFail();

        Livewire::test(Plans::class)
            ->set('search', $tier->name)
            ->assertSee($tier->name);

        Livewire::test(Plans::class)
            ->set('search', 'zzzz-nomatch-zzzz')
            ->assertDontSee($tier->name)
            ->assertSee('No plans match your search.');
    }

    public function test_plan_guest_purchase_redirects_to_register(): void
    {
        $tier = ServiceTier::query()->published()->firstOrFail();

        Livewire::test(Plans::class)
            ->call('purchase', $tier->id)
            ->assertRedirect(route('register', ['plan' => $tier->id]));
    }

    public function test_plan_purchase_creates_pending_invoice(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->published()->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Plans::class)
            ->call('purchase', $tier->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'paypal')
            ->call('confirmPurchase')
            ->assertHasNoErrors()
            ->assertSee('Plan '.$tier->name.' requested via PayPal');

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'status' => 'pending',
            'payment_method' => 'paypal',
        ]);
    }

    public function test_plan_duplicate_active_order_is_blocked(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->published()->firstOrFail();

        Invoice::query()->create([
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'invoice_no' => 'INV-2026-1111',
            'amount' => (float) preg_replace('/[^0-9.]/', '', $tier->price),
            'currency' => 'USD',
            'status' => 'pending',
            'purchased_at' => now(),
        ]);

        Livewire::actingAs($customer)
            ->test(Plans::class)
            ->call('purchase', $tier->id)
            ->assertSee('You already have an active order for this plan.');
    }
}
