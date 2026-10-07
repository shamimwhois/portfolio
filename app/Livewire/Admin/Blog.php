<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\BlogPost;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Blog extends Component
{
    public string $search = '';

    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $body = '';

    public string $category = '';

    public string $tags = '';

    public string $cover_image = '';

    public bool $featured = false;

    public bool $published = true;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(BlogPost $post): void
    {
        $this->resetForm();
        $this->editingId = $post->id;
        $this->showForm = true;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->excerpt = $post->excerpt ?? '';
        $this->body = $post->body;
        $this->category = $post->category ?? '';
        $this->tags = implode(', ', $post->tags ?? []);
        $this->cover_image = $post->cover_image ?? '';
        $this->featured = (bool) $post->featured;
        $this->published = (bool) $post->published;
    }

    public function updatedTitle(): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'alpha_dash', 'max:190', Rule::unique('blog_posts', 'slug')->ignore($this->editingId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'category' => ['nullable', Rule::in(BlogPost::CATEGORIES)],
            'tags' => ['nullable', 'string', 'max:500'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'featured' => ['boolean'],
            'published' => ['boolean'],
        ]);

        $data = [
            ...$validated,
            'tags' => collect(explode(',', $validated['tags']))->map(fn ($t) => trim($t))->filter()->values()->all(),
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
            'published_at' => $validated['published'] ? now() : null,
        ];

        if ($this->editingId) {
            $post = BlogPost::query()->findOrFail($this->editingId);
            $post->update($data);
            ActivityLog::record('admin.blog.updated', auth()->user(), ['post' => $post->title]);
            session()->flash('status', 'Post updated.');
        } else {
            $post = BlogPost::query()->create([...$data, 'user_id' => auth()->id()]);
            ActivityLog::record('admin.blog.created', auth()->user(), ['post' => $post->title]);
            session()->flash('status', 'Post created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(BlogPost $post): void
    {
        ActivityLog::record('admin.blog.deleted', auth()->user(), ['post' => $post->title]);
        $post->delete();
        session()->flash('status', 'Post deleted.');
    }

    public function togglePublished(BlogPost $post): void
    {
        $post->update([
            'published' => ! $post->published,
            'published_at' => ! $post->published ? now() : null,
        ]);
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function updatedSearch(): void
    {
        $this->limit = 10;
    }

    #[Computed]
    public function posts()
    {
        return BlogPost::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
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
        return BlogPost::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->count();
    }

    private function resetForm(): void
    {
        $this->reset('title', 'slug', 'excerpt', 'body', 'category', 'tags', 'cover_image', 'featured', 'published');
        $this->published = true;
    }

    public function render()
    {
        return view('livewire.admin.blog');
    }
}
