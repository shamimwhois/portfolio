<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Conversations extends Component
{
    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public string $filter = 'open';

    public ?int $conversationId = null;

    public string $reply = '';

    protected function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'max:4000'],
        ];
    }

    public function select(int $id): void
    {
        $this->conversationId = $id;
        $this->reply = '';
    }

    public function sendReply(): void
    {
        $this->validate();

        $conversation = $this->selectedConversation;

        $conversation->messages()->create([
            'role' => SupportMessage::ROLE_ASSISTANT,
            'body' => $this->reply,
        ]);

        $conversation->update([
            'status' => SupportConversation::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        ActivityLog::record('support.replied', auth()->user(), [
            'conversation' => $conversation->id,
        ]);

        $this->reply = '';

        session()->flash('status', 'Reply sent.');
    }

    public function close(SupportConversation $conversation): void
    {
        $conversation->update(['status' => SupportConversation::STATUS_CLOSED]);

        ActivityLog::record('support.closed', auth()->user(), ['conversation' => $conversation->id]);

        session()->flash('status', 'Conversation closed.');
    }

    public function reopen(SupportConversation $conversation): void
    {
        $conversation->update(['status' => SupportConversation::STATUS_OPEN]);

        session()->flash('status', 'Conversation reopened.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function updatedFilter(): void
    {
        $this->limit = 10;
    }

    #[Computed]
    public function conversations()
    {
        return SupportConversation::query()
            ->with(['user', 'messages' => fn ($q) => $q->latest('id')->limit(1)])
            ->withCount('messages')
            ->when($this->filter === 'open', fn ($q) => $q->where('status', SupportConversation::STATUS_OPEN))
            ->when($this->filter === 'closed', fn ($q) => $q->where('status', SupportConversation::STATUS_CLOSED))
            ->latest('last_message_at')
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->conversations->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return SupportConversation::query()
            ->when($this->filter === 'open', fn ($q) => $q->where('status', SupportConversation::STATUS_OPEN))
            ->when($this->filter === 'closed', fn ($q) => $q->where('status', SupportConversation::STATUS_CLOSED))
            ->count();
    }

    #[Computed]
    public function selectedConversation(): ?SupportConversation
    {
        if ($this->conversationId === null) {
            return null;
        }

        return SupportConversation::query()->find($this->conversationId);
    }

    #[Computed]
    public function messages()
    {
        return $this->selectedConversation?->oldestMessages()->get() ?? collect();
    }

    public function render()
    {
        return view('livewire.admin.conversations');
    }
}
