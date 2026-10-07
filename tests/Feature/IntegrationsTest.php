<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Integrations as SettingsIntegrations;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // The seeder fills every legacy flat social key — clear them so each
        // test starts from a clean social state.
        foreach (['facebook', 'github', 'telegram', 'whatsapp'] as $key) {
            Setting::set($key, '');
        }

        Setting::set('socials', []);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_registry_lists_real_integrations_across_all_groups(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            ->assertSet('metrics.total', 25)
            ->assertSet('metrics.groups', 4)
            ->assertSet('plugins', fn (array $plugins) => count($plugins) === 25)
            ->assertSet('plugins.openai.group', 'ai')
            ->assertSet('plugins.stripe.group', 'payments')
            ->assertSet('plugins.facebook.group', 'social')
            ->assertSet('plugins.google_oauth.group', 'auth')
            ->assertSet('plugins.github_oauth.label', 'GitHub Sign-In')
            ->assertSet('plugins.embed.label', 'Embedded Feed')
            ->assertSet('plugins.manual.label', 'Manual Methods');
    }

    public function test_manage_buttons_point_to_existing_settings_routes(): void
    {
        $component = Livewire::actingAs($this->admin())->test(SettingsIntegrations::class);
        $plugins = $component->get('plugins');

        $this->assertNotEmpty($plugins);

        foreach ($plugins as $key => $plugin) {
            $this->assertTrue(
                Route::has($plugin['manage']),
                'Manage route '.$plugin['manage'].' missing for plugin '.$key
            );
        }
    }

    public function test_page_renders_workspace_and_registry(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            ->assertSee('Integration Center')
            ->assertSee('Plugin Registry')
            ->assertSee('AI Providers')
            ->assertSee('Payment Gateways')
            ->assertSee('Social Channels')
            ->assertSee('Login & Security');
    }

    public function test_ai_status_reflects_selected_provider_and_credentials(): void
    {
        Setting::set('ai_provider', 'openai');
        Setting::set('ai_config', ['api_key' => 'sk-test', 'model' => 'gpt-4o-mini']);

        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            ->assertSet('plugins.openai.status', 'active')
            ->assertSet('plugins.deepseek.status', 'inactive')
            ->assertSet('plugins.openai.meta', 'Active provider · gpt-4o-mini');
    }

    public function test_local_ai_providers_count_as_configured_without_a_key(): void
    {
        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            ->assertSet('plugins.ollama.meta', 'Configured · switch provider');
    }

    public function test_social_status_reflects_connected_channels(): void
    {
        Setting::set('socials', [
            'github' => ['url' => 'https://github.com/shamim', 'enabled' => true],
        ]);

        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            ->assertSet('plugins.github.status', 'active')
            ->assertSet('plugins.github.meta', 'Connected · https://github.com/shamim')
            ->assertSet('plugins.facebook.status', 'inactive')
            ->assertSet('plugins.embed.status', 'inactive');
    }

    public function test_registry_filters_by_group_search_and_status(): void
    {
        Setting::set('socials', [
            'github' => ['url' => 'https://github.com/shamim', 'enabled' => true],
        ]);

        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            // Group tab narrows the registry.
            ->set('group', 'ai')
            ->assertSet('filteredPlugins', fn (array $plugins) => count($plugins) === 7)
            // Search across the whole registry.
            ->set('group', 'all')
            ->set('search', 'stripe')
            ->assertSet('filteredPlugins', fn (array $plugins) => array_keys($plugins) === ['stripe'])
            // Status filter within a group.
            ->set('search', '')
            ->set('group', 'social')
            ->set('status', 'active')
            ->assertSet('filteredPlugins', fn (array $plugins) => array_keys($plugins) === ['github']);
    }

    public function test_group_stats_and_metrics_stay_in_sync(): void
    {
        Setting::set('socials', [
            'github' => ['url' => 'https://github.com/shamim', 'enabled' => true],
        ]);

        Livewire::actingAs($this->admin())
            ->test(SettingsIntegrations::class)
            ->assertSet('groupStats.social.count', 9)
            ->assertSet('groupStats.social.active', 1)
            ->assertSet('groupStats.payments.count', 7)
            // Manual methods are enabled by default → 1 active in payments.
            ->assertSet('metrics.active', 2);
    }
}
