<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="relative w-full sm:max-w-sm">
            <x-icon name="search" class="icon pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input type="search" wire:model.live.debounce.300ms="search" class="input pl-10" placeholder="Search posts..." aria-label="Search posts" />
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New Post
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit Post' : 'New Post' }}" subtitle="Write and publish portfolio articles" icon="doc" size="lg" close-method="cancel">
            <form wire:submit="save" id="post-form" class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-admin.field label="Title" name="title" placeholder="Post title" required error="{{ $errors->first('title') }}" icon="edit" wire:model="title" />
                </div>
                <x-admin.field label="Slug" name="slug" placeholder="auto-generated from title" hint="Leave blank to auto-generate" required error="{{ $errors->first('slug') }}" wire:model="slug" />
                <x-admin.field label="Cover image URL" name="cover_image" placeholder="https://.../cover.jpg" icon="image" wire:model="cover_image" />
                <div class="md:col-span-2">
                    <x-admin.field label="Excerpt" name="excerpt" type="textarea" rows="2" placeholder="Short summary shown on listings" hint="Displayed on the blog listing and search results" wire:model="excerpt" />
                </div>
                <div class="md:col-span-2">
                    <x-admin.field label="Body (HTML allowed)" name="body" type="textarea" rows="10" placeholder="Full article content..." required error="{{ $errors->first('body') }}" wire:model="body" />
                </div>
                <x-admin.field label="Category" name="category" type="select" icon="folder" hint="Used for the blog filter pills &amp; sidebar" wire:model="category">
                    <option value="">Uncategorized</option>
                    @foreach (\App\Models\BlogPost::CATEGORIES as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </x-admin.field>
                <x-admin.field label="Tags (comma separated)" name="tags" placeholder="laravel, livewire, tailwind" icon="tag" wire:model="tags" />
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 pb-2.5 md:justify-end">
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model="featured" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-white/10" /> Featured
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model="published" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-white/10" /> Published
                    </label>
                </div>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="post-form" save-label="{{ $editingId ? 'Update post' : 'Create post' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Tags</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->posts->take($this->limit) as $post)
                    <tr>
                        <td>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $post->title }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $post->excerpt }}</p>
                        </td>
                        <td>
                            <span class="badge {{ in_array($post->category, \App\Models\BlogPost::CATEGORIES, true) ? 'badge-brand' : 'badge-neutral' }}">{{ $post->categoryLabel() }}</span>
                        </td>
                        <td>
                            <button type="button" wire:click="togglePublished({{ $post->id }})" class="badge cursor-pointer {{ $post->published ? 'badge-success' : 'badge-warning' }}">
                                {{ $post->published ? 'Published' : 'Draft' }}
                            </button>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($post->tags as $tag)
                                    <span class="badge badge-neutral">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ $post->published_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="text-right">
                            <x-admin.actions :view="route('blog.show', $post->slug)" :edit="$post->id" :delete="$post->id" delete-message="Delete this post?" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">No posts found.</td>
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
