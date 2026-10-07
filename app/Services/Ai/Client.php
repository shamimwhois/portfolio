<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class Client
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->config['base_url'] ?? null)
            && (filled($this->config['api_key'] ?? null) || ($this->config['local'] ?? false));
    }

    /**
     * @param  Message[]  $messages
     */
    public function chat(array $messages, ?int $maxTokens = null): string
    {
        if (! $this->isConfigured()) {
            throw new AiException('LLM client is not configured.');
        }

        try {
            $response = Http::baseUrl($this->config['base_url'])
                ->withToken($this->config['api_key'])
                ->acceptJson()
                ->asJson()
                ->timeout((int) ($this->config['timeout'] ?? 30))
                ->post('/chat/completions', [
                    'model' => $this->config['model'],
                    'messages' => array_map(
                        static fn (Message $message) => $message->toArray(),
                        $messages
                    ),
                    'max_tokens' => $maxTokens ?? (int) ($this->config['max_tokens'] ?? 500),
                ]);
        } catch (ConnectionException $e) {
            throw new AiException('Could not reach the LLM provider.', 0, $e);
        }

        if ($response->failed()) {
            throw new AiException(
                sprintf('LLM provider returned HTTP %s.', $response->status()),
                $response->status()
            );
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new AiException('LLM provider returned an empty response.');
        }

        return $content;
    }
}
