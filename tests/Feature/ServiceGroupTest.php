<?php

namespace Tests\Feature;

use App\Livewire\Admin\GroupPackages as AdminGroupPackages;
use App\Livewire\Servicegroup;
use App\Models\GroupPackage;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_servicegroup_renders_combos_tools_and_sections(): void
    {
        $combo = GroupPackage::query()->where('kind', GroupPackage::KIND_COMBO)->firstOrFail();
        $tool = GroupPackage::query()->where('kind', GroupPackage::KIND_TOOL)->firstOrFail();

        Livewire::test(Servicegroup::class)
            ->assertSee('COMBO PACKAGE')
            ->assertSee('GROUP BUY SINGLE TOOLS')
            ->assertSee('Why You Must Choose Us?')
            ->assertSee($combo->title)
            ->assertSee($tool->title)
            ->assertSee('Order Now');
    }

    public function test_draft_items_hidden_from_servicegroup(): void
    {
        // 'Essential Kit' has no title overlap with any other seeded item.
        $combo = GroupPackage::query()->where('kind', GroupPackage::KIND_COMBO)->where('title', 'Essential Kit')->firstOrFail();
        $combo->update(['publish_status' => 'draft']);

        Livewire::test(Servicegroup::class)
            ->assertDontSee($combo->title);
    }

    public function test_unpublished_items_stay_listed_but_not_buyable(): void
    {
        $combo = GroupPackage::query()->where('kind', GroupPackage::KIND_COMBO)->firstOrFail();
        $combo->update(['publish_status' => 'unpublished']);

        Livewire::test(Servicegroup::class)
            ->assertSee($combo->title)
            ->assertSee('Inactive')
            ->assertSee('Stock Out')
            ->assertDontSeeHtml('wire:click="purchase('.$combo->id.')');
    }

    public function test_servicegroup_guest_purchase_redirects_to_register(): void
    {
        $combo = GroupPackage::query()->where('kind', GroupPackage::KIND_COMBO)->firstOrFail();

        Livewire::test(Servicegroup::class)
            ->call('purchase', $combo->id)
            ->assertRedirect(route('register'));
    }

    public function test_servicegroup_purchase_creates_invoice_with_group_package(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $combo = GroupPackage::query()->where('kind', GroupPackage::KIND_COMBO)->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Servicegroup::class)
            ->call('purchase', $combo->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'nagad')
            ->call('confirmPurchase')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'group_package_id' => $combo->id,
            'amount' => $combo->price,
            'currency' => 'BDT',
            'status' => 'pending',
            'payment_method' => 'nagad',
        ]);
    }

    public function test_admin_can_create_edit_status_and_delete_group_items(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdminGroupPackages::class)
            ->call('create')
            ->set('kind', 'tool')
            ->set('title', 'Test Tool Pro')
            ->set('price', 199)
            ->set('features', "Alpha\nBeta")
            ->call('save')
            ->assertHasNoErrors();

        $item = GroupPackage::query()->where('title', 'Test Tool Pro')->firstOrFail();
        $this->assertSame('tool', $item->kind);
        $this->assertSame(['Alpha', 'Beta'], $item->features);
        $this->assertTrue($item->isPublished());

        Livewire::actingAs($this->admin)
            ->test(AdminGroupPackages::class)
            ->call('setStatus', $item->id, 'draft');

        $this->assertTrue($item->refresh()->isDraft());

        Livewire::actingAs($this->admin)
            ->test(AdminGroupPackages::class)
            ->call('edit', $item->id)
            ->set('title', 'Test Tool Pro Edited')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Test Tool Pro Edited', $item->refresh()->title);

        Livewire::actingAs($this->admin)
            ->test(AdminGroupPackages::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('group_packages', ['id' => $item->id]);
    }

    public function test_invoice_item_label_resolves_group_package(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $combo = GroupPackage::query()->where('kind', GroupPackage::KIND_COMBO)->firstOrFail();

        $invoice = Invoice::query()->create([
            'user_id' => $customer->id,
            'group_package_id' => $combo->id,
            'invoice_no' => 'INV-2026-9999',
            'amount' => (float) $combo->price,
            'currency' => 'BDT',
            'status' => 'pending',
            'purchased_at' => now(),
        ]);

        $this->assertSame('Group Buy // '.$combo->title, $invoice->itemLabel());
    }
}
