<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Ai as SettingsAi;
use App\Models\Setting;
use App\Models\User;
use App\Services\Ai\Client;
use App\Support\AiConfig;
use App\Support\SupportAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_provider_defaults_from_env(): void
    {
        $all = AiConfig::all();

        $this->assertSame('openai', $all['provider']);
        $this->assertSame(config('llm.base_url'), $all['base_url']);
        $this->assertSame(config('llm.api_key'), $all['api_key']);
        $this->assertSame(config('llm.model'), $all['model']);
        $this->assertSame((int) config('llm.timeout'), $all['timeout']);
        $this->assertSame((int) config('llm.max_tokens'), $all['max_tokens']);
        $this->assertFalse($all['local']);
        $this->assertNull($all['custom_name']);
    }

    public function test_admin_can_save_ai_provider(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAi::class)
            ->set('ai_provider', 'openrouter')
            ->set('ai_config_base_url', 'https://openrouter.ai/api/v1')
            ->set('ai_config_api_key', 'sk-openrouter')
            ->set('ai_config_model', 'openai/gpt-4o-mini')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('openrouter', Setting::get('ai_provider'));
        $this->assertSame('https://openrouter.ai/api/v1', Setting::get('ai_config.base_url'));
        $this->assertSame('sk-openrouter', Setting::get('ai_config.api_key'));
        $this->assertSame('openai/gpt-4o-mini', Setting::get('ai_config.model'));

        $resolved = AiConfig::all();
        $this->assertSame('openrouter', $resolved['provider']);
        $this->assertSame('https://openrouter.ai/api/v1', $resolved['base_url']);
        $this->assertSame('sk-openrouter', $resolved['api_key']);
        $this->assertSame('openai/gpt-4o-mini', $resolved['model']);
    }

    public function test_ai_test_connection_success(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => 'pong']]],
            ], 200),
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAi::class)
            ->set('ai_provider', 'deepseek')
            ->set('ai_config_base_url', 'https://api.deepseek.com/v1')
            ->set('ai_config_api_key', 'sk-test')
            ->set('ai_config_model', 'deepseek-chat')
            ->call('testConnection')
            ->assertSet('testResult', 'ok');
    }

    public function test_ai_test_connection_failure(): void
    {
        Http::fake([
            '*' => Http::response([], 401),
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(SettingsAi::class)
            ->set('ai_provider', 'deepseek')
            ->set('ai_config_base_url', 'https://api.deepseek.com/v1')
            ->set('ai_config_api_key', 'sk-test')
            ->set('ai_config_model', 'deepseek-chat')
            ->call('testConnection')
            ->assertSet('testResult', 'LLM provider returned HTTP 401.');
    }

    public function test_local_provider_configured_without_api_key(): void
    {
        Setting::set('ai_provider', 'ollama');
        Setting::set('ai_config', [
            'base_url' => 'http://localhost:11434/v1',
            'api_key' => null,
            'model' => 'llama3.2',
        ]);

        $config = AiConfig::all();
        $this->assertTrue($config['local']);
        $this->assertTrue((new Client($config))->isConfigured());
        $this->assertTrue(app(SupportAssistant::class)->isAiConfigured());
    }

    public function test_ai_client_requires_base_url_for_cloud(): void
    {
        $config = AiConfig::all();
        $this->assertFalse($config['local']);
        $this->assertFalse((new Client($config))->isConfigured());
    }
}
