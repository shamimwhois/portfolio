<?php

namespace Tests\Feature;

use App\Livewire\Admin\Blog;
use App\Livewire\Admin\Faqs;
use App\Livewire\Admin\Packages;
use App\Livewire\Admin\Projects;
use App\Livewire\Admin\Services;
use App\Livewire\Admin\Settings\Site as AdminSettingsSite;
use App\Livewire\Admin\Skills;
use App\Livewire\Admin\Timeline;
use App\Livewire\Admin\Users;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\ServiceTier;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\TechPackage;
use App\Models\TimelinePhase;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_admin_can_create_update_delete_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('create')
            ->set('name', 'Test Operator')
            ->set('email', 'operator@example.com')
            ->set('password', 'password')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'operator@example.com')->first();
        $this->assertNotNull($user);

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('edit', $user->id)
            ->set('name', 'Renamed Operator')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Renamed Operator']);

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('delete', $user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_view_user_details_modal(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'phone' => '+880 1700-123',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('view', $customer->id)
            ->assertSet('viewingId', $customer->id)
            ->assertSee('Account details')
            ->assertSee($customer->email)
            ->assertSee('+880 1700-123')
            ->assertSee('Customer')
            ->call('closeView')
            ->assertSet('viewingId', null);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('delete', $this->admin->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('toggleActive', $this->admin->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'is_active' => true]);
    }

    public function test_admin_cannot_deactivate_self_via_edit_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('edit', $this->admin->id)
            ->set('is_active', false)
            ->call('save')
            ->assertHasErrors('is_active');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'is_active' => true]);
    }

    public function test_admin_can_create_update_delete_project(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Projects::class)
            ->call('create')
            ->set('title', 'Orbital Dashboard')
            ->set('status', 'Live')
            ->call('save')
            ->assertHasNoErrors();

        $project = Project::query()->where('title', 'Orbital Dashboard')->first();
        $this->assertNotNull($project);

        Livewire::actingAs($this->admin)
            ->test(Projects::class)
            ->call('edit', $project->id)
            ->set('title', 'Orbital Dashboard v2')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'Orbital Dashboard v2']);

        Livewire::actingAs($this->admin)
            ->test(Projects::class)
            ->call('delete', $project->id);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_admin_can_create_update_delete_skill(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Skills::class)
            ->call('create')
            ->set('name', 'Blockchain')
            ->set('level', 70)
            ->call('save')
            ->assertHasNoErrors();

        $skill = Skill::query()->where('name', 'Blockchain')->first();
        $this->assertNotNull($skill);

        Livewire::actingAs($this->admin)
            ->test(Skills::class)
            ->call('edit', $skill->id)
            ->set('level', 85)
            ->call('save');

        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'level' => 85]);

        Livewire::actingAs($this->admin)
            ->test(Skills::class)
            ->call('delete', $skill->id);

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_admin_can_create_update_delete_faq(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Faqs::class)
            ->call('create')
            ->set('question', 'Is the code reusable?')
            ->set('answer', 'Yes, fully.')
            ->call('save')
            ->assertHasNoErrors();

        $faq = Faq::query()->where('question', 'Is the code reusable?')->first();
        $this->assertNotNull($faq);

        Livewire::actingAs($this->admin)
            ->test(Faqs::class)
            ->call('delete', $faq->id);

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_create_update_delete_timeline_phase(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Timeline::class)
            ->call('create')
            ->set('days', 'Days 08-10')
            ->set('title', 'Deploy')
            ->set('description', 'Ship to production.')
            ->call('save')
            ->assertHasNoErrors();

        $phase = TimelinePhase::query()->where('title', 'Deploy')->first();
        $this->assertNotNull($phase);

        Livewire::actingAs($this->admin)
            ->test(Timeline::class)
            ->call('delete', $phase->id);

        $this->assertDatabaseMissing('timeline_phases', ['id' => $phase->id]);
    }

    public function test_admin_can_create_update_delete_service_category_and_tier(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Services::class)
            ->call('createCategory')
            ->set('categoryName', 'Maintenance')
            ->set('categorySlug', 'maintenance')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $category = ServiceCategory::query()->where('slug', 'maintenance')->first();
        $this->assertNotNull($category);

        Livewire::actingAs($this->admin)
            ->test(Services::class)
            ->call('createTier', $category->id)
            ->set('tierName', 'Care Plan')
            ->set('tierPrice', '$99')
            ->call('saveTier')
            ->assertHasNoErrors();

        $tier = ServiceTier::query()->where('name', 'Care Plan')->first();
        $this->assertNotNull($tier);

        Livewire::actingAs($this->admin)
            ->test(Services::class)
            ->call('deleteTier', $tier->id)
            ->call('deleteCategory', $category->id);

        $this->assertDatabaseMissing('service_tiers', ['id' => $tier->id]);
        $this->assertDatabaseMissing('service_categories', ['id' => $category->id]);
    }

    public function test_services_cancel_closes_either_modal_without_error(): void
    {
        // Regression: the Services page previously had no generic `cancel()`
        // method, so closing a modal (X / Escape / footer Cancel) could throw
        // MethodNotFoundException for [cancel]. The unified method must resolve
        // regardless of which form is open.
        Livewire::actingAs($this->admin)
            ->test(Services::class)
            ->call('createCategory')
            ->call('cancel')
            ->assertHasNoErrors()
            ->assertSet('showCategoryForm', false)
            ->assertSet('showTierForm', false);

        $category = ServiceCategory::query()->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(Services::class)
            ->call('createTier', $category->id)
            ->call('cancel')
            ->assertHasNoErrors()
            ->assertSet('showCategoryForm', false)
            ->assertSet('showTierForm', false);

        // The more specific methods remain available (stale pages / future use).
        Livewire::actingAs($this->admin)
            ->test(Services::class)
            ->call('createCategory')
            ->call('cancelCategory')
            ->call('createTier', $category->id)
            ->call('cancelTier')
            ->assertHasNoErrors();
    }

    public function test_admin_can_create_update_delete_package(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Packages::class)
            ->call('create')
            ->set('category', 'php')
            ->set('title', 'Orbit Toolkit')
            ->set('command', 'composer require orbit/toolkit')
            ->call('save')
            ->assertHasNoErrors();

        $package = TechPackage::query()->where('title', 'Orbit Toolkit')->first();
        $this->assertNotNull($package);

        Livewire::actingAs($this->admin)
            ->test(Packages::class)
            ->call('edit', $package->id)
            ->set('title', 'Orbit Toolkit Pro')
            ->call('save');

        $this->assertDatabaseHas('tech_packages', ['id' => $package->id, 'title' => 'Orbit Toolkit Pro']);

        Livewire::actingAs($this->admin)
            ->test(Packages::class)
            ->call('delete', $package->id);

        $this->assertDatabaseMissing('tech_packages', ['id' => $package->id]);
    }

    public function test_admin_can_create_toggle_and_delete_blog_post(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Blog::class)
            ->call('create')
            ->set('title', 'HUD Design Guide')
            ->set('body', '<p>Guide content</p>')
            ->set('published', false)
            ->call('save')
            ->assertHasNoErrors();

        $post = BlogPost::query()->where('title', 'HUD Design Guide')->first();
        $this->assertNotNull($post);
        $this->assertFalse($post->published);

        Livewire::actingAs($this->admin)
            ->test(Blog::class)
            ->call('togglePublished', $post->id);

        $post->refresh();
        $this->assertTrue($post->published);

        Livewire::actingAs($this->admin)
            ->test(Blog::class)
            ->call('delete', $post->id);

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_admin_can_save_settings(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdminSettingsSite::class)
            ->set('site_name', 'Nova HUD')
            ->set('stats_json', '[{"value":"10","label":"Years"}]')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nova HUD', Setting::get('site_name'));
        $this->assertSame([['value' => '10', 'label' => 'Years']], Setting::get('stats'));
    }
}
