<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Appearance as SettingsAppearance;
use App\Models\Setting;
use App\Models\User;
use App\Support\Theme;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ThemeManagerTest extends TestCase
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

    public function test_theme_helper_merges_saved_palettes_over_defaults(): void
    {
        $defaults = Theme::palette('light');

        $this->assertSame('#4663EE', $defaults['primary']);

        Setting::set('theme_palettes', [
            'light' => ['primary' => '#123456', 'secondary' => '#abcdef'],
        ]);

        $palette = Theme::palette('light');

        $this->assertSame('#123456', $palette['primary']);
        $this->assertSame('#abcdef', $palette['secondary']);
        // Unset slot falls back to the theme default.
        $this->assertSame('#17A86A', $palette['accent']);
    }

    public function test_theme_style_tag_emits_overrides_for_default_and_customized_themes(): void
    {
        Setting::set('theme_default', 'dark');
        Setting::set('theme_palettes', [
            'light_rose' => ['primary' => '#ff0000', 'secondary' => '#ff0000', 'accent' => '#ff0000'],
        ]);

        $tag = Theme::styleTag();

        $this->assertStringContainsString('[data-theme="dark"]', $tag);
        $this->assertStringContainsString('[data-theme="light_rose"]', $tag);
        // Uncustomized non-default themes are not emitted.
        $this->assertStringNotContainsString('[data-theme="light_ice"]', $tag);
        $this->assertStringContainsString('--color-brand-600:#ff0000', $tag);
    }

    public function test_admin_can_activate_a_theme_instantly(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->call('activate', 'dark_orchid')
            ->assertSet('theme_default', 'dark_orchid')
            // Palette editor follows the activated theme.
            ->assertSet('palette_theme', 'dark_orchid');

        $this->assertSame('dark_orchid', Setting::get('theme_default'));
    }

    public function test_admin_can_save_a_theme_palette(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('palette_theme', 'light_ice')
            ->set('palette.primary', '#AA11BB')
            ->set('palette.secondary', '#22CCDD')
            ->set('palette.accent', '#33EEFF')
            ->call('savePalette')
            ->assertHasNoErrors();

        $stored = Setting::get('theme_palettes.light_ice');

        $this->assertSame('#AA11BB', $stored['primary']);
        $this->assertSame('#22CCDD', $stored['secondary']);
        $this->assertSame('#33EEFF', $stored['accent']);
    }

    public function test_admin_can_reset_a_palette_to_defaults(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('palette_theme', 'light_rose')
            ->set('palette.primary', '#ff0000')
            ->call('savePalette')
            ->set('palette.primary', '#ff0000')
            ->call('resetPalette')
            ->assertSet('palette.primary', '#B93A6E');

        $this->assertSame('#B93A6E', Setting::get('theme_palettes.light_rose.primary'));
    }

    public function test_invalid_palette_hex_is_rejected(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('palette.primary', 'not-a-colour')
            ->call('savePalette')
            ->assertHasErrors(['palette.primary']);
    }

    public function test_appearance_save_api_is_preserved(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->set('theme_default', 'dark_aurora')
            ->set('theme_switcher', false)
            ->set('sections.faq', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('dark_aurora', Setting::get('theme_default'));
        $this->assertFalse(Setting::get('theme_switcher'));
        $this->assertFalse(Setting::get('sections.faq'));
    }

    public function test_theme_manager_metrics_reflect_active_theme(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingsAppearance::class)
            ->call('activate', 'dark')
            ->assertSet('metrics.active_label', 'Dark')
            ->assertSet('metrics.mode', 'Dark')
            ->assertSet('metrics.themes', 7)
            ->assertSet('metrics.sections_total', 8);
    }
}
