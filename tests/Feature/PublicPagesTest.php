<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_renders_portfolio_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('MD SHAMIM HOSSAIN', false)
            ->assertSee('What I bring to the table')
            ->assertSee('Register')
            ->assertSee('Open site menu', false);
    }

    public function test_blog_index_lists_published_posts(): void
    {
        $this->get('/blog')
            ->assertOk()
            ->assertSee(BlogPost::query()->where('published', true)->first()->title);
    }

    public function test_blog_show_renders_published_post(): void
    {
        $post = BlogPost::query()->where('published', true)->first();

        $this->get("/blog/{$post->slug}")
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_blog_show_aborts_404_for_unpublished_post(): void
    {
        $post = BlogPost::query()->create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Hidden Draft',
            'slug' => 'hidden-draft',
            'body' => '<p>draft</p>',
            'published' => false,
            'published_at' => null,
        ]);

        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_contact_page_renders(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk();
    }
}
