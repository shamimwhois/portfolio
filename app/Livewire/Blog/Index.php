<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.hud')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $tag = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->category = $category === 'all' ? '' : $category;
    }

    public function setTag(?string $tag): void
    {
        $this->tag = $tag ?? '';
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->tag = '';
    }

    /**
     * Tag cloud: every published post's tags with post counts.
     */
    #[Computed]
    public function allTags()
    {
        return BlogPost::query()
            ->where('published', true)
            ->get()
            ->flatMap(fn (BlogPost $post) => collect($post->tags ?? [])->map(fn ($tag) => ['name' => $tag, 'post' => $post->id]))
            ->groupBy('name')
            ->map(fn ($group) => ['name' => (string) $group->first()['name'], 'count' => $group->count()])
            ->sortByDesc('count')
            ->values()
            ->take(20);
    }

    /**
     * Categories with published post counts, for the filter pills + sidebar.
     */
    #[Computed]
    public function categories()
    {
        return BlogPost::query()
            ->where('published', true)
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->all();
    }

    /**
     * Sidebar widget: most-viewed published posts.
     */
    #[Computed]
    public function popularPosts()
    {
        return BlogPost::query()
            ->where('published', true)
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();
    }

    public function render()
    {
        $posts = BlogPost::query()
            ->with('author')
            ->where('published', true)
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('excerpt', 'like', '%'.$this->search.'%')
                ->orWhere('body', 'like', '%'.$this->search.'%')))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->tag, fn ($q) => $q->whereJsonContains('tags', $this->tag))
            ->orderByDesc('published_at')
            ->paginate(7);

        return view('livewire.blog.index', ['posts' => $posts]);
    }
}
