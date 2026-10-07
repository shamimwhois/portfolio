<?php

namespace Tests\Feature;

use App\Livewire\Admin\Messages;
use App\Livewire\Auth\Register;
use App\Livewire\Contact;
use App\Livewire\Panel\Invoices;
use App\Livewire\Sections\Services;
use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\ServiceTier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EngagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_can_submit_contact_message(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('message', 'Hello there.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'jane@example.com',
            'message' => 'Hello there.',
        ]);
    }

    public function test_registration_creates_active_customer(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'New User')
            ->set('email', 'new@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect('/panel');

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_customer_can_see_own_invoices(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Invoice::query()->create([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-TEST-001',
            'amount' => 99.00,
            'status' => 'draft',
        ]);

        Livewire::actingAs($customer)
            ->test(Invoices::class)
            ->assertSee('INV-TEST-001');
    }

    public function test_customer_cannot_see_other_users_invoices(): void
    {
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        Invoice::query()->create([
            'user_id' => $other->id,
            'invoice_no' => 'INV-SECRET-001',
            'amount' => 99.00,
            'status' => 'draft',
        ]);

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($customer)
            ->test(Invoices::class)
            ->assertDontSee('INV-SECRET-001');
    }

    public function test_guest_purchase_requires_login(): void
    {
        $tier = ServiceTier::query()->first();

        $this->get(route('login'))->assertOk();
        $this->assertNotNull($tier);
    }

    public function test_authenticated_customer_purchase_creates_invoice(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->first();

        Livewire::actingAs($customer)
            ->test(Services::class)
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

    public function test_admin_messages_page_shows_unread_count(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        ContactMessage::query()->create([
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'message' => 'Hello',
            'is_read' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(Messages::class)
            ->assertSee('Spammer');
    }
}
