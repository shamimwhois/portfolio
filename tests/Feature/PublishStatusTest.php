<?php

namespace Tests\Feature;

use App\Livewire\Admin\Faqs;
use App\Livewire\Admin\Feed as AdminFeed;
use App\Livewire\Admin\Packages;
use App\Livewire\Admin\Projects;
use App\Livewire\Admin\Skills;
use App\Livewire\Home\Index as HomeIndex;
use App\Livewire\Servicelist;
use App\Models\Faq;
use App\Models\FeedPost;
use App\Models\Project;
use App\Models\Skill;
use App\Models\TechPackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublishStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_admin_can_cycle_project_publish_status(): void
    {
        $project = Project::query()->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(Projects::class)
            ->call('setStatus', $project->id, 'draft')
            ->assertHasNoErrors();

        $this->assertSame('draft', $project->refresh()->publish_status);
        $this->assertTrue($project->isDraft());

        Livewire::actingAs($this->admin)
            ->test(Projects::class)
            ->call('setStatus', $project->id, 'unpublished');

        $this->assertSame('unpublished', $project->refresh()->publish_status);
        $this->assertTrue($project->isUnpublished());
    }

    public function test_admin_can_set_status_on_skills_faqs_and_packages(): void
    {
        $skill = Skill::query()->firstOrFail();
        $faq = Faq::query()->firstOrFail();
        $package = TechPackage::query()->firstOrFail();

        Livewire::actingAs($this->admin)->test(Skills::class)->call('setStatus', $skill->id, 'draft');
        Livewire::actingAs($this->admin)->test(Faqs::class)->call('setStatus', $faq->id, 'draft');
        Livewire::actingAs($this->admin)->test(Packages::class)->call('setStatus', $package->id, 'draft');

        $this->assertTrue($skill->refresh()->isDraft());
        $this->assertTrue($faq->refresh()->isDraft());
        $this->assertTrue($package->refresh()->isDraft());
    }

    public function test_draft_items_are_hidden_from_home_page(): void
    {
        $skill = Skill::query()->firstOrFail();
        $project = Project::query()->firstOrFail();
        $faq = Faq::query()->firstOrFail();

        $skill->update(['publish_status' => 'draft']);
        $project->update(['publish_status' => 'draft']);
        $faq->update(['publish_status' => 'draft']);

        Livewire::test(HomeIndex::class)
            ->assertDontSee($skill->name)
            ->assertDontSee($project->title)
            ->assertDontSee($faq->question);
    }

    public function test_draft_packages_hidden_from_servicelist(): void
    {
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();
        $package->update(['publish_status' => 'draft']);

        Livewire::test(Servicelist::class)
            ->assertDontSee($package->title);
    }

    public function test_unpublished_packages_stay_listed_but_not_buyable(): void
    {
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();
        $package->update(['publish_status' => 'unpublished']);

        // The unpublished tool stays listed, marked Inactive / Stock Out, and
        // its Buy Now button is gone (other published tools may still show one).
        Livewire::test(Servicelist::class)
            ->assertSee($package->title)
            ->assertSee('Inactive')
            ->assertSee('Stock Out')
            ->assertDontSeeHtml('wire:click="purchase('.$package->id.')');
    }

    public function test_servicelist_shows_published_sellable_packages_with_buy_now(): void
    {
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Livewire::test(Servicelist::class)
            ->assertSee($package->title)
            ->assertSee('Active')
            ->assertSee('Buy Now');
    }

    public function test_servicelist_search_filters_packages(): void
    {
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();
        $other = TechPackage::query()->whereNotNull('price')->where('id', '!=', $package->id)->first();

        Livewire::test(Servicelist::class)
            ->set('search', $package->title)
            ->assertSee($package->title);

        if ($other) {
            Livewire::test(Servicelist::class)
                ->set('search', 'zzzz-nomatch-zzzz')
                ->assertDontSee($other->title)
                ->assertSee('No tools found');
        }
    }

    public function test_unpublished_items_hidden_from_home_page(): void
    {
        $skill = Skill::query()->firstOrFail();
        $skill->update(['publish_status' => 'unpublished']);

        Livewire::test(HomeIndex::class)
            ->assertDontSee($skill->name);
    }

    public function test_servicelist_guest_purchase_redirects_to_register(): void
    {
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Livewire::test(Servicelist::class)
            ->call('purchase', $package->id)
            ->assertRedirect(route('register', ['pkg' => $package->id]));
    }

    public function test_servicelist_purchase_creates_invoice_with_validity(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $package = TechPackage::query()->whereNotNull('price')->firstOrFail();

        Livewire::actingAs($customer)
            ->test(Servicelist::class)
            ->call('chooseValidity', $package->id, '14 Days')
            ->call('purchase', $package->id)
            ->set('termsAccepted', true)
            ->call('goToPayment')
            ->set('paymentMethod', 'nagad')
            ->call('confirmPurchase')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'user_id' => $customer->id,
            'tech_package_id' => $package->id,
            'status' => 'pending',
            'payment_method' => 'nagad',
        ]);

        $invoice = \App\Models\Invoice::query()->where('user_id', $customer->id)->where('tech_package_id', $package->id)->firstOrFail();
        $this->assertStringContainsString('Validity: 14 Days', $invoice->notes);
    }

    public function test_admin_feed_moderation_sets_status(): void
    {
        $post = FeedPost::query()->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(AdminFeed::class)
            ->call('setStatus', $post->id, 'draft');

        $this->assertSame('draft', $post->refresh()->publish_status);
    }

    public function test_draft_feed_posts_hidden_from_public_feed(): void
    {
        $post = FeedPost::query()->firstOrFail();
        $post->update(['publish_status' => 'draft']);

        Livewire::test(\App\Livewire\Feed\Index::class)
            ->assertDontSee($post->body);
    }
}
