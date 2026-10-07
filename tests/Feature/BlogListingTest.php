<?php

namespace Tests\Feature;

use App\Livewire\Admin\Blog as AdminBlog;
use App\Livewire\Blog\Index as BlogIndex;
use App\Livewire\Blog\Show as BlogShow;
use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_blog_index_renders_search_categories_and_sidebar_widgets(): void
    {
        Livewire::test(BlogIndex::class)
            ->assertSee('Search articles...')
            ->assertSee('All')
            ->assertSee('Tutorials')
            ->assertSee('Guides')
            ->assertSee('Popular posts')
            ->assertSee('Tags')
            ->assertSee('Categories')
            // The category pills must emit the real category name (not a numeric key).
            ->assertSeeHtml("wire:click=\"setCategory('all')\"")
            ->assertSeeHtml("wire:click=\"setCategory('Tutorials')\"")
            ->assertSeeHtml("wire:click=\"setCategory('Guides')\"");
    }

    public function test_blog_search_filters_posts(): void
    {
        Livewire::test(BlogIndex::class)
            ->set('search', 'HUD')
            ->assertSee('Designing HUD Interfaces for Real Conversion')
            ->assertDontSee('Service Packaging Strategy');
    }

    public function test_blog_category_filter_shows_only_that_category(): void
    {
        Livewire::test(BlogIndex::class)
            ->call('setCategory', 'Tutorials')
            ->assertSee('Designing HUD Interfaces for Real Conversion')
            ->assertSee('Tailwind at Scale')
            ->assertDontSee('Service Packaging Strategy')
            ->assertDontSee('Cyberpunk Typography');
    }

    public function test_blog_tag_filter_shows_only_matching_posts(): void
    {
        Livewire::test(BlogIndex::class)
            ->call('setTag', 'SEO')
            ->assertSee('SEO for Creative Portfolios')
            ->assertDontSee('Service Packaging Strategy');
    }

    public function test_blog_popular_posts_ordered_by_views(): void
    {
        $titles = collect(Livewire::test(BlogIndex::class)->instance()->popularPosts)->pluck('title')->all();

        $this->assertSame([
            'Designing HUD Interfaces for Real Conversion', // 3,200
            'Tailwind at Scale',                            // 2,100
            'Micro-interactions that Convert',              // 1,580
            'SEO for Creative Portfolios',                  // 1,250
        ], $titles);
    }

    public function test_blog_categories_counts_published_posts(): void
    {
        $counts = BlogPost::query()
            ->where('published', true)
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->all();

        $this->assertSame(2, $counts['Tutorials']);
        $this->assertSame(2, $counts['Guides']);
        $this->assertSame(1, $counts['Tips']);
        $this->assertSame(1, $counts['News']);
        $this->assertArrayNotHasKey('Updates', $counts); // unpublished post excluded
    }

    public function test_blog_show_increments_views(): void
    {
        $post = BlogPost::query()->where('published', true)->first();
        $before = $post->views;

        Livewire::test(BlogShow::class, ['post' => $post])->assertOk();

        $this->assertSame($before + 1, $post->refresh()->views);
    }

    public function test_admin_can_set_category_on_post(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Livewire::actingAs($admin)
            ->test(AdminBlog::class)
            ->call('create')
            ->set('title', 'Categorized Post')
            ->set('body', '<p>Body</p>')
            ->set('category', 'Tips')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Tips', BlogPost::query()->where('title', 'Categorized Post')->firstOrFail()->category);
    }
}
