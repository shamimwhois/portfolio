<div id="comment-{{ $comment->id }}" class="{{ isset($isReply) && $isReply ? 'ml-6 sm:ml-10' : '' }}" @guest x-data="{ prompt: false }" @endguest>
    <div class="flex items-start gap-2.5 rounded-xl border border-white/40 bg-white/30 px-3 py-2.5 backdrop-blur-md dark:border-white/5 dark:bg-white/[0.03]">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-500/15 text-xs font-semibold text-brand-700 dark:text-brand-300">
            {{ strtoupper(substr($comment->author?->name ?? '?', 0, 1)) }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ $comment->author?->name ?? 'Former member' }}</p>
            <p class="mt-0.5 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $comment->body }}</p>
            <div class="mt-1.5 flex items-center gap-3">
                <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                <div class="relative" x-data="{ open: false }">
                    <button
                        type="button"
                        @auth
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            @click="open = !open"
                            wire:click="reactToComment({{ $comment->id }}, '{{ auth()->check() ? ($comment->reactionBy(auth()->id()) ?? 'like') : 'like' }}')"
                        @endauth
                        @guest @click="prompt = true" @endguest
                        class="flex items-center gap-1 text-[11px] font-medium transition-colors {{ auth()->check() && $comment->reactionBy(auth()->id()) ? 'text-brand-600 dark:text-brand-400' : 'text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-200' }}"
                        aria-label="React to comment"
                    >
                        @php $myReaction = auth()->check() ? $comment->reactionBy(auth()->id()) : null; @endphp
                        @if ($myReaction)
                            <span class="text-sm leading-none">{{ $reactionEmoji($myReaction) }}</span>
                        @else
                            <x-icon name="heart" class="icon h-3 w-3" />
                        @endif
                        <span>{{ $comment->reactions_count }}</span>
                    </button>
                    @auth
                    <div
                        x-show="open"
                        x-cloak
                        @mouseenter="open = true"
                        @mouseleave="open = false"
                        @click.outside="open = false"
                        class="absolute bottom-full left-0 z-10 mb-1.5 flex items-center gap-0.5 rounded-xl border border-slate-200/60 bg-white/95 px-1.5 py-1 shadow-lg shadow-slate-900/10 backdrop-blur-md dark:border-white/10 dark:bg-slate-900/95"
                    >
                        @foreach (\App\Models\FeedLike::TYPES as $type)
                            <button type="button" wire:click="reactToComment({{ $comment->id }}, '{{ $type }}')" wire:key="creact-{{ $comment->id }}-{{ $type }}" class="rounded-full px-0.5 text-base transition-transform duration-150 hover:scale-125" aria-label="React with {{ $type }}">
                                {{ $reactionEmoji($type) }}
                            </button>
                        @endforeach
                    </div>
                    @endauth
                </div>
                <button
                    type="button"
                    @auth wire:click="openReply({{ $comment->id }})" @endauth
                    @guest @click="prompt = true" @endguest
                    class="text-[11px] font-medium text-slate-400 transition-colors hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-200"
                >
                    Reply
                </button>
                @if (auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $comment->user_id))
                    <button
                        type="button"
                        wire:click="deleteComment({{ $comment->id }})"
                        wire:confirm="Delete this comment?"
                        class="text-[11px] font-medium text-slate-400 transition-colors hover:text-red-600 dark:text-slate-500 dark:hover:text-red-400"
                    >
                        Delete
                    </button>
                @endif
            </div>
            @guest
                <div x-show="prompt" x-cloak x-transition class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                    <x-icon name="lock" class="icon h-3.5 w-3.5 shrink-0 text-brand-600 dark:text-brand-400" />
                    <span>
                        <a href="{{ route('login', ['redirect' => '/'.request()->path()]) }}" class="font-semibold text-brand-600 hover:underline dark:text-brand-400">Log in</a>
                        to like or reply to comments.
                    </span>
                </div>
            @endguest
        </div>
    </div>

    @if ($activeReplyCommentId === $comment->id)
        <form wire:submit="postReply" class="mt-2 flex items-start gap-2.5 ml-6 sm:ml-10">
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
        @include('livewire.feed.partials.comment', ['comment' => $reply, 'post' => $post, 'isReply' => true])
    @endforeach
</div>
