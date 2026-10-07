<?php

namespace App\Livewire\Support;

use App\Models\Setting;
use App\Models\SupportConversation;
use App\Support\SupportAssistant;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Assistant extends Component
{
    public ?int $conversationId = null;

    public string $question = '';

    public bool $open = false;

    public string $guestEmail = '';

    public string $guestPhone = '';

    protected function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:2000'],
            'guestEmail' => ['nullable', 'email', 'max:190'],
            'guestPhone' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function start(): void
    {
        $this->open = true;
        $this->conversationId = $this->resolveConversation()->id;
    }

    public function submitContact(): void
    {
        $this->validate([
            'guestEmail' => ['required', 'email', 'max:190'],
            'guestPhone' => ['required', 'string', 'max:40'],
        ]);

        $conversation = $this->resolveConversation();

        $conversation->update([
            'guest_email' => trim($this->guestEmail),
            'guest_phone' => trim($this->guestPhone),
        ]);
    }

    public function send(): void
    {
        if ($this->contactRequired()) {
            return;
        }

        $this->validate(['question' => ['required', 'string', 'max:2000']]);

        $conversation = $this->resolveConversation();

        $reply = app(SupportAssistant::class)->answer($conversation, $this->question);

        $this->conversationId = $conversation->id;
        $this->question = '';

        $this->dispatch('assistant-received', reply: $reply);
    }

    public function resetConversation(): void
    {
        if ($this->conversation) {
            $this->conversation->update(['status' => SupportConversation::STATUS_CLOSED]);
        }

        $this->conversationId = null;
        $this->question = '';
        $this->open = false;
    }

    public function sendQuick(string $key): void
    {
        $this->question = match ($key) {
            'pricing' => 'What are your pricing options?',
            'delivery' => 'What is the delivery timeline?',
            'package' => 'Which package do you recommend?',
            'status' => 'What is my order status?',
            default => $this->question,
        };

        $this->send();
    }

    public function contactRequired(): bool
    {
        // Logged-in users are already identified through their profile.
        if (auth()->check()) {
            return false;
        }

        // Query the live row directly (not the computed property) so the gate
        // reflects details saved earlier in the same widget session.
        if ($this->conversationId === null) {
            return true;
        }

        $conversation = SupportConversation::query()->find($this->conversationId);

        return $conversation === null || ! $conversation->guest_email;
    }

    #[Computed]
    public function conversation(): ?SupportConversation
    {
        if ($this->conversationId === null) {
            return null;
        }

        $conversation = SupportConversation::query()->find($this->conversationId);

        return $conversation && $this->owns($conversation) ? $conversation : null;
    }

    #[Computed]
    public function messages()
    {
        return $this->conversation?->oldestMessages()->get() ?? collect();
    }

    private function resolveConversation(): SupportConversation
    {
        if ($this->conversationId !== null) {
            $existing = SupportConversation::query()->find($this->conversationId);

            if ($existing && $this->owns($existing)) {
                return $existing;
            }

            $this->conversationId = null;
        }

        $conversation = SupportConversation::query()
            ->ownedBy(auth()->id(), session()->getId())
            ->open()
            ->latest('id')
            ->first();

        $conversation ??= SupportConversation::query()->create([
            'user_id' => auth()->id(),
            'session_id' => auth()->check() ? null : session()->getId(),
            // Carry last-known contact into a new chat so repeat guests
            // don't have to re-enter their details.
            'guest_email' => auth()->check() ? null : (trim($this->guestEmail) ?: null),
            'guest_phone' => auth()->check() ? null : (trim($this->guestPhone) ?: null),
            'title' => 'Support Chat',
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'hud',
        ]);

        $this->conversationId = $conversation->id;

        return $conversation;
    }

    private function owns(SupportConversation $conversation): bool
    {
        if (auth()->check()) {
            return $conversation->user_id === auth()->id();
        }

        return $conversation->user_id === null
            && $conversation->session_id === session()->getId();
    }

    public function render()
    {
        return view('livewire.support.assistant', [
            'assistantEnabled' => app(SupportAssistant::class)->isEnabled(),
            'assistantName' => (string) Setting::get('ai_name', 'Support AI'),
            'assistantWelcome' => (string) Setting::get('ai_welcome', 'Ask me about pricing, delivery, or packages.'),
        ]);
    }
}
