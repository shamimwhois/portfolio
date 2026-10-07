<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Seo as SettingsSeo;
use App\Models\Setting;
use App\Models\User;
use App\Support\Seo as SeoSupport;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SeoManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_helper_resolves_global_seo_by_default(): void
    {
        Setting::set('seo', [
            'meta_title' => 'Shamim Dev',
            'meta_description' => 'Laravel specialist',
            'og_type' => 'website',
        ]);

        $seo = SeoSupport::forRequest('home', '/');

        $this->assertSame('Shamim Dev', $seo['meta_title']);
        $this->assertSame('Laravel specialist', $seo['meta_description']);
        $this->assertSame('index,follow', $seo['robots']);
    }

    public function test_helper_overrides_global_with_builtin_page_record(): void
    {
        Setting::set('seo', ['meta_title' => 'Global Title']);
        Setting::set('seo_pages', [
            'contact' => [
                'meta_title' => 'Contact Shamim',
                'robots' => 'noindex,follow',
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);

        $seo = SeoSupport::forRequest('contact', '/contact');

        $this->assertSame('Contact Shamim', $seo['meta_title']);
        $this->assertSame('noindex,follow', $seo['robots']);
    }

    public function test_helper_resolves_custom_path_records(): void
    {
        Setting::set('seo_pages', [
            'custom_about' => [
                'path' => '/about',
                'meta_title' => 'About Us',
                'robots' => 'index,follow',
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);

        $seo = SeoSupport::forRequest('home', '/about');

        $this->assertSame('About Us', $seo['meta_title']);
    }

    public function test_admin_can_save_global_seo_via_modal(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            ->call('manage', 'global')
            ->set('editTitle', 'Shamim — Laravel Portfolio')
            ->set('editRobots', 'noindex,nofollow')
            ->call('savePage')
            ->assertHasNoErrors();

        $this->assertSame('Shamim — Laravel Portfolio', Setting::get('seo.meta_title'));
        $this->assertSame('noindex,nofollow', Setting::get('seo.robots'));
    }

    public function test_admin_can_manage_a_builtin_page_record(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            ->call('manage', 'plan')
            ->set('editTitle', 'Plans & Pricing')
            ->set('editDescription', 'Compare plans')
            ->call('savePage')
            ->assertHasNoErrors();

        $record = Setting::get('seo_pages.plan');

        $this->assertSame('Plans & Pricing', $record['meta_title']);
        $this->assertSame('Compare plans', $record['meta_description']);
        $this->assertSame('index,follow', $record['robots']);
        $this->assertNotNull($record['updated_at']);
    }

    public function test_admin_can_add_and_delete_a_custom_page(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            ->call('addPage')
            ->set('editPath', '/about')
            ->set('editTitle', 'About Us')
            ->call('savePage')
            ->assertHasNoErrors();

        $this->assertSame('About Us', Setting::get('seo_pages.custom_about.meta_title'));

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            ->call('deletePage', 'custom_about');

        $this->assertNull(Setting::get('seo_pages.custom_about'));
    }

    public function test_duplicate_custom_path_is_rejected(): void
    {
        $admin = $this->admin();
        Setting::set('seo_pages', [
            'custom_about' => ['path' => '/about', 'robots' => 'index,follow', 'updated_at' => now()->toDateTimeString()],
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            ->call('addPage')
            ->set('editPath', '/about')
            ->call('savePage')
            ->assertHasErrors(['editPath']);
    }

    public function test_seo_metrics_reflect_records(): void
    {
        $admin = $this->admin();
        Setting::set('seo_pages', [
            'contact' => ['meta_title' => 'Contact', 'robots' => 'index,follow', 'updated_at' => now()->toDateTimeString()],
            'custom_faq' => ['path' => '/faq', 'robots' => 'noindex,nofollow', 'updated_at' => now()->toDateTimeString()],
        ]);

        Livewire::actingAs($admin)
            ->test(SettingsSeo::class)
            // 1 global + 8 built-in pages + 1 custom.
            ->assertSet('metrics.total', 10)
            ->assertSet('metrics.optimized', 1)
            ->assertSet('metrics.noindex', 1);
    }
}
