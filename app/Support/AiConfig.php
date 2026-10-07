<?php

namespace App\Support;

use App\Models\Setting;

class AiConfig
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function providers(): array
    {
        return config('llm.providers', []);
    }

    public static function provider(): string
    {
        $provider = (string) Setting::get('ai_provider', 'openai');

        if (! array_key_exists($provider, static::providers())) {
            $provider = 'openai';
        }

        return $provider;
    }

    /**
     * Resolved client configuration: admin-saved values win, then the provider
     * preset, then the env-driven config/llm.php fallback. Never persisted.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $provider = static::provider();
        $preset = config('llm.providers.'.$provider, []);
        $saved = (array) Setting::get('ai_config', []);

        return [
            'provider' => $provider,
            'base_url' => ! empty($saved['base_url']) ? $saved['base_url'] : ($preset['base_url'] ?: config('llm.base_url')),
            'api_key' => ! empty($saved['api_key']) ? $saved['api_key'] : config('llm.api_key'),
            'model' => ! empty($saved['model']) ? $saved['model'] : ($preset['model'] ?: config('llm.model')),
            'timeout' => (int) config('llm.timeout', 30),
            'max_tokens' => (int) config('llm.max_tokens', 500),
            'local' => (bool) ($preset['local'] ?? false),
            'custom_name' => $provider === 'custom' && ! empty($saved['custom_name'])
                ? $saved['custom_name']
                : null,
        ];
    }
}
