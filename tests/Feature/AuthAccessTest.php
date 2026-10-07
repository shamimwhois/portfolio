<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_reach_panel(): void
    {
        $this->get('/panel')->assertRedirect('/login');
    }

    public function test_guest_cannot_reach_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customer_can_reach_panel(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer)
            ->get('/panel')
            ->assertOk()
            ->assertSee('Recent Invoices')
            ->assertSee('Client portal')
            ->assertSee('Billing')
            ->assertSee('Support');
    }

    public function test_customer_cannot_reach_admin(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_reach_admin_console(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Pending Orders')
            ->assertSee('Quick Actions')
            ->assertSee('Admin Console', false);
    }

    public function test_admin_can_reach_all_admin_sections(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        foreach (['users', 'projects', 'skills', 'services', 'packages', 'blog', 'faqs', 'timeline', 'messages', 'invoices', 'conversations', 'settings/site', 'settings/seo', 'settings/appearance', 'settings/ai', 'settings/payments'] as $section) {
            $this->actingAs($admin)
                ->get("/admin/{$section}")
                ->assertOk();
        }
    }

    public function test_admin_settings_index_redirects_to_site(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertRedirect('/admin/settings/site');
    }

    public function test_admin_settings_sidebar_shows_dropdown_links(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/admin/settings/site')
            ->assertOk()
            ->assertSee('/admin/settings/seo')
            ->assertSee('/admin/settings/appearance')
            ->assertSee('/admin/settings/ai')
            ->assertSee('/admin/settings/payments');
    }

    public function test_inactive_user_is_blocked_from_panel(): void
    {
        $inactive = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'is_active' => false,
        ]);

        $this->actingAs($inactive)
            ->get('/panel')
            ->assertRedirect('/login');
    }

    public function test_customer_can_reach_all_panel_sections(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        foreach (['panel', 'panel/invoices', 'panel/services', 'panel/support', 'panel/profile'] as $path) {
            $this->actingAs($customer)
                ->get('/'.$path)
                ->assertOk();
        }
    }
}
