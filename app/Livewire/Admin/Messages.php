<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Messages extends Component
{
    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public ?int $openId = null;

    public string $filter = 'all';

    public function open(ContactMessage $message): void
    {
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        $this->openId = $message->id;
    }

    public function markUnread(ContactMessage $message): void
    {
        $message->update(['is_read' => false]);
    }

    public function delete(ContactMessage $message): void
    {
        ActivityLog::record('admin.message.deleted', auth()->user(), ['subject' => $message->subject]);
        $message->delete();
        session()->flash('status', 'Message deleted.');
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
    public function messages()
    {
        return ContactMessage::query()
            ->when($this->filter === 'unread', fn ($q) => $q->where('is_read', false))
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->messages->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return ContactMessage::query()
            ->when($this->filter === 'unread', fn ($q) => $q->where('is_read', false))
            ->count();
    }

    public function render()
    {
        return view('livewire.admin.messages');
    }
}
