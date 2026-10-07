<div id="blog-comment-{{ $comment->id }}" class="{{ isset($isReply) && $isReply ? 'ml-6 sm:ml-10' : '' }}">
    <div class="flex items-start gap-3 rounded-2xl border border-white/50 bg-white/40 px-4 py-3.5 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500/20 to-violet-500/20 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
            {{ strtoupper(substr($comment->author?->name ?? '?', 0, 1)) }}
        </span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $comment->author?->name ?? 'Former member' }}</p>
                @if ($comment->author?->isAdmin())
                    <span class="badge badge-brand">Author</span>
                @endif
                <span class="text-xs text-slate-400 dark:text-slate-500">· {{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ $comment->body }}</p>
            <div class="mt-2 flex items-center gap-3">
                @auth
                    <button
                        type="button"
                        wire:click="openReply({{ $comment->id }})"
                        class="text-xs font-semibold text-brand-600 transition-colors hover:text-brand-700 dark:text-brand-400"
                    >
                        Reply
                    </button>
                @endauth
                @auth
                    @if (auth()->user()->isAdmin() || auth()->id() === $comment->user_id)
                        <button
                            type="button"
                            wire:click="deleteComment({{ $comment->id }})"
                            wire:confirm="Delete this comment? Its replies will be removed too."
                            class="text-xs font-semibold text-slate-400 transition-colors hover:text-red-600 dark:text-slate-500 dark:hover:text-red-400"
                        >
                            Delete
                        </button>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    @if ($activeReplyId === $comment->id)
        <form wire:submit="postReply" class="mt-2.5 ml-6 flex items-start gap-2.5 sm:ml-10">
            <input
                type="text"
                wire:model="replyBody"
                class="input !py-2"
                placeholder="Reply to {{ $comment->author?->name ?? 'this comment' }}..."
                maxlength="1000"
                required
            />
            @error('replyBody') <p class="error-text mt-1">{{ $message }}</p> @enderror
            <button type="submit" class="btn btn-primary btn-sm shrink-0">Reply</button>
        </form>
    @endif

    @foreach ($comment->replies as $reply)
        @include('livewire.blog.partials.comment', ['comment' => $reply, 'isReply' => true])
    @endforeach
</div>
