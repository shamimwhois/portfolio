<?php

namespace App\Livewire\Panel;

use App\Models\SupportConversation;
use App\Support\SupportAssistant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Support extends Component
{
    public ?int $conversationId = null;

    public string $message = '';

    protected function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function mount(): void
    {
        $this->conversationId = SupportConversation::query()
            ->ownedBy(auth()->id(), null)
            ->latest('id')
            ->value('id');
    }

    public function openConversation(int $id): void
    {
        $this->conversationId = $id;
        $this->message = '';
    }

    public function newConversation(): void
    {
        $conversation = SupportConversation::query()->create([
            'user_id' => auth()->id(),
            'title' => 'Support Chat',
            'status' => SupportConversation::STATUS_OPEN,
            'source' => 'panel',
        ]);

        $this->conversationId = $conversation->id;
        $this->message = '';
    }

    public function send(): void
    {
        $this->validate();

        $conversation = $this->selectedConversation;

        $reply = app(SupportAssistant::class)->answer($conversation, $this->message);

        $this->message = '';

        $this->dispatch('panel-chat-received', reply: $reply);
    }

    public function closeConversation(): void
    {
        $conversation = $this->selectedConversation;
        $conversation->update(['status' => SupportConversation::STATUS_CLOSED]);
        $this->message = '';
    }

    #[Computed]
    public function conversations()
    {
        return SupportConversation::query()
            ->ownedBy(auth()->id(), null)
            ->withCount('messages')
            ->latest('last_message_at')
            ->get();
    }

    #[Computed]
    public function selectedConversation(): ?SupportConversation
    {
        if ($this->conversationId === null) {
            return null;
        }

        $conversation = SupportConversation::query()->find($this->conversationId);

        abort_unless($conversation && $conversation->user_id === auth()->id(), 404);

        return $conversation;
    }

    #[Computed]
    public function messages()
    {
        return $this->selectedConversation?->oldestMessages()->get() ?? collect();
    }

    public function render()
    {
        return view('livewire.panel.support');
    }
}
