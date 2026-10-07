<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LLM Connection
    |--------------------------------------------------------------------------
    |
    | OpenAI-compatible Chat Completions endpoint. Values are read from the
    | project's own environment (USER_LLM_*). Leave USER_LLM_API_KEY empty to
    | run the support assistant in fallback mode (offline canned replies).
    |
    */

    'base_url' => env('USER_LLM_BASE_URL', 'https://api.openai.com/v1'),

    'api_key' => env('USER_LLM_API_KEY'),

    'model' => env('USER_LLM_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('USER_LLM_TIMEOUT', 30),

    'max_tokens' => (int) env('USER_LLM_MAX_TOKENS', 500),

    /*
    |--------------------------------------------------------------------------
    | Provider presets
    |--------------------------------------------------------------------------
    |
    | OpenAI-compatible Chat Completions presets used by the admin AI settings
    | page. Saved settings override these; the env-driven keys above remain the
    | final fallback when both are empty.
    |
    */

    'providers' => [
        'openai' => ['label' => 'OpenAI', 'base_url' => 'https://api.openai.com/v1', 'model' => 'gpt-4o-mini', 'local' => false],
        'deepseek' => ['label' => 'DeepSeek', 'base_url' => 'https://api.deepseek.com/v1', 'model' => 'deepseek-chat', 'local' => false],
        'openrouter' => ['label' => 'OpenRouter', 'base_url' => 'https://openrouter.ai/api/v1', 'model' => 'openai/gpt-4o-mini', 'local' => false],
        'mistral' => ['label' => 'Mistral', 'base_url' => 'https://api.mistral.ai/v1', 'model' => 'mistral-small-latest', 'local' => false],
        'ollama' => ['label' => 'Ollama (local)', 'base_url' => 'http://localhost:11434/v1', 'model' => 'llama3.2', 'local' => true],
        'lmstudio' => ['label' => 'LM Studio (local)', 'base_url' => 'http://localhost:1234/v1', 'model' => 'local-model', 'local' => true],
        'custom' => ['label' => 'Custom', 'base_url' => '', 'model' => '', 'local' => false],
    ],

];
