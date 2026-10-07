<?php

namespace Tests\Feature;

use App\Livewire\Feed\Index as FeedIndex;
use App\Models\FeedComment;
use App\Models\FeedLike;
use App\Models\FeedMedia;
use App\Models\FeedPost;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\UploadedFile;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Livewire;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_feed_page_renders_posts_publicly(): void
    {
        $this->get(route('feed'))
            ->assertOk()
            ->assertSee('Community feed')
            ->assertSee('Share. Connect. Discuss.');
    }

    public function test_guest_sees_login_prompt_and_cannot_post(): void
    {
        $this->get(route('feed'))
            ->assertSee('log in')
            ->assertSee('Register');
    }

    public function test_guest_can_open_comment_thread_to_read(): void
    {
        $post = FeedPost::query()->firstOrFail();

        // View-only: opening a thread must NOT redirect a guest to login.
        Livewire::test(FeedIndex::class)
            ->call('openComment', $post->id)
            ->assertSet('activeCommentPostId', $post->id);
    }

    public function test_guest_is_redirected_to_login_when_posting(): void
    {
        Livewire::test(FeedIndex::class)
            ->set('body', 'Guest post attempt')
            ->call('createPost')
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('feed_posts', ['body' => 'Guest post attempt']);
    }

    public function test_guest_is_redirected_to_login_when_reacting(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $owner->id, 'body' => 'Fresh post']);

        Livewire::test(FeedIndex::class)
            ->call('reactToPost', $post->id, 'like')
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('feed_likes', [
            'likeable_type' => FeedPost::class,
            'likeable_id' => $post->id,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_commenting(): void
    {
        $post = FeedPost::query()->firstOrFail();

        Livewire::test(FeedIndex::class)
            ->call('openComment', $post->id)
            ->set('commentBody', 'Guest comment attempt')
            ->call('postComment')
            ->assertRedirect(route('login'));

        $this->assertDatabaseMissing('feed_comments', ['body' => 'Guest comment attempt']);
    }

    public function test_guest_is_redirected_to_login_when_replying(): void
    {
        $comment = FeedComment::query()->firstOrFail();

        Livewire::test(FeedIndex::class)
            ->call('openReply', $comment->id)
            ->assertRedirect(route('login'));
    }

    public function test_guest_feed_links_carry_redirect_back(): void
    {
        // The "Log in" CTAs on the guest feed keep the current page as the
        // post-login destination so users return to the feed.
        $this->get(route('feed'))
            ->assertOk()
            ->assertSee('redirect=');
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->set('body', 'Hello world from the feed!')
            ->call('createPost')
            ->assertHasNoErrors()
            ->assertSet('body', '');

        $this->assertDatabaseHas('feed_posts', [
            'user_id' => $user->id,
            'body' => 'Hello world from the feed!',
        ]);
    }

    public function test_user_can_react_and_unreact_to_post(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('reactToPost', $post->id, 'love');

        $this->assertDatabaseHas('feed_likes', [
            'user_id' => $user->id,
            'likeable_type' => FeedPost::class,
            'likeable_id' => $post->id,
            'reaction_type' => 'love',
        ]);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('reactToPost', $post->id, 'love');

        $this->assertDatabaseMissing('feed_likes', [
            'user_id' => $user->id,
            'likeable_type' => FeedPost::class,
            'likeable_id' => $post->id,
        ]);
    }

    public function test_reacting_switches_reaction_type(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('reactToPost', $post->id, 'like');

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('reactToPost', $post->id, 'angry');

        $this->assertDatabaseHas('feed_likes', [
            'user_id' => $user->id,
            'likeable_type' => FeedPost::class,
            'likeable_id' => $post->id,
            'reaction_type' => 'angry',
        ]);

        $this->assertDatabaseMissing('feed_likes', [
            'user_id' => $user->id,
            'likeable_type' => FeedPost::class,
            'likeable_id' => $post->id,
            'reaction_type' => 'like',
        ]);
    }

    public function test_user_can_comment_and_reply(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('openComment', $post->id)
            ->set('commentBody', 'Nice post!')
            ->call('postComment')
            ->assertHasNoErrors();

        $comment = FeedComment::query()->where('feed_post_id', $post->id)
            ->where('body', 'Nice post!')
            ->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('openReply', $comment->id)
            ->set('replyBody', 'Thanks for sharing!')
            ->call('postReply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('feed_comments', [
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'body' => 'Thanks for sharing!',
        ]);
    }

    public function test_user_can_react_to_comment(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $comment = FeedComment::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('reactToComment', $comment->id, 'haha');

        $this->assertDatabaseHas('feed_likes', [
            'user_id' => $user->id,
            'likeable_type' => FeedComment::class,
            'likeable_id' => $comment->id,
            'reaction_type' => 'haha',
        ]);
    }

    public function test_author_can_delete_own_post(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'My own post']);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('deletePost', $post->id);

        $this->assertDatabaseMissing('feed_posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_others_post(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('deletePost', $post->id)
            ->assertForbidden();

        $this->assertDatabaseHas('feed_posts', ['id' => $post->id]);
    }

    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'Someone elses post']);

        Livewire::actingAs($admin)
            ->test(FeedIndex::class)
            ->call('deletePost', $post->id);

        $this->assertDatabaseMissing('feed_posts', ['id' => $post->id]);
    }

    public function test_deleting_post_removes_comments_and_reactions(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'Cascading delete check']);
        $comment = FeedComment::query()->create(['user_id' => $user->id, 'feed_post_id' => $post->id, 'body' => 'A comment']);
        $post->reactions()->create(['user_id' => $user->id, 'reaction_type' => 'like']);
        $comment->reactions()->create(['user_id' => $user->id, 'reaction_type' => 'wow']);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('deletePost', $post->id);

        $this->assertDatabaseMissing('feed_posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('feed_comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('feed_likes', [
            'likeable_type' => FeedPost::class,
            'likeable_id' => $post->id,
        ]);
    }

    public function test_user_can_create_post_with_image(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->set('images', [UploadedFile::fake()->image('photo.jpg', 800, 600)])
            ->set('body', 'Look at this!')
            ->call('createPost')
            ->assertHasNoErrors()
            ->assertSet('body', '');

        $post = FeedPost::query()->where('body', 'Look at this!')->firstOrFail();
        $media = $post->media()->firstOrFail();

        $this->assertSame(FeedMedia::TYPE_IMAGE, $media->type);
        Storage::disk('local')->assertExists($media->path);
    }

    public function test_user_can_create_post_with_video(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Real MP4 container magic + explicitly sniffed mime, mirroring what a
        // browser upload carries. Without the explicit mimeType() the testing
        // fake derives the mime from the extension and cannot exercise the
        // content-sniffing (mimetypes) rule.
        $mp4 = "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom".str_repeat("\x00", 1024);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->set('video', UploadedFile::fake()->createWithContent('clip.mp4', $mp4)->mimeType('video/mp4'))
            ->call('createPost')
            ->assertHasNoErrors();

        $post = FeedPost::query()->where('body', '')->first();
        $this->assertNotNull($post);

        $media = $post->media()->firstOrFail();
        $this->assertSame(FeedMedia::TYPE_VIDEO, $media->type);
        Storage::disk('local')->assertExists($media->path);
    }

    public function test_user_can_create_post_with_document(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $pdf = "%PDF-1.4\n".str_repeat("\x00", 256);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->set('documents', [UploadedFile::fake()->createWithContent('brief.pdf', $pdf)])
            ->set('body', 'Here is the brief')
            ->call('createPost')
            ->assertHasNoErrors()
            ->assertSet('documents', []);

        $post = FeedPost::query()->where('body', 'Here is the brief')->firstOrFail();
        $media = $post->media()->firstOrFail();

        $this->assertSame(FeedMedia::TYPE_DOCUMENT, $media->type);
        $this->assertSame('brief.pdf', $media->filename);
        Storage::disk('local')->assertExists($media->path);
    }

    public function test_video_and_document_uploads_reject_disguised_html(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $payload = '<html><script>alert(document.cookie)</script></html>';
        $postsBefore = FeedPost::query()->count();

        // The extension claims mp4/pdf but the sniffed mime is text/html — both
        // the mimes and mimetypes rules must reject the file.
        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->set('video', UploadedFile::fake()->createWithContent('evil.mp4', $payload)->mimeType('text/html'))
            ->call('createPost')
            ->assertHasErrors(['video']);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->set('documents', [UploadedFile::fake()->createWithContent('evil.pdf', $payload)->mimeType('text/html')])
            ->call('createPost')
            ->assertHasErrors(['documents.0']);

        // Neither disguised file may produce a post (the seeder's own media
        // rows are untouched).
        $this->assertSame($postsBefore, FeedPost::query()->count());
    }

    public function test_post_requires_body_or_media(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('createPost')
            ->assertHasErrors(['body']);
    }

    public function test_media_route_serves_uploaded_file(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'With media']);
        $path = 'feed/test/hero.png';
        Storage::disk('local')->put($path, 'png-bytes');

        $media = FeedMedia::query()->create([
            'feed_post_id' => $post->id,
            'type' => FeedMedia::TYPE_IMAGE,
            'path' => $path,
        ]);

        // Session-dependent middleware is skipped: serving a file needs no
        // session, and the trio trips the known Git-Bash cookie-path env bug.
        $this->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
            ->get(route('feed.media', $media))
            ->assertOk();

        $this->assertSame('png-bytes', Storage::disk('local')->get($path));
        Storage::disk('local')->delete($path);
    }

    public function test_media_route_sends_security_headers(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'With media']);
        $path = 'feed/test/asset.png';
        Storage::disk('local')->put($path, 'png-bytes');

        $media = FeedMedia::query()->create([
            'feed_post_id' => $post->id,
            'type' => FeedMedia::TYPE_IMAGE,
            'path' => $path,
        ]);

        $response = $this->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
            ->get(route('feed.media', $media));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        // Symfony normalizes cache directives (public / immutable / max-age),
        // so assert the parts rather than the order.
        $cacheControl = $response->headers->get('Cache-Control') ?? '';
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);

        Storage::disk('local')->delete($path);
    }

    public function test_document_media_is_served_with_sandbox_csp(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'Doc post']);
        $path = 'feed/test/doc.pdf';
        Storage::disk('local')->put($path, "%PDF-1.4\n");

        $media = FeedMedia::query()->create([
            'feed_post_id' => $post->id,
            'type' => FeedMedia::TYPE_DOCUMENT,
            'path' => $path,
            'filename' => '季度报告.pdf',
        ]);

        $response = $this->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
            ->get(route('feed.media', $media));

        $response->assertOk();
        $this->assertStringContainsString('sandbox', $response->headers->get('Content-Security-Policy') ?? '');
        // Symfony emits filename*=utf-8''… (lowercase) with the original UTF-8 name.
        $this->assertStringContainsString('filename*=utf-8', strtolower($response->headers->get('Content-Disposition') ?? ''));

        Storage::disk('local')->delete($path);
    }

    public function test_media_from_unpublished_post_returns_404(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create([
            'user_id' => $user->id,
            'body' => 'Draft post',
            'publish_status' => FeedPost::STATUS_DRAFT,
        ]);
        $path = 'feed/test/draft.png';
        Storage::disk('local')->put($path, 'png-bytes');

        $media = FeedMedia::query()->create([
            'feed_post_id' => $post->id,
            'type' => FeedMedia::TYPE_IMAGE,
            'path' => $path,
        ]);

        $this->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
            ->get(route('feed.media', $media))
            ->assertNotFound();

        Storage::disk('local')->delete($path);
    }

    public function test_user_can_edit_own_post(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'Original body']);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('startEditPost', $post->id)
            ->assertSet('editingPostId', $post->id)
            ->set('editBody', 'Updated body')
            ->call('saveEditPost')
            ->assertHasNoErrors()
            ->assertSet('editingPostId', null);

        $this->assertDatabaseHas('feed_posts', ['id' => $post->id, 'body' => 'Updated body']);
    }

    public function test_user_cannot_edit_others_post(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->firstOrFail();

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('startEditPost', $post->id)
            ->assertForbidden();
    }

    public function test_deleting_post_removes_media_files(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $post = FeedPost::query()->create(['user_id' => $user->id, 'body' => 'With a file']);
        $path = 'feed/'.$post->id.'/asset.png';
        Storage::disk('local')->put($path, 'bytes');

        FeedMedia::query()->create([
            'feed_post_id' => $post->id,
            'type' => FeedMedia::TYPE_IMAGE,
            'path' => $path,
        ]);

        Livewire::actingAs($user)
            ->test(FeedIndex::class)
            ->call('deletePost', $post->id);

        Storage::disk('local')->assertMissing($path);
    }
}
