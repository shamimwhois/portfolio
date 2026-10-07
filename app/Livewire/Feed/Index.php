<?php

namespace App\Livewire\Feed;

use App\Models\ActivityLog;
use App\Models\FeedComment;
use App\Models\FeedLike;
use App\Models\FeedMedia;
use App\Models\FeedPost;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.hud')]
class Index extends Component
{
    use WithFileUploads;

    public const MAX_IMAGES = 6;

    public const MAX_DOCUMENTS = 3;

    public string $body = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $images = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $video = null;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $documents = [];

    public ?int $activeCommentPostId = null;

    public string $commentBody = '';

    public ?int $activeReplyCommentId = null;

    public string $replyBody = '';

    public ?int $editingPostId = null;

    public string $editBody = '';

    public ?string $error = null;

    /** @var int Posts per page chunk — pagination via Load more. */
    public int $limit = 10;

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->posts->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return FeedPost::query()->published()->count();
    }

    private function requireAuth(): bool
    {
        if (auth()->check()) {
            return true;
        }

        $this->redirect(route('login'));

        return false;
    }

    /**
     * Shared upload rules so the per-field hooks and createPost() stay in sync.
     *
     * `mimes` gates the file extension while `mimetypes` sniffs the real file
     * content, so a renamed HTML/script payload can't slip through as an image,
     * video or document.
     */
    private function imageRules(): array
    {
        return ['image', 'mimes:jpeg,png,webp,gif', 'max:10240'];
    }

    private function videoRules(): array
    {
        return [
            'file',
            'mimes:mp4,webm,ogg,ogv,mov',
            // finfo often reports .ogg/.ogv as audio/ogg or application/ogg —
            // include them so legit uploads aren't falsely rejected.
            'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime,audio/ogg,application/ogg',
            'max:65536',
        ];
    }

    private function documentRules(): array
    {
        return [
            'file',
            'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,md,zip,rar',
            'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,application/x-rar-compressed,application/vnd.rar,text/plain,text/csv,text/markdown',
            'max:20480',
        ];
    }

    private function safeName(string $name): string
    {
        $name = basename($name);
        $name = trim(preg_replace('/[^\w.\- ]+/u', '', $name) ?? 'file');

        return $name !== '' ? $name : 'file';
    }

    public function createPost(): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', FeedPost::class);

        $this->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'images' => ['array', 'max:'.self::MAX_IMAGES],
            'images.*' => $this->imageRules(),
            'video' => ['nullable', ...$this->videoRules()],
            'documents' => ['array', 'max:'.self::MAX_DOCUMENTS],
            'documents.*' => $this->documentRules(),
        ]);

        if (trim($this->body) === '' && empty($this->images) && ! $this->video && empty($this->documents)) {
            $this->addError('body', 'Write something or attach a photo, video or document.');

            return;
        }

        $post = FeedPost::query()->create([
            'user_id' => auth()->id(),
            'body' => trim($this->body),
            'publish_status' => FeedPost::STATUS_PUBLISHED,
        ]);

        $position = 0;

        foreach ($this->images as $image) {
            $post->media()->create([
                'type' => FeedMedia::TYPE_IMAGE,
                'path' => $image->store('feed/'.$post->id, 'local'),
                'position' => $position++,
            ]);
        }

        if ($this->video) {
            $post->media()->create([
                'type' => FeedMedia::TYPE_VIDEO,
                'path' => $this->video->store('feed/'.$post->id, 'local'),
                'filename' => $this->safeName($this->video->getClientOriginalName()),
                'position' => $position++,
            ]);
        }

        foreach ($this->documents as $document) {
            $post->media()->create([
                'type' => FeedMedia::TYPE_DOCUMENT,
                'path' => $document->store('feed/'.$post->id, 'local'),
                'filename' => $this->safeName($document->getClientOriginalName()),
                'position' => $position++,
            ]);
        }

        ActivityLog::record('feed.post_created', auth()->user(), ['post' => $post->id]);

        $this->body = '';
        $this->images = [];
        $this->video = null;
        $this->documents = [];
        $this->error = null;

        $this->dispatch('feed.post-created', postId: $post->id);
    }

    public function updatedImages(): void
    {
        $this->validate([
            'images' => ['array', 'max:'.self::MAX_IMAGES],
            'images.*' => $this->imageRules(),
        ]);
    }

    public function updatedVideo(): void
    {
        $this->validate([
            'video' => $this->videoRules(),
        ]);
    }

    public function updatedDocuments(): void
    {
        $this->validate([
            'documents' => ['array', 'max:'.self::MAX_DOCUMENTS],
            'documents.*' => $this->documentRules(),
        ]);
    }

    public function removeImage(int $index): void
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            $this->images = array_values($this->images);
        }
    }

    public function removeVideo(): void
    {
        $this->video = null;
    }

    public function removeDocument(int $index): void
    {
        if (isset($this->documents[$index])) {
            unset($this->documents[$index]);
            $this->documents = array_values($this->documents);
        }
    }

    public function startEditPost(int $postId): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $post = FeedPost::query()->findOrFail($postId);

        $this->authorize('update', $post);

        $this->editingPostId = $postId;
        $this->editBody = $post->body;
    }

    public function cancelEditPost(): void
    {
        $this->editingPostId = null;
        $this->editBody = '';
    }

    public function saveEditPost(): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        if (! $this->editingPostId) {
            return;
        }

        $post = FeedPost::query()->findOrFail($this->editingPostId);

        $this->authorize('update', $post);

        $this->validate(['editBody' => ['required', 'string', 'max:2000']]);

        $post->update(['body' => trim($this->editBody)]);

        ActivityLog::record('feed.post_updated', auth()->user(), ['post' => $post->id]);

        $this->cancelEditPost();
    }

    public function reactToPost(int $postId, string $type): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', FeedLike::class);

        if (! in_array($type, FeedLike::TYPES, true)) {
            return;
        }

        $post = FeedPost::query()->findOrFail($postId);

        $reaction = $post->reactions()->where('user_id', auth()->id())->first();

        if ($reaction && $reaction->reaction_type === $type) {
            // Tapping the same reaction again removes it.
            $reaction->delete();

            return;
        }

        FeedLike::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'likeable_type' => FeedPost::class,
                'likeable_id' => $postId,
            ],
            ['reaction_type' => $type]
        );
    }

    public function reactToComment(int $commentId, string $type): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', FeedLike::class);

        if (! in_array($type, FeedLike::TYPES, true)) {
            return;
        }

        $comment = FeedComment::query()->findOrFail($commentId);

        $reaction = $comment->reactions()->where('user_id', auth()->id())->first();

        if ($reaction && $reaction->reaction_type === $type) {
            $reaction->delete();

            return;
        }

        FeedLike::query()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'likeable_type' => FeedComment::class,
                'likeable_id' => $commentId,
            ],
            ['reaction_type' => $type]
        );
    }

    public function openComment(int $postId): void
    {
        // Intentionally not auth-gated: guests may open a thread to READ the
        // comments (view-only). Writing is gated inside postComment() and
        // postReply() via requireAuth().
        if ($this->activeCommentPostId === $postId) {
            $this->activeCommentPostId = null;
            $this->commentBody = '';

            return;
        }

        $this->activeCommentPostId = $postId;
        $this->commentBody = '';
        $this->activeReplyCommentId = null;
        $this->replyBody = '';
    }

    public function postComment(): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', FeedComment::class);

        if (! $this->activeCommentPostId) {
            return;
        }

        $this->validate(['commentBody' => ['required', 'string', 'max:1000']]);

        FeedComment::query()->create([
            'user_id' => auth()->id(),
            'feed_post_id' => $this->activeCommentPostId,
            'body' => trim($this->commentBody),
        ]);

        ActivityLog::record('feed.comment_added', auth()->user());

        $this->commentBody = '';
        $this->activeCommentPostId = null;
    }

    public function openReply(int $commentId): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        if ($this->activeReplyCommentId === $commentId) {
            $this->activeReplyCommentId = null;
            $this->replyBody = '';

            return;
        }

        $this->activeReplyCommentId = $commentId;
        $this->replyBody = '';
        $this->activeCommentPostId = null;
        $this->commentBody = '';
    }

    public function postReply(): void
    {
        if (! $this->requireAuth()) {
            return;
        }

        $this->authorize('create', FeedComment::class);

        if (! $this->activeReplyCommentId) {
            return;
        }

        $parent = FeedComment::query()->findOrFail($this->activeReplyCommentId);

        $this->validate(['replyBody' => ['required', 'string', 'max:1000']]);

        // Flatten replies to the top-level comment (like Facebook) so
        // nesting is capped at two levels and the feed stays tidy.
        $root = $parent->parent_id ?? $parent->id;

        FeedComment::query()->create([
            'user_id' => auth()->id(),
            'feed_post_id' => $parent->feed_post_id,
            'parent_id' => $root,
            'body' => trim($this->replyBody),
        ]);

        ActivityLog::record('feed.reply_added', auth()->user());

        $this->replyBody = '';
        $this->activeReplyCommentId = null;
    }

    public function deletePost(int $postId): void
    {
        $post = FeedPost::query()->findOrFail($postId);

        $this->authorize('delete', $post);

        foreach ($post->media as $media) {
            Storage::disk('local')->delete($media->path);
        }

        $post->reactions()->delete();
        $post->delete();

        ActivityLog::record('feed.post_deleted', auth()->user(), ['post' => $postId]);
    }

    public function deleteComment(int $commentId): void
    {
        $comment = FeedComment::query()->findOrFail($commentId);

        $this->authorize('delete', $comment);

        $comment->reactions()->delete();
        $comment->delete();

        ActivityLog::record('feed.comment_deleted', auth()->user(), ['comment' => $commentId]);
    }

    #[Computed]
    public function posts()
    {
        return FeedPost::query()
            ->published()
            ->with([
                'author',
                'media',
                // Top-level comments only — replies are rendered nested via
                // the `replies` relation, so loading them here too would
                // render each reply twice.
                'comments' => fn ($q) => $q->whereNull('parent_id')->withCount('reactions')->with([
                    'author',
                    'reactions',
                    'replies' => fn ($q) => $q->withCount('reactions')->with(['author', 'reactions']),
                ]),
                'reactions',
            ])
            ->withCount(['reactions', 'comments'])
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    public function render()
    {
        return view('livewire.feed.index');
    }
}
