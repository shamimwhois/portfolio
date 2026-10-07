<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\FeedPost;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Feed extends Component
{
    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public string $search = '';

    public string $statusFilter = 'all';

    public function setStatus(FeedPost $post, string $status): void
    {
        if (! in_array($status, FeedPost::STATUSES, true)) {
            return;
        }

        $post->update(['publish_status' => $status]);
        ActivityLog::record('admin.feed.status_changed', auth()->user(), ['post' => $post->id, 'status' => $status]);
        session()->flash('status', 'Feed post marked as '.ucfirst($status).'.');
    }

    public function delete(FeedPost $post): void
    {
        foreach ($post->media as $media) {
            Storage::disk('local')->delete($media->path);
        }

        $post->reactions()->delete();
        $post->delete();

        ActivityLog::record('admin.feed.deleted', auth()->user(), ['post' => $post->id]);
        session()->flash('status', 'Feed post deleted.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function updatedSearch(): void
    {
        $this->limit = 10;
    }

    public function updatedStatusFilter(): void
    {
        $this->limit = 10;
    }

    #[Computed]
    public function posts()
    {
        return FeedPost::query()
            ->with(['author', 'media'])
            ->withCount(['reactions', 'comments'])
            ->when($this->search, fn ($q) => $q->where('body', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('publish_status', $this->statusFilter))
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->posts->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return FeedPost::query()
            ->when($this->search, fn ($q) => $q->where('body', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('publish_status', $this->statusFilter))
            ->count();
    }

    public function render()
    {
        return view('livewire.admin.feed');
    }
}
