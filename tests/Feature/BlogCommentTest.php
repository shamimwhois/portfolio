<?php

namespace Tests\Feature;

use App\Livewire\Blog\Show;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlogCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function publishedPost(): BlogPost
    {
        return BlogPost::query()->where('published', true)->firstOrFail();
    }

    public function test_guest_can_read_comments_on_blog_post(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = $this->publishedPost();

        BlogComment::query()->create([
            'user_id' => $author->id,
            'blog_post_id' => $post->id,
            'body' => 'Great article, thanks for sharing!',
        ]);

        // Rendered via Livewire (avoids the session-cookie middleware path).
        Livewire::test(Show::class, ['post' => $post])
            ->assertSee('Comments')
            ->assertSee('Great article, thanks for sharing!')
            ->assertSee('log in', false)
            // Guest sees read-only UI — no comment form.
            ->assertDontSee('Post comment');
    }

    public function test_guest_cannot_post_a_comment(): void
    {
        $post = $this->publishedPost();
        $before = BlogComment::query()->count();

        Livewire::test(Show::class, ['post' => $post])
            ->set('commentBody', 'Guest comment attempt')
            ->call('postComment')
            ->assertRedirect(route('login'));

        $this->assertSame($before, BlogComment::query()->count());
    }

    public function test_customer_can_post_comment_and_reply(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = $this->publishedPost();

        Livewire::actingAs($customer)
            ->test(Show::class, ['post' => $post])
            ->set('commentBody', 'This is a great read!')
            ->call('postComment')
            ->assertHasNoErrors()
            ->assertSet('commentBody', '')
            ->assertSee('This is a great read!');

        $comment = BlogComment::query()->where('blog_post_id', $post->id)->where('body', 'This is a great read!')->firstOrFail();

        Livewire::actingAs($other)
            ->test(Show::class, ['post' => $post])
            ->call('openReply', $comment->id)
            ->set('replyBody', 'Fully agree with you!')
            ->call('postReply')
            ->assertHasNoErrors()
            ->assertSee('Fully agree with you!');

        $this->assertSame(2, BlogComment::query()->count());
        $this->assertSame($comment->id, BlogComment::query()->where('body', 'Fully agree with you!')->firstOrFail()->parent_id);
    }

    public function test_comment_owner_and_admin_can_delete(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = $this->publishedPost();

        $comment = BlogComment::query()->create([
            'user_id' => $owner->id,
            'blog_post_id' => $post->id,
            'body' => 'Delete me',
        ]);

        // A different customer cannot delete it.
        Livewire::actingAs($other)
            ->test(Show::class, ['post' => $post])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseHas('blog_comments', ['id' => $comment->id]);

        // The owner can.
        Livewire::actingAs($owner)
            ->test(Show::class, ['post' => $post])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('blog_comments', ['id' => $comment->id]);

        // Admin can delete another customer's comment.
        $second = BlogComment::query()->create([
            'user_id' => $other->id,
            'blog_post_id' => $post->id,
            'body' => 'Delete me too',
        ]);

        Livewire::actingAs($admin)
            ->test(Show::class, ['post' => $post])
            ->call('deleteComment', $second->id);

        $this->assertDatabaseMissing('blog_comments', ['id' => $second->id]);
    }

    public function test_deleting_comment_removes_replies(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $replier = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = $this->publishedPost();

        $comment = BlogComment::query()->create([
            'user_id' => $owner->id,
            'blog_post_id' => $post->id,
            'body' => 'Parent comment',
        ]);

        $reply = BlogComment::query()->create([
            'user_id' => $replier->id,
            'blog_post_id' => $post->id,
            'parent_id' => $comment->id,
            'body' => 'Nested reply',
        ]);

        Livewire::actingAs($owner)
            ->test(Show::class, ['post' => $post])
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('blog_comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('blog_comments', ['id' => $reply->id]);
    }

    public function test_blog_show_renders_nested_replies(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $replier = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = $this->publishedPost();

        $comment = BlogComment::query()->create([
            'user_id' => $author->id,
            'blog_post_id' => $post->id,
            'body' => 'Top-level comment',
        ]);

        BlogComment::query()->create([
            'user_id' => $replier->id,
            'blog_post_id' => $post->id,
            'parent_id' => $comment->id,
            'body' => 'A nested reply',
        ]);

        Livewire::test(Show::class, ['post' => $post])
            ->assertSee('Top-level comment')
            ->assertSee('A nested reply');
    }

    public function test_feed_appears_in_desktop_nav(): void
    {
        // Feed must be present in the desktop nav (route + desktop flag).
        $feed = collect(config('nav.primary'))->firstWhere('route', 'feed');

        $this->assertNotNull($feed);
        $this->assertTrue($feed['desktop'] ?? false);
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('feed'));

        // The desktop nav (hud/nav) filters primary items by desktop=true,
        // so the rendered link must appear. Render the nav component directly
        // to avoid the session-cookie middleware path.
        $html = \Illuminate\Support\Facades\Blade::render(
            '<x-hud.nav />',
            [],
            true
        );

        $this->assertStringContainsString(route('feed'), $html);
    }
}
