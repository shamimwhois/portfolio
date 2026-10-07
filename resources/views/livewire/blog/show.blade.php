<div class="container-shell max-w-3xl py-10 sm:py-14">
    <a href="{{ route('blog.index') }}" class="nav-link inline-flex items-center gap-1.5">
        <x-icon name="arrow-left" class="icon h-4 w-4" /> All articles
    </a>

    <article class="mt-8">
        <div class="flex flex-wrap gap-2">
            <span class="badge badge-brand uppercase">{{ $post->categoryLabel() }}</span>
            @foreach (($post->tags ?? []) as $t)
                <span class="badge badge-neutral">{{ $t }}</span>
            @endforeach
        </div>
        <h1 class="mt-4 font-display text-3xl font-bold leading-tight text-slate-900 sm:text-4xl dark:text-white">{{ $post->title }}</h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
            <span>{{ $post->author?->name }}</span> ·
            <span>{{ $post->published_at?->format('M d, Y') }}</span> ·
            <span>{{ $post->readTime() }} min read</span> ·
            <span class="inline-flex items-center gap-1"><x-icon name="eye" class="icon h-3.5 w-3.5" /> {{ $post->formattedViews() }} views</span>
        </p>

        @if ($post->cover_image)
            <div class="mt-6 overflow-hidden rounded-2xl border border-white/50 bg-white/40 backdrop-blur-md dark:border-white/10 dark:bg-white/5">
                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="h-64 w-full object-cover sm:h-80" />
            </div>
        @endif

        @if ($post->excerpt)
            <p class="mt-6 text-lg font-medium leading-relaxed text-slate-700 dark:text-slate-200">{{ $post->excerpt }}</p>
        @endif

        <div class="article-prose mt-6">
            {!! $post->body !!}
        </div>

        <div class="mt-8 border-t border-slate-200/60 pt-6 dark:border-white/10">
            <x-social-share
                :url="route('blog.show', $post)"
                :text="$post->title"
                label="Share this article"
            />
        </div>
    </article>

    <section class="mt-14" aria-label="Comments">
        <div class="flex items-center gap-2">
            <x-icon name="chat" class="icon h-5 w-5 text-brand-600 dark:text-brand-400" />
            <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">Comments</h2>
            @if (count($this->comments) > 0)
                <span class="badge badge-neutral">{{ count($this->comments) }}</span>
            @endif
        </div>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Join the discussion — thoughts, questions and replies.</p>

        <div class="mt-6 space-y-4">
            @auth
                <form wire:submit="postComment" class="card card-pad flex items-start gap-3 !p-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500/20 to-violet-500/20 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <label for="blog-comment-body" class="sr-only">Write a comment</label>
                        <textarea
                            id="blog-comment-body"
                            wire:model="commentBody"
                            rows="3"
                            class="input resize-none"
                            placeholder="Share your thoughts..."
                            maxlength="1000"
                        ></textarea>
                        @error('commentBody') <p class="error-text mt-1">{{ $message }}</p> @enderror
                        <div class="mt-2 flex items-center justify-end gap-3">
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ strlen($commentBody) }}/1000</p>
                            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                <span wire:loading.remove wire:target="postComment"><x-icon name="send" class="icon h-3.5 w-3.5" /> Post comment</span>
                                <span wire:loading wire:target="postComment">Posting...</span>
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="card card-pad flex flex-wrap items-center justify-between gap-3 !p-4">
                    <p class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-500/10 text-slate-500 dark:text-slate-400">
                            <x-icon name="lock" class="icon h-4 w-4" />
                        </span>
                        Comments are open to everyone — <span class="font-semibold text-slate-900 dark:text-white">log in</span> to join in.
                    </p>
                    <div class="flex gap-2">
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-primary btn-sm">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">Register</a>
                    </div>
                </div>
            @endauth

            <div class="space-y-4">
                @forelse ($this->comments as $comment)
                    @include('livewire.blog.partials.comment', ['comment' => $comment])
                @empty
                    <p class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">No comments yet — be the first to share your thoughts.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mt-16">
            <h2 class="font-display text-xl font-semibold text-slate-900 dark:text-white">Related articles</h2>
            <div class="mt-6 grid gap-6 md:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ route('blog.show', $item) }}" class="card card-hover block p-5">
                        <h3 class="font-display text-base font-semibold leading-snug text-slate-900 dark:text-white">{{ $item->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $item->excerptOrFallback(90) }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
