<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\Ai\AiException;
use App\Services\Ai\Client;
use App\Services\Ai\Message;
use App\Support\AiConfig;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Ai extends Component
{
    public bool $ai_enabled = true;

    public string $ai_name = 'Support AI';

    public string $ai_welcome = '';

    public string $ai_prompt = '';

    public string $ai_provider = 'openai';

    public string $ai_config_base_url = '';

    public string $ai_config_api_key = '';

    public string $ai_config_model = '';

    public string $ai_custom_name = '';

    public ?string $testResult = null;

    public function mount(): void
    {
        $this->ai_enabled = (bool) Setting::get('ai_enabled', true);
        $this->ai_name = (string) Setting::get('ai_name', 'Support AI');
        $this->ai_welcome = (string) Setting::get('ai_welcome', 'Ask me about pricing, delivery, or packages.');
        $this->ai_prompt = (string) Setting::get('ai_prompt', 'You are a helpful support assistant for the portfolio. Answer concisely about services, pricing, packages, and delivery.');

        $this->ai_provider = AiConfig::provider();
        $preset = config('llm.providers.'.$this->ai_provider, []);
        $saved = (array) Setting::get('ai_config', []);

        $this->ai_config_base_url = (string) ($saved['base_url'] ?? $preset['base_url'] ?? config('llm.base_url'));
        $this->ai_config_api_key = (string) ($saved['api_key'] ?? '');
        $this->ai_config_model = (string) ($saved['model'] ?? $preset['model'] ?? config('llm.model'));
        $this->ai_custom_name = (string) ($saved['custom_name'] ?? '');
    }

    public function providerChanged(): void
    {
        $preset = config('llm.providers.'.$this->ai_provider, []);

        $this->ai_config_base_url = (string) ($preset['base_url'] ?? '');
        $this->ai_config_model = (string) ($preset['model'] ?? '');
    }

    public function testConnection(): void
    {
        $config = AiConfig::all();
        $config['base_url'] = $this->ai_config_base_url ?: $config['base_url'];
        $config['api_key'] = $this->ai_config_api_key ?: $config['api_key'];
        $config['model'] = $this->ai_config_model ?: $config['model'];
        $config['local'] = (bool) (config('llm.providers.'.$this->ai_provider.'.local') ?? false);

        try {
            (new Client($config))->chat([Message::user('ping')], 1);
            $this->testResult = 'ok';
        } catch (AiException $e) {
            $this->testResult = $e->getMessage();
        }
    }

    public function save(): void
    {
        $this->validate([
            'ai_name' => ['nullable', 'string', 'max:80'],
            'ai_provider' => ['required', Rule::in(array_keys(config('llm.providers')))],
            'ai_config_base_url' => ['required', 'string', 'starts_with:http'],
            'ai_config_api_key' => ['nullable', 'string'],
            'ai_config_model' => $this->ai_provider === 'custom' ? ['nullable', 'string'] : ['required', 'string'],
            'ai_custom_name' => $this->ai_provider === 'custom' ? ['required', 'string', 'max:80'] : ['nullable', 'string'],
        ]);

        Setting::set('ai_enabled', $this->ai_enabled);
        Setting::set('ai_name', $this->ai_name);
        Setting::set('ai_welcome', $this->ai_welcome);
        Setting::set('ai_prompt', $this->ai_prompt);
        Setting::set('ai_provider', $this->ai_provider);
        Setting::set('ai_config', [
            'base_url' => $this->ai_config_base_url ?: null,
            'api_key' => $this->ai_config_api_key ?: null,
            'model' => $this->ai_config_model ?: null,
            'custom_name' => $this->ai_provider === 'custom' ? ($this->ai_custom_name ?: null) : null,
        ]);

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.settings.ai');
    }
}
