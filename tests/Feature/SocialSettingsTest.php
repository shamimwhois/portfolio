<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Site as SettingsSite;
use App\Livewire\Admin\Settings\Social as SettingsSocial;
use App\Models\Setting;
use App\Models\User;
use App\Support\Social;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SocialSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // The seeder fills every legacy flat key — clear them so each test
        // starts from a clean social state.
        foreach (['facebook', 'github', 'telegram', 'whatsapp'] as $key) {
            Setting::set($key, '');
        }

        Setting::set('socials', []);
    }

    public function test_social_helper_resolves_legacy_flat_keys(): void
    {
        Setting::set('facebook', 'https://facebook.com/legacy');

        $channels = Social::all();

        $this->assertSame('https://facebook.com/legacy', $channels['facebook']['url']);
        $this->assertCount(1, Social::connected());
        $this->assertSame(1, Social::connectedCount());
    }

    public function test_social_helper_filters_disabled_channels(): void
    {
        Setting::set('socials', [
            'github' => ['url' => 'https://github.com/shamim', 'enabled' => true],
            'telegram' => ['url' => 'https://t.me/shamim', 'enabled' => false],
        ]);

        $connected = Social::connected();

        $this->assertArrayHasKey('github', $connected);
        $this->assertArrayNotHasKey('telegram', $connected);
        $this->assertSame(1, Social::connectedCount());
    }

    public function test_admin_can_save_social_channels_and_embed(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsSocial::class)
            ->set('channels.github.url', 'https://github.com/shamim')
            ->set('channels.github.enabled', true)
            ->set('channels.facebook.url', 'https://facebook.com/shamim')
            ->set('channels.facebook.enabled', false)
            ->set('embed_enabled', true)
            ->set('embed_title', 'From the community')
            ->set('embed_html', '<blockquote class="instagram-media">demo</blockquote>')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('https://github.com/shamim', Setting::get('socials.github.url'));
        $this->assertTrue(Setting::get('socials.github.enabled'));
        // Disabled channels keep their URL (so toggling doesn't lose data)
        // but are not saved as enabled.
        $this->assertSame('https://facebook.com/shamim', Setting::get('socials.facebook.url'));
        $this->assertFalse(Setting::get('socials.facebook.enabled'));

        $embed = Social::embed();
        $this->assertTrue($embed['enabled']);
        $this->assertSame('From the community', $embed['title']);
        $this->assertSame('<blockquote class="instagram-media">demo</blockquote>', $embed['html']);

        // Only enabled+filled channels count as connected.
        $this->assertSame(1, Social::connectedCount());
    }

    public function test_social_directory_filters_by_search(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsSocial::class)
            ->set('search', 'git')
            ->assertSet('filteredChannels', fn (array $channels) => array_keys($channels) === ['github']);
    }

    public function test_social_metrics_reflect_form_state(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsSocial::class)
            ->set('channels.github.url', 'https://github.com/shamim')
            ->set('channels.github.enabled', true)
            ->assertSet('metrics.connected', 1)
            ->assertSet('metrics.embed', false);
    }

    public function test_site_settings_syncs_socials_array(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsSite::class)
            ->set('site_name', 'Shamim Dev')
            ->set('facebook', 'https://facebook.com/shamim')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('https://facebook.com/shamim', Setting::get('socials.facebook.url'));
        $this->assertSame(1, Social::connectedCount());
    }
}
