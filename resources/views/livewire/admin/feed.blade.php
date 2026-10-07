<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative w-full sm:max-w-sm">
            <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search feed posts..." aria-label="Search feed posts" />
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach (['all' => 'All', 'published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Unpublished'] as $key => $label)
                <button
                    type="button"
                    wire:click="set('statusFilter', '{{ $key }}')"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors {{ $statusFilter === $key ? 'border-brand-400/70 bg-brand-500/15 text-brand-700 dark:border-brand-400/60 dark:bg-brand-400/15 dark:text-brand-300' : 'border-white/50 bg-white/40 text-slate-600 backdrop-blur-md hover:border-white/80 hover:bg-white/70 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-white/20 dark:hover:bg-white/10' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <section class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Author</th>
                    <th>Media</th>
                    <th>Engagement</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->posts->take($this->limit) as $post)
                    <tr>
                        <td>
                            <p class="max-w-[24rem] whitespace-pre-line font-medium text-slate-900 dark:text-white">{{ \Illuminate\Support\Str::limit($post->body, 80) ?: '(media only)' }}</p>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $post->author?->name ?? 'Deleted user' }}</td>
                        <td>
                            @if ($post->media->count() > 0)
                                <span class="badge badge-neutral">{{ $post->media->count() }} file{{ $post->media->count() > 1 ? 's' : '' }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500 dark:text-slate-400">{{ $post->reactions_count }} reactions · {{ $post->comments_count }} comments</td>
                        <td>
                            <select
                                wire:change="setStatus({{ $post->id }}, $event.target.value)"
                                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $post->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($post->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                aria-label="Post status"
                                name="publish_status"
                            >
                                @foreach (\App\Models\FeedPost::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($post->publish_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $post->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <x-admin.actions :view="route('feed')" :delete="$post->id" delete-message="Delete this feed post? Comments and reactions will be removed too." delete-method="delete" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">No feed posts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($this->hasMore)
        <div class="mt-6 flex flex-col items-center gap-2">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" class="btn btn-secondary btn-sm">
                <x-icon name="plus" class="icon h-4 w-4" /> Load more posts
            </button>
            <p class="text-xs text-slate-500 dark:text-slate-400">Showing {{ min($this->limit, $this->matchingTotal) }} of {{ $this->matchingTotal }}</p>
        </div>
    @endif
</div>
