<?php

namespace App\Livewire\Blog;

use App\Models\ActivityLog;
use App\Models\BlogComment;
use App\Models\BlogPost;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hud')]
class Show extends Component
{
    public BlogPost $post;

    public ?int $activeReplyId = null;

    public string $commentBody = '';

    public string $replyBody = '';

    public function mount(BlogPost $post): void
    {
        abort_unless($post->published, 404);

        $post->increment('views');

        $this->post = $post->fresh();
    }

    private function requireAuth(): bool
    {
        if (auth()->check()) {
            return true;
        }

        $this->redirect(route('login'));

        return false;
    }

    public function postComment(): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', BlogComment::class);

        $this->validate(['commentBody' => ['required', 'string', 'max:1000']]);

        BlogComment::query()->create([
            'user_id' => auth()->id(),
            'blog_post_id' => $this->post->id,
            'body' => trim($this->commentBody),
        ]);

        ActivityLog::record('blog.comment_added', auth()->user(), ['post' => $this->post->title]);

        $this->commentBody = '';
    }

    public function openReply(int $commentId): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        if ($this->activeReplyId === $commentId) {
            $this->activeReplyId = null;
            $this->replyBody = '';

            return;
        }

        $this->activeReplyId = $commentId;
        $this->replyBody = '';
    }

    public function postReply(): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', BlogComment::class);

        if (! $this->activeReplyId) {
            return;
        }

        $parent = BlogComment::query()->findOrFail($this->activeReplyId);

        $this->validate(['replyBody' => ['required', 'string', 'max:1000']]);

        // Flatten replies to the top-level comment (like the feed) so nesting
        // stays capped at two levels.
        $root = $parent->parent_id ?? $parent->id;

        BlogComment::query()->create([
            'user_id' => auth()->id(),
            'blog_post_id' => $this->post->id,
            'parent_id' => $root,
            'body' => trim($this->replyBody),
        ]);

        ActivityLog::record('blog.reply_added', auth()->user(), ['post' => $this->post->title]);

        $this->replyBody = '';
        $this->activeReplyId = null;
    }

    public function deleteComment(int $commentId): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $comment = BlogComment::query()->findOrFail($commentId);

        $this->authorize('delete', $comment);

        // Cascade removes replies via the FK.
        $comment->delete();

        ActivityLog::record('blog.comment_deleted', auth()->user(), ['post' => $this->post->title]);
    }

    #[Computed]
    public function comments()
    {
        return $this->post->comments()
            ->withCount('replies')
            ->with(['author', 'replies' => fn ($q) => $q->with(['author'])])
            ->get();
    }

    public function render()
    {
        return view('livewire.blog.show', [
            'related' => BlogPost::query()
                ->where('published', true)
                ->whereKeyNot($this->post->id)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
