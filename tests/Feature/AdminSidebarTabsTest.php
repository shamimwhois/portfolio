<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Site;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSidebarTabsTest extends TestCase
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

    public function test_portfolio_group_renders_all_five_section_tabs(): void
    {
        $this->actingAs($this->admin());

        $html = view('components.admin.sidebar')->render();

        $this->assertStringContainsString('Portfolio', $html);
        $this->assertStringContainsString('Home (Hero)', $html);
        $this->assertStringContainsString('About', $html);
        $this->assertStringContainsString('Timeline', $html);
        $this->assertStringContainsString('Skills', $html);
        $this->assertStringContainsString('Selected Work', $html);
    }

    public function test_flat_portfolio_skills_and_timeline_items_removed(): void
    {
        $topLevel = collect(config('nav.admin'));

        $this->assertNull($topLevel->firstWhere('route', 'admin.projects'));
        $this->assertNull($topLevel->firstWhere('route', 'admin.skills'));
        $this->assertNull($topLevel->firstWhere('route', 'admin.timeline'));

        $portfolio = $topLevel->firstWhere('label', 'Portfolio');
        $this->assertNotNull($portfolio);
        $this->assertCount(5, $portfolio['children']);

        $labels = collect($portfolio['children'])->pluck('label')->all();
        $this->assertSame(['Home (Hero)', 'About', 'Timeline', 'Skills', 'Selected Work'], $labels);
    }

    public function test_home_and_about_tabs_deep_link_into_site_settings(): void
    {
        $children = collect(config('nav.admin'))->firstWhere('label', 'Portfolio')['children'];

        $home = collect($children)->firstWhere('label', 'Home (Hero)');
        $about = collect($children)->firstWhere('label', 'About');

        $this->assertSame('admin.settings.site', $home['route']);
        $this->assertSame(['section' => 'hero'], $home['params']);
        $this->assertSame('admin.settings.site', $about['route']);
        $this->assertSame(['section' => 'about'], $about['params']);
    }

    public function test_site_settings_renders_section_anchors_for_deep_links(): void
    {
        $this->actingAs($this->admin());

        $html = Livewire::test(Site::class)->html();

        $this->assertStringContainsString('id="section-hero"', $html);
        $this->assertStringContainsString('id="section-about"', $html);
    }
}
