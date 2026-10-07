<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Faq;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\TechPackage;
use App\Services\Ai\AiException;
use App\Services\Ai\Client;
use App\Services\Ai\Message;

class SupportAssistant
{
    public function __construct(
        private readonly Client $client,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) Setting::get('ai_enabled', true);
    }

    public function isAiConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * Answer a customer question for a given conversation.
     *
     * Persists the user message, generates (or falls back to) a reply,
     * persists the assistant message and returns the reply text.
     */
    public function answer(SupportConversation $conversation, string $question): string
    {
        $question = trim($question);

        $conversation->messages()->create([
            'role' => SupportMessage::ROLE_USER,
            'body' => $question,
        ]);

        $reply = $this->generate($conversation, $question);

        $conversation->messages()->create([
            'role' => SupportMessage::ROLE_ASSISTANT,
            'body' => $reply,
        ]);

        $conversation->update(['last_message_at' => now()]);

        ActivityLog::record('support.answered', auth()->user(), [
            'conversation' => $conversation->id,
            'source' => $conversation->source,
            'ai' => $this->client->isConfigured(),
        ]);

        return $reply;
    }

    public function generate(SupportConversation $conversation, string $question): string
    {
        if (! $this->client->isConfigured()) {
            return $this->fallbackReply($question);
        }

        try {
            return $this->client->chat($this->buildMessages($conversation, $question));
        } catch (AiException) {
            return $this->fallbackReply($question);
        }
    }

    /**
     * @return Message[]
     */
    public function buildMessages(SupportConversation $conversation, string $question): array
    {
        $history = $conversation->oldestMessages()
            ->latest('id')
            ->limit(8)
            ->get()
            ->reverse();

        $messages = [
            Message::system(implode("\n\n", array_filter([
                $this->systemPrompt(),
                $this->knowledge(),
            ]))),
        ];

        foreach ($history as $message) {
            $messages[] = $message->role === SupportMessage::ROLE_USER
                ? Message::user($message->body)
                : Message::assistant($message->body);
        }

        $messages[] = Message::user($question);

        return $messages;
    }

    public function systemPrompt(): string
    {
        return (string) Setting::get(
            'ai_prompt',
            'You are a friendly support assistant for a web development portfolio. '
            .'Answer concisely about services, pricing, packages, and delivery. '
            .'If you do not know, suggest opening a support ticket.'
        );
    }

    public function knowledge(): string
    {
        $lines = [];

        $name = Setting::get('site_name', 'MD SHAMIM HOSSAIN');
        $tagline = Setting::get('site_tagline', 'Senior Laravel & PHP Specialist');
        $email = Setting::get('email');

        $lines[] = sprintf('Site: %s — %s.', $name, $tagline);
        if (filled($email)) {
            $lines[] = sprintf('Contact email: %s.', $email);
        }

        $tiers = ServiceTier::query()->with('category')->get();
        if ($tiers->isNotEmpty()) {
            $lines[] = 'Services: '.$tiers
                ->map(fn (ServiceTier $tier) => sprintf(
                    '%s (%s%s)',
                    $tier->name,
                    $tier->price !== null && $tier->price !== '' ? '$'.$tier->price : 'custom',
                    $tier->billing ? '/'.$tier->billing : ''
                ))
                ->implode('; ');
        }

        $packages = TechPackage::query()->whereNotNull('price')->orderBy('sort_order')->get();
        if ($packages->isNotEmpty()) {
            $lines[] = 'Packages: '.$packages
                ->map(fn (TechPackage $package) => sprintf(
                    '%s (%s, %s)',
                    $package->title,
                    $package->displayPrice(),
                    $package->type
                ))
                ->implode('; ');
        }

        $faqs = Faq::query()->orderBy('sort_order')->limit(8)->get();
        foreach ($faqs as $faq) {
            $lines[] = sprintf('FAQ: %s -> %s', $faq->question, $faq->answer);
        }

        return implode("\n", $lines);
    }

    public function fallbackReply(string $question): string
    {
        $q = mb_strtolower($question);

        if (str_contains($q, 'price') || str_contains($q, 'pricing') || str_contains($q, 'cost') || str_contains($q, 'how much')) {
            return 'For budget fit, start with the Pro tier — it balances speed, architecture quality, and scalability. Packages are listed on the home page with full pricing.';
        }

        if (str_contains($q, 'deliver') || str_contains($q, 'timeline') || str_contains($q, 'long')) {
            return 'Typical delivery is 7-14 days based on complexity, revisions, and integration needs. Share your ticket ID for a precise milestone readout.';
        }

        if (str_contains($q, 'package') || str_contains($q, 'service') || str_contains($q, 'stack')) {
            return 'For full-stack projects, the Web Development Pro package plus SEO Starter gives the best launch coverage.';
        }

        if (str_contains($q, 'status') || str_contains($q, 'ticket') || str_contains($q, 'order')) {
            return 'Your order status is available in the customer panel under Invoices. Share a ticket ID for a detailed milestone readout.';
        }

        return 'Thanks for reaching out. Ask me about pricing, delivery timelines, recommended packages, or your order status — or open a support ticket for personal help.';
    }
}
