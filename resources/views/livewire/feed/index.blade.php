@php
    use App\Models\FeedLike;
    use App\Models\Setting;

    $reactionEmoji = fn (string $type): string => match ($type) {
        'love' => '❤️',
        'haha' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
        default => '👍',
    };
@endphp

<div class="container-shell py-10 sm:py-14" x-data="mediaViewer" @open-viewer.window="openViewer($event.detail)">
    <div class="mx-auto max-w-2xl">
        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="text-sm font-medium uppercase tracking-wider text-brand-600 dark:text-brand-400">Community feed</p>
                <h1 class="section-heading mt-2">Share. Connect. Discuss.</h1>
                <p class="section-lead">Posts, updates and conversations from {{ Setting::get('site_name', 'the studio') }} — join in.</p>
            </div>
        </div>

        @if (auth()->check())
            <form wire:submit="createPost" class="card card-pad mt-8">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-700 text-sm font-semibold text-white shadow-md shadow-brand-600/30 ring-1 ring-inset ring-white/25">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1" x-data="{ len: 0 }" x-init="const el = $refs.body; len = (el?.value ?? '').length; el?.addEventListener('input', () => len = el.value.length); $wire.on('feed.post-created', () => { len = 0; })">
                        <label for="feed-body" class="sr-only">What's on your mind?</label>
                        <textarea
                            id="feed-body"
                            x-ref="body"
                            wire:model="body"
                            rows="3"
                            class="input resize-none"
                            placeholder="What's on your mind, {{ auth()->user()->name }}?"
                            maxlength="2000"
                        ></textarea>

                        @if ($images || $video || $documents)
                            <div class="mt-3 space-y-2">
                                @if ($images)
                                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                                        @foreach ($images as $index => $image)
                                            <div class="group relative overflow-hidden rounded-xl" wire:key="preview-{{ $loop->index }}-{{ $image->getFilename() }}">
                                                @if ($image->isPreviewable())
                                                    <img src="{{ $image->temporaryUrl() }}" alt="Attachment preview {{ $index + 1 }}" class="aspect-square w-full object-cover" />
                                                @else
                                                    <div class="flex aspect-square w-full flex-col items-center justify-center gap-1 bg-slate-500/10 p-2 text-slate-400 dark:bg-white/5">
                                                        <x-icon name="file" class="icon h-5 w-5 shrink-0" />
                                                        <span class="w-full truncate text-center text-[10px]">{{ $image->getClientOriginalName() }}</span>
                                                    </div>
                                                @endif
                                                <button
                                                    type="button"
                                                    wire:click="removeImage({{ $index }})"
                                                    class="absolute right-1.5 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-slate-950/60 text-white opacity-0 transition-opacity hover:opacity-100 group-hover:opacity-100"
                                                    aria-label="Remove image"
                                                >
                                                    <x-icon name="close" class="icon h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($video)
                                    @if ($video->isPreviewable())
                                        <div class="group relative overflow-hidden rounded-xl" wire:key="video-preview">
                                            <video src="{{ $video->temporaryUrl() }}" controls preload="metadata" class="aspect-video w-full rounded-xl bg-slate-950/5 dark:bg-white/5"></video>
                                            <button
                                                type="button"
                                                wire:click="removeVideo"
                                                class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-slate-950/60 text-white opacity-0 transition-opacity hover:opacity-100 group-hover:opacity-100"
                                                aria-label="Remove video"
                                            >
                                                <x-icon name="close" class="icon h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    @else
                                        <div class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white/60 px-3 py-2.5 dark:border-white/10 dark:bg-white/5" wire:key="video-preview">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-300">
                                                <x-icon name="video" class="icon h-4 w-4" />
                                            </span>
                                            <span class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ $video->getClientOriginalName() }}</span>
                                            <button
                                                type="button"
                                                wire:click="removeVideo"
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-500/10 hover:text-slate-700 dark:hover:text-white"
                                                aria-label="Remove video"
                                            >
                                                <x-icon name="close" class="icon h-4 w-4" />
                                            </button>
                                        </div>
                                    @endif
                                @endif
                                @if ($documents)
                                    <div class="space-y-1.5" wire:key="doc-previews">
                                        @foreach ($documents as $index => $document)
                                            <div class="group flex items-center gap-3 rounded-xl border border-slate-200/60 bg-white/60 px-3 py-2.5 dark:border-white/10 dark:bg-white/5" wire:key="doc-preview-{{ $loop->index }}-{{ $document->getFilename() }}">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-300">
                                                    <x-icon name="file" class="icon h-4 w-4" />
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ $document->getClientOriginalName() }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($document->getSize() / 1024, 1) }} KB</p>
                                                </div>
                                                <button
                                                    type="button"
                                                    wire:click="removeDocument({{ $index }})"
                                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-500/10 hover:text-slate-700 dark:hover:text-white"
                                                    aria-label="Remove {{ $document->getClientOriginalName() }}"
                                                >
                                                    <x-icon name="close" class="icon h-4 w-4" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5">
                                <label class="btn btn-secondary btn-sm cursor-pointer">
                                    <x-icon name="image" class="icon h-3.5 w-3.5" />
                                    <span class="hidden sm:inline">Photo</span>
                                    <input type="file" wire:model="images" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only" />
                                </label>
                                <label class="btn btn-secondary btn-sm cursor-pointer">
                                    <x-icon name="video" class="icon h-3.5 w-3.5" />
                                    <span class="hidden sm:inline">Video</span>
                                    <input type="file" wire:model="video" accept="video/mp4,video/webm,video/ogg,video/quicktime" class="sr-only" />
                                </label>
                                <label class="btn btn-secondary btn-sm cursor-pointer">
                                    <x-icon name="file" class="icon h-3.5 w-3.5" />
                                    <span class="hidden sm:inline">File</span>
                                    <input type="file" wire:model="documents" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.md,.zip,.rar" class="sr-only" />
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    <span wire:ignore>
                                        <span x-text="len"></span>/2000
                                    </span>
                                </p>
                                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                                    <span wire:loading.remove wire:target="createPost">
                                        <x-icon name="send" class="icon h-3.5 w-3.5" /> Post
                                    </span>
                                    <span wire:loading wire:target="createPost">
                                        <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                        Posting...
                                    </span>
                                </button>
                            </div>
                        </div>
                        @error('body') <p class="error-text mt-2">{{ $message }}</p> @enderror
                        @error('images') <p class="error-text mt-2">{{ $message }}</p> @enderror
                        @error('images.*') <p class="error-text mt-2">{{ $message }}</p> @enderror
                        @error('video') <p class="error-text mt-2">{{ $message }}</p> @enderror
                        @error('documents') <p class="error-text mt-2">{{ $message }}</p> @enderror
                        @error('documents.*') <p class="error-text mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>
            </form>
        @else
            <div class="card card-pad mt-8 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-500/10 text-slate-500 dark:text-slate-400">
                        <x-icon name="chat" class="icon h-5 w-5" />
                    </span>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Join the conversation — <span class="font-semibold text-slate-900 dark:text-white">log in</span> to post, react and comment.
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('login', ['redirect' => '/'.request()->path()]) }}" class="btn btn-primary btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">Register</a>
                </div>
            </div>
        @endif

        <div class="mt-8 space-y-5">
            @forelse ($this->posts->take($this->limit) as $post)
                <article id="post-{{ $post->id }}" class="card card-pad spotlight" @guest x-data="{ prompt: false }" @endguest>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500/20 to-violet-500/20 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-400/30 dark:text-brand-300 dark:ring-brand-400/20">
                                {{ strtoupper(substr($post->author?->name ?? '?', 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $post->author?->name ?? 'Former member' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @if (auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $post->user_id))
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" class="icon-btn" wire:click="startEditPost({{ $post->id }})" aria-label="Edit post">
                                    <x-icon name="edit" class="icon h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="icon-btn"
                                    wire:click="deletePost({{ $post->id }})"
                                    wire:confirm="Delete this post? Its comments and likes will be removed too."
                                    aria-label="Delete post"
                                >
                                    <x-icon name="trash" class="icon h-4 w-4" />
                                </button>
                            </div>
                        @endif
                    </div>

                    @if ($editingPostId === $post->id)
                        <form wire:submit="saveEditPost" class="mt-4">
                            <textarea wire:model="editBody" rows="3" maxlength="2000" class="input resize-none" aria-label="Edit post body"></textarea>
                            @error('editBody') <p class="error-text mt-1">{{ $message }}</p> @enderror
                            <div class="mt-2 flex items-center justify-end gap-2">
                                <button type="button" wire:click="cancelEditPost" class="btn btn-ghost btn-sm">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 whitespace-pre-line text-[15px] leading-relaxed text-slate-700 dark:text-slate-300">{{ $post->body }}</p>
                    @endif

                    @php
                        $postMedia = $post->media;
                        $mediaImages = $postMedia->where('type', 'image')->values();
                        $mediaVideo = $postMedia->firstWhere('type', 'video');
                        $mediaDocs = $postMedia->where('type', 'document')->values();
                        // Only visuals go in the lightbox viewer — documents render as file cards.
                        $mediaItems = $postMedia->whereIn('type', ['image', 'video'])->map(fn ($m) => ['url' => route('feed.media', $m), 'type' => $m->type])->values();
                        $imageCount = $mediaImages->count();
                    @endphp
                    @if ($postMedia->isNotEmpty())
                        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/50 bg-slate-950/5 dark:border-white/10 dark:bg-white/5">
                            @if ($mediaVideo)
                                <video src="{{ route('feed.media', $mediaVideo) }}" controls preload="metadata" playsinline class="aspect-video w-full bg-slate-950 object-contain"></video>
                            @elseif ($imageCount === 1)
                                <button
                                    type="button"
                                    class="block w-full cursor-zoom-in"
                                    @click="$dispatch('open-viewer', { items: {{ \Illuminate\Support\Js::from($mediaItems) }}, index: 0 })"
                                    aria-label="Open image"
                                >
                                    <img src="{{ route('feed.media', $mediaImages[0]) }}" loading="lazy" alt="Post image" class="aspect-[16/10] w-full object-cover transition-transform duration-300 hover:scale-[1.02]" />
                                </button>
                            @else
                                <div class="grid grid-cols-2 gap-1.5">
                                    @foreach ($mediaImages as $i => $img)
                                        <button
                                            type="button"
                                            class="{{ $i === 0 && $imageCount === 3 ? 'col-span-2' : '' }} block w-full cursor-zoom-in overflow-hidden"
                                            @click="$dispatch('open-viewer', { items: {{ \Illuminate\Support\Js::from($mediaItems) }}, index: {{ $i }} })"
                                            aria-label="Open image {{ $i + 1 }}"
                                        >
                                            <img
                                                src="{{ route('feed.media', $img) }}"
                                                loading="lazy"
                                                alt="Post image {{ $i + 1 }}"
                                                class="{{ $i === 0 && $imageCount === 3 ? 'aspect-[16/9]' : 'aspect-[4/3]' }} w-full object-cover transition-transform duration-300 hover:scale-[1.02]"
                                            />
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($mediaDocs->isNotEmpty())
                        <div class="mt-2 divide-y divide-slate-200/60 overflow-hidden rounded-2xl border border-slate-200/60 bg-white/60 dark:divide-white/10 dark:border-white/10 dark:bg-white/5">
                            @foreach ($mediaDocs as $doc)
                                <a
                                    href="{{ route('feed.media', $doc) }}"
                                    download
                                    class="group flex items-center gap-3 px-3.5 py-3 transition-colors hover:bg-white/70 dark:hover:bg-white/10"
                                    title="Download {{ $doc->filename ?? $doc->path }}"
                                >
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-300">
                                        <x-icon name="file" class="icon h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-slate-800 dark:text-slate-200">{{ $doc->filename ?? basename($doc->path) }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ strtoupper(substr(pathinfo($doc->filename ?? $doc->path, PATHINFO_EXTENSION), 0, 8)) ?: 'FILE' }}</span>
                                    </span>
                                    <x-icon name="download" class="icon h-4 w-4 shrink-0 text-slate-400 transition-colors group-hover:text-brand-600 dark:group-hover:text-brand-300" />
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 flex items-center gap-1 border-t border-slate-200/60 pt-3 dark:border-white/10">
                        <div class="relative" x-data="{ open: false }">
                            <button
                                type="button"
                                @auth
                                    @mouseenter="open = true"
                                    @mouseleave="open = false"
                                    @click="open = !open"
                                    wire:click="reactToPost({{ $post->id }}, '{{ auth()->check() ? ($post->reactionBy(auth()->id()) ?? 'like') : 'like' }}')"
                                @endauth
                                @guest @click="prompt = true" @endguest
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium transition-colors {{ auth()->check() && $post->reactionBy(auth()->id()) ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 hover:bg-white/60 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                                aria-label="React to post"
                            >
                                @php $myReaction = auth()->check() ? $post->reactionBy(auth()->id()) : null; @endphp
                                @if ($myReaction)
                                    <span class="text-base leading-none">{{ $reactionEmoji($myReaction) }}</span>
                                @else
                                    <x-icon name="heart" class="icon h-4 w-4" />
                                @endif
                                <span>{{ $post->reactions_count }}</span>
                            </button>
                            @auth
                            <div
                                x-show="open"
                                x-cloak
                                @mouseenter="open = true"
                                @mouseleave="open = false"
                                @click.outside="open = false"
                                class="absolute bottom-full left-0 z-10 mb-2 flex items-center gap-1 rounded-2xl border border-slate-200/60 bg-white/95 px-2 py-1.5 shadow-lg shadow-slate-900/10 backdrop-blur-md dark:border-white/10 dark:bg-slate-900/95"
                            >
                                @foreach (FeedLike::TYPES as $type)
                                    <button type="button" wire:click="reactToPost({{ $post->id }}, '{{ $type }}')" wire:key="react-{{ $post->id }}-{{ $type }}" class="rounded-full p-1 text-xl transition-transform duration-150 hover:scale-125" aria-label="React with {{ $type }}">
                                        {{ $reactionEmoji($type) }}
                                    </button>
                                @endforeach
                            </div>
                            @endauth
                        </div>

                        @php $reactionTotals = $post->reactionTotals(); @endphp
                        @if (array_sum($reactionTotals) > 0)
                            <span class="ml-1 flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400" title="Reactions">
                                <span class="flex -space-x-1">
                                    @foreach (array_slice(array_keys($reactionTotals), 0, 3) as $type)
                                        <span class="text-sm leading-none">{{ $reactionEmoji($type) }}</span>
                                    @endforeach
                                </span>
                                <span>{{ array_sum($reactionTotals) }}</span>
                            </span>
                        @endif

                        <button
                            type="button"
                            wire:click="openComment({{ $post->id }})"
                            class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-500 transition-colors hover:bg-white/60 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white"
                        >
                            <x-icon name="chat" class="icon h-4 w-4" />
                            <span>Comment {{ $post->comments_count > 0 ? '('.$post->comments_count.')' : '' }}</span>
                        </button>

                        <div class="relative ml-auto" x-data="{ shareOpen: false, copied: false }">
                            <button
                                type="button"
                                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-slate-500 transition-colors hover:bg-white/60 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white"
                                @click="shareOpen = !shareOpen"
                                aria-label="Share post"
                            >
                                <x-icon name="share" class="icon h-4 w-4" />
                                <span>Share</span>
                            </button>
                            <div
                                x-show="shareOpen"
                                x-cloak
                                x-transition
                                @click.outside="shareOpen = false"
                                class="absolute bottom-full right-0 z-10 mb-2 w-60 rounded-2xl border border-slate-200/60 bg-white/95 p-1.5 shadow-lg shadow-slate-900/10 backdrop-blur-md dark:border-white/10 dark:bg-slate-900/95"
                            >
                                @php
                                    $shareUrl = route('feed').'#post-'.$post->id;
                                    $shareText = 'Check out this post on '.Setting::get('site_name', 'the studio');
                                @endphp
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5">
                                    <x-icon name="facebook" class="icon h-4 w-4 text-[#1877f2]" /> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5">
                                    <x-icon name="x" class="icon h-4 w-4 text-slate-400" /> Post on X
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5">
                                    <x-icon name="whatsapp" class="icon h-4 w-4 text-[#25d366]" /> WhatsApp
                                </a>
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5"
                                    @click="navigator.clipboard?.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                >
                                    <x-icon name="link" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                                    <span x-text="copied ? 'Copied!' : 'Copy link'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    @guest
                        <div x-show="prompt" x-cloak x-transition class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50/70 px-4 py-3 dark:border-brand-400/20 dark:bg-brand-500/10">
                            <p class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <x-icon name="lock" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                                Log in to like this post.
                            </p>
                            <div class="flex gap-2">
                                <a href="{{ route('login', ['redirect' => '/'.request()->path()]) }}" class="btn btn-primary btn-sm">Log in</a>
                                <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">Register</a>
                            </div>
                        </div>
                    @endguest

                    @if ($activeCommentPostId === $post->id)
                        <div class="mt-4 space-y-4 border-t border-slate-200/60 pt-4 dark:border-white/10">
                            @auth
                            <form wire:submit="postComment" class="flex items-start gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-500/15 text-xs font-semibold text-brand-700 dark:text-brand-300">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <input
                                        type="text"
                                        wire:model="commentBody"
                                        class="input !py-2"
                                        placeholder="Write a comment..."
                                        maxlength="1000"
                                        required
                                    />
                                    @error('commentBody') <p class="error-text mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Post</button>
                            </form>
                            @else
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50/70 px-4 py-3 dark:border-brand-400/20 dark:bg-brand-500/10">
                                <p class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <x-icon name="lock" class="icon h-4 w-4 text-brand-600 dark:text-brand-400" />
                                    Log in to join the conversation.
                                </p>
                                <div class="flex gap-2">
                                    <a href="{{ route('login', ['redirect' => '/'.request()->path()]) }}" class="btn btn-primary btn-sm">Log in</a>
                                    <a href="{{ route('register') }}" class="btn btn-secondary btn-sm">Register</a>
                                </div>
                            </div>
                            @endauth

                            @forelse ($post->comments as $comment)
                                @include('livewire.feed.partials.comment', ['comment' => $comment, 'post' => $post])
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">No comments yet — be the first.</p>
                            @endforelse
                        </div>
                    @endif
                </article>
            @empty
                <div class="card card-pad py-12 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-500/10 text-slate-400">
                        <x-icon name="chat" class="icon h-6 w-6" />
                    </span>
                    <p class="mt-3 font-semibold text-slate-900 dark:text-white">No posts yet</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Be the first to share something with the community.</p>
                </div>
            @endforelse
        </div>

        @if ($this->hasMore)
            <div class="mt-8 flex flex-col items-center gap-2">
                <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary">
                    <x-icon name="plus" class="icon h-4 w-4" /> Load more posts
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
            </div>
        @endif

        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-md"
            @click.self="close"
            @keydown.escape.window="onEscape"
            @keydown.arrow-right.window="next"
            @keydown.arrow-left.window="prev"
        >
            <button type="button" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20" @click="close" aria-label="Close viewer">
                <x-icon name="close" class="icon h-5 w-5" />
            </button>
            <button type="button" x-show="items.length > 1" class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20" @click="prev" aria-label="Previous media">
                <x-icon name="chevron-left" class="icon h-5 w-5" />
            </button>
            <button type="button" x-show="items.length > 1" class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20" @click="next" aria-label="Next media">
                <x-icon name="chevron-right" class="icon h-5 w-5" />
            </button>
            <template x-for="(item, i) in items" :key="i">
                <div x-show="i === index" x-transition class="flex max-h-[88vh] max-w-[92vw] items-center justify-center">
                    <img x-show="item.type === 'image'" :src="item.url" :alt="'Media ' + (i + 1)" class="max-h-[88vh] max-w-[92vw] rounded-xl object-contain shadow-2xl" />
                    <video x-show="item.type === 'video'" :src="item.url" controls autoplay playsinline class="max-h-[88vh] max-w-[92vw] rounded-xl"></video>
                </div>
            </template>
            <p x-show="items.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-xs text-white" x-text="(index + 1) + ' / ' + items.length"></p>
        </div>
    </div>
</div>
