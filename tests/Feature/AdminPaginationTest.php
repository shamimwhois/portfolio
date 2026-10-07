<?php

namespace Tests\Feature;

use App\Livewire\Admin\Blog as AdminBlog;
use App\Livewire\Admin\Feed as AdminFeed;
use App\Livewire\Admin\GroupPackages as AdminGroupPackages;
use App\Livewire\Admin\Messages as AdminMessages;
use App\Livewire\Admin\Packages as AdminPackages;
use App\Livewire\Admin\Users as AdminUsers;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\FeedPost;
use App\Models\GroupPackage;
use App\Models\TechPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_blog_posts_cap_at_ten_and_load_more(): void
    {
        BlogPost::query()->delete();
        BlogPost::factory()->count(25)->create(['user_id' => $this->admin()->id]);

        Livewire::actingAs($this->admin())
            ->test(AdminBlog::class)
            ->assertSet('posts', fn ($posts) => $posts->count() === 11) // 10 + sentinel
            ->assertSet('hasMore', true)
            ->assertSee('Load more posts')
            ->assertSee('Showing 10 of 25')
            ->call('loadMore')
            ->assertSet('posts', fn ($posts) => $posts->count() === 21)
            ->assertSet('matchingTotal', 25);
    }

    public function test_blog_exactly_ten_has_no_sentinel_or_load_more(): void
    {
        BlogPost::query()->delete();
        BlogPost::factory()->count(10)->create(['user_id' => $this->admin()->id]);

        Livewire::actingAs($this->admin())
            ->test(AdminBlog::class)
            ->assertSet('posts', fn ($posts) => $posts->count() === 10) // no sentinel at exact limit
            ->assertSet('hasMore', false);
    }

    public function test_blog_search_resets_limit(): void
    {
        BlogPost::query()->delete();
        BlogPost::factory()->count(20)->create(['user_id' => $this->admin()->id]);

        Livewire::actingAs($this->admin())
            ->test(AdminBlog::class)
            ->set('search', 'no-such-title')
            ->assertSet('limit', 10)
            ->assertSet('posts', fn ($posts) => $posts->count() === 0);
    }

    public function test_users_cap_at_ten_and_load_more(): void
    {
        User::query()->where('role', User::ROLE_CUSTOMER)->delete();
        User::factory()->count(22)->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($this->admin())
            ->test(AdminUsers::class)
            ->assertSet('users', fn ($users) => $users->count() === 11)
            ->assertSet('matchingTotal', fn ($total) => $total >= 22)
            ->call('loadMore')
            ->assertSet('users', fn ($users) => $users->count() === 21);
    }

    public function test_packages_cap_at_ten_and_load_more(): void
    {
        TechPackage::query()->delete();
        TechPackage::factory()->count(18)->create();

        Livewire::actingAs($this->admin())
            ->test(AdminPackages::class)
            ->assertSet('packages', fn ($packages) => $packages->count() === 11)
            ->assertSet('matchingTotal', 18)
            ->call('loadMore')
            ->assertSet('packages', fn ($packages) => $packages->count() === 18);
    }

    public function test_messages_cap_at_ten_and_load_more(): void
    {
        ContactMessage::query()->delete();
        ContactMessage::factory()->count(16)->create();

        Livewire::actingAs($this->admin())
            ->test(AdminMessages::class)
            ->assertSet('messages', fn ($messages) => $messages->count() === 11)
            ->assertSet('matchingTotal', 16)
            ->call('loadMore')
            ->assertSet('messages', fn ($messages) => $messages->count() === 16);
    }

    public function test_feed_posts_cap_at_ten_and_load_more(): void
    {
        FeedPost::query()->delete();
        FeedPost::factory()->count(14)->create(['user_id' => $this->admin()->id]);

        Livewire::actingAs($this->admin())
            ->test(AdminFeed::class)
            ->assertSet('posts', fn ($posts) => $posts->count() === 11)
            ->assertSet('matchingTotal', 14)
            ->call('loadMore')
            ->assertSet('posts', fn ($posts) => $posts->count() === 14);
    }

    public function test_group_packages_cap_at_ten_and_load_more(): void
    {
        GroupPackage::query()->delete();
        GroupPackage::factory()->count(13)->create(['kind' => GroupPackage::KIND_TOOL]);
        GroupPackage::factory()->count(13)->create(['kind' => GroupPackage::KIND_COMBO]);

        Livewire::actingAs($this->admin())
            ->test(AdminGroupPackages::class)
            ->assertSet('combos', fn ($combos) => $combos->count() === 11)
            ->assertSet('tools', fn ($tools) => $tools->count() === 11)
            ->assertSet('matchingTotal', 26)
            ->call('loadMore')
            ->assertSet('combos', fn ($combos) => $combos->count() === 13)
            ->assertSet('tools', fn ($tools) => $tools->count() === 13);
    }
}
