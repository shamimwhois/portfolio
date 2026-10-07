<?php

namespace Tests\Feature;

use App\Livewire\Admin\Faqs as AdminFaqs;
use App\Livewire\Admin\Services as AdminServices;
use App\Livewire\Admin\Skills as AdminSkills;
use App\Livewire\Admin\Timeline as AdminTimeline;
use App\Livewire\Feed\Index as FeedIndex;
use App\Livewire\Home\Index as HomeIndex;
use App\Models\Faq;
use App\Models\FeedPost;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\Skill;
use App\Models\TimelinePhase;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaginationMoreTest extends TestCase
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

    public function test_feed_caps_at_ten_and_loads_more(): void
    {
        FeedPost::query()->delete();
        FeedPost::factory()->count(14)->create(['user_id' => $this->admin()->id]);

        Livewire::actingAs($this->admin())
            ->test(FeedIndex::class)
            ->assertSet('posts', fn ($posts) => $posts->count() === 11) // 10 + sentinel
            ->assertSet('hasMore', true)
            ->assertSet('matchingTotal', 14)
            ->assertSee('Load more posts')
            ->assertSee('Showing 10 of 14')
            ->call('loadMore')
            ->assertSet('posts', fn ($posts) => $posts->count() === 14)
            ->assertSet('hasMore', false);
    }

    public function test_feed_renders_only_ten_posts_not_the_sentinel(): void
    {
        FeedPost::query()->delete();
        FeedPost::factory()->count(14)->create(['user_id' => $this->admin()->id]);

        $html = Livewire::actingAs($this->admin())
            ->test(FeedIndex::class)
            ->html();

        // The +1 sentinel item must never render — exactly 10 post articles.
        $this->assertSame(10, substr_count($html, 'class="card card-pad spotlight"'));
    }

    public function test_feed_exactly_ten_has_no_sentinel(): void
    {
        FeedPost::query()->delete();
        FeedPost::factory()->count(10)->create(['user_id' => $this->admin()->id]);

        Livewire::actingAs($this->admin())
            ->test(FeedIndex::class)
            ->assertSet('posts', fn ($posts) => $posts->count() === 10)
            ->assertSet('hasMore', false)
            ->assertDontSee('Load more posts');
    }

    public function test_admin_skills_cap_at_ten_and_load_more(): void
    {
        Skill::query()->delete();
        Skill::factory()->count(13)->create();

        Livewire::actingAs($this->admin())
            ->test(AdminSkills::class)
            ->assertSet('skills', fn ($skills) => $skills->count() === 11)
            ->assertSet('hasMore', true)
            ->assertSet('matchingTotal', 13)
            ->assertSee('Load more skills')
            ->call('loadMore')
            ->assertSet('skills', fn ($skills) => $skills->count() === 13);
    }

    public function test_admin_timeline_caps_at_ten_and_load_more(): void
    {
        TimelinePhase::query()->delete();
        TimelinePhase::factory()->count(12)->create();

        Livewire::actingAs($this->admin())
            ->test(AdminTimeline::class)
            ->assertSet('phases', fn ($phases) => $phases->count() === 11)
            ->assertSet('hasMore', true)
            ->assertSet('matchingTotal', 12)
            ->assertSee('Load more phases')
            ->call('loadMore')
            ->assertSet('phases', fn ($phases) => $phases->count() === 12);
    }

    public function test_admin_faqs_cap_at_ten_and_load_more(): void
    {
        Faq::query()->delete();
        Faq::factory()->count(12)->create();

        Livewire::actingAs($this->admin())
            ->test(AdminFaqs::class)
            ->assertSet('faqs', fn ($faqs) => $faqs->count() === 11)
            ->assertSet('hasMore', true)
            ->assertSet('matchingTotal', 12)
            ->assertSee('Load more FAQs')
            ->call('loadMore')
            ->assertSet('faqs', fn ($faqs) => $faqs->count() === 12);
    }

    public function test_admin_services_cap_categories_at_ten_and_load_more(): void
    {
        ServiceCategory::query()->delete();
        ServiceCategory::factory()->count(12)->create();

        Livewire::actingAs($this->admin())
            ->test(AdminServices::class)
            ->assertSet('categories', fn ($categories) => $categories->count() === 11)
            ->assertSet('allCategories', fn ($categories) => $categories->count() === 12)
            ->assertSet('hasMore', true)
            ->assertSet('matchingTotal', 12)
            ->assertSee('Load more categories')
            ->call('loadMore')
            ->assertSet('categories', fn ($categories) => $categories->count() === 12);
    }

    public function test_home_projects_grid_caps_at_six_and_loads_more(): void
    {
        Project::query()->delete();
        Project::factory()->count(8)->create(['featured' => true]);

        Livewire::actingAs($this->admin())
            ->test(HomeIndex::class)
            ->assertSet('projectLimit', 6)
            ->assertSee('Load more work')
            ->assertSee('Showing 6 of 8 projects')
            ->call('loadMoreProjects')
            ->assertSet('projectLimit', 12);
    }

    public function test_home_projects_exactly_six_hides_load_more(): void
    {
        Project::query()->delete();
        Project::factory()->count(6)->create(['featured' => true]);

        Livewire::actingAs($this->admin())
            ->test(HomeIndex::class)
            ->assertDontSee('Load more work');
    }
}
