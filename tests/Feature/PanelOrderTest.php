<?php

namespace Tests\Feature;

use App\Livewire\Admin\ClientProjects as AdminClientProjects;
use App\Livewire\Panel\Order;
use App\Livewire\Panel\ProjectDetail;
use App\Livewire\Panel\Projects;
use App\Models\ClientProject;
use App\Models\ProjectFile;
use App\Models\ProjectMedia;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PanelOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_open_order_form(): void
    {
        $this->get(route('panel.orders.create'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_submit_order_creating_pending_client_project(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($customer)
            ->test(Order::class)
            ->set('title', 'Brand refresh')
            ->set('description', 'A full visual refresh')
            ->set('requirements', "1. Landing page\n2. Contact form")
            ->set('exampleUrl', 'https://stripe.com')
            ->set('colorPalette', ['#6366f1', '#0f172a'])
            ->set('budgetFrom', '500')
            ->set('budgetTo', '1200')
            ->set('timelinePreference', 'within_2_weeks')
            ->set('docs', [UploadedFile::fake()->create('brief.pdf', 1024)])
            ->set('images', [UploadedFile::fake()->image('mockup.png', 800, 600)])
            ->set('video', UploadedFile::fake()->create('demo.mp4', 2048))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $project = ClientProject::query()->where('title', 'Brand refresh')->firstOrFail();

        $this->assertSame(ClientProject::SOURCE_CLIENT, $project->source);
        $this->assertSame(ClientProject::STATUS_PENDING, $project->status);
        $this->assertNotNull($project->submitted_at);
        $this->assertSame('https://stripe.com', $project->example_url);
        $this->assertSame(['#6366f1', '#0f172a'], $project->color_palette);
        $this->assertSame('500.00', $project->budget_from);
        $this->assertSame('1200.00', $project->budget_to);

        $this->assertSame(1, $project->attachments()->count());
        $this->assertSame(ProjectFile::KIND_ATTACHMENT, $project->attachments()->first()->kind);

        $this->assertSame(1, $project->media()->where('type', ProjectMedia::TYPE_IMAGE)->count());
        $this->assertSame(1, $project->media()->where('type', ProjectMedia::TYPE_VIDEO)->count());

        Storage::disk('local')->assertExists($project->attachments()->first()->path);
        Storage::disk('local')->assertExists($project->media()->first()->path);
    }

    public function test_order_requires_title_and_requirements(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($customer)
            ->test(Order::class)
            ->call('submit')
            ->assertHasErrors(['title']);

        Livewire::actingAs($customer)
            ->test(Order::class)
            ->set('title', 'Missing requirements')
            ->call('submit')
            ->assertHasErrors(['requirements']);
    }

    public function test_wizard_navigates_and_renders_every_step(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($customer)
            ->test(Order::class)
            ->set('title', 'Wizard walkthrough')
            ->call('nextStep')
            ->assertSee('Project requirements')
            ->set('requirements', 'Some requirements.')
            ->call('nextStep')
            ->assertSee('Brand colors')
            ->assertSee('Design notes')
            ->call('nextStep')
            ->assertSee('A range helps us scope')
            ->call('nextStep')
            ->assertSee('Documents')
            ->assertSee('upload-zone')
            ->assertSee('Video')
            ->call('nextStep')
            ->assertSeeHtml('Review & submit')
            ->assertSee('Wizard walkthrough');
    }

    public function test_budget_to_must_be_gte_budget_from(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($customer)
            ->test(Order::class)
            ->set('title', 'Budget test')
            ->set('requirements', 'Something to build.')
            ->set('budgetFrom', '1000')
            ->set('budgetTo', '500')
            ->call('submit')
            ->assertHasErrors(['budgetTo']);
    }

    public function test_customer_can_edit_own_pending_order_and_offer_resets(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Old title',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
            'requirements' => 'Original requirements.',
            'offer_price' => 900,
            'offer_status' => ClientProject::OFFER_PENDING,
        ]);

        Livewire::actingAs($customer)
            ->test(Order::class, ['project' => $project])
            ->assertSet('title', 'Old title')
            ->set('title', 'New title')
            ->set('requirements', 'Updated requirements.')
            ->call('submit')
            ->assertHasNoErrors();

        $project->refresh();

        $this->assertSame('New title', $project->title);
        $this->assertSame('Updated requirements.', $project->requirements);
        $this->assertNull($project->offer_price);
        $this->assertNull($project->offer_status);
    }

    public function test_customer_cannot_edit_another_customers_order(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $other->id,
            'title' => 'Their order',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
        ]);

        $this->withoutExceptionHandling();

        $this->expectException(HttpException::class);

        Livewire::actingAs($customer)->test(Order::class, ['project' => $project]);
    }

    public function test_customer_cannot_edit_order_once_accepted(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Accepted order',
            'status' => ClientProject::STATUS_IN_PROGRESS,
            'source' => ClientProject::SOURCE_CLIENT,
            'requirements' => 'Locked in.',
        ]);

        $this->withoutExceptionHandling();

        $this->expectException(HttpException::class);

        Livewire::actingAs($customer)->test(Order::class, ['project' => $project]);
    }

    public function test_customer_can_cancel_own_pending_order(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Cancellable order',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
        ]);

        Livewire::actingAs($customer)
            ->test(ProjectDetail::class, ['project' => $project])
            ->call('cancelOrder')
            ->assertRedirect(route('panel.projects'));

        $this->assertSame(ClientProject::STATUS_CANCELLED, $project->fresh()->status);
    }

    public function test_admin_can_send_budget_offer(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Offerable order',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
            'budget_from' => 500,
            'budget_to' => 1200,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminClientProjects::class)
            ->call('openDetail', $project->id)
            ->set('offerPrice', '950')
            ->set('offerNote', 'Two revision rounds included.')
            ->call('negotiateBudget')
            ->assertHasNoErrors();

        $project->refresh();

        $this->assertSame('950.00', $project->offer_price);
        $this->assertSame('Two revision rounds included.', $project->offer_note);
        $this->assertSame(ClientProject::OFFER_PENDING, $project->offer_status);
    }

    public function test_customer_can_accept_offer(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Acceptable order',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
            'offer_price' => 950,
            'offer_status' => ClientProject::OFFER_PENDING,
        ]);

        Livewire::actingAs($customer)
            ->test(ProjectDetail::class, ['project' => $project])
            ->call('acceptOffer')
            ->assertRedirect(route('panel.projects.show', $project));

        $this->assertSame(ClientProject::OFFER_ACCEPTED, $project->fresh()->offer_status);
    }

    public function test_customer_can_decline_offer(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Declinable order',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
            'offer_price' => 950,
            'offer_status' => ClientProject::OFFER_PENDING,
        ]);

        Livewire::actingAs($customer)
            ->test(ProjectDetail::class, ['project' => $project])
            ->call('declineOffer')
            ->assertRedirect(route('panel.projects.show', $project));

        $this->assertSame(ClientProject::OFFER_DECLINED, $project->fresh()->offer_status);
    }

    public function test_media_route_serves_owner_and_admin_but_forbids_others(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Media project',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
        ]);

        $path = 'client-projects/'.$project->id.'/media/reference.png';
        Storage::disk('local')->put($path, 'png-bytes');

        $media = ProjectMedia::query()->create([
            'client_project_id' => $project->id,
            'type' => ProjectMedia::TYPE_IMAGE,
            'path' => $path,
            'position' => 0,
        ]);

        $this->actingAs($customer)
            ->get(route('panel.projects.media', [$project, $media]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('panel.projects.media', [$project, $media]))
            ->assertOk();

        $this->actingAs($other)
            ->get(route('panel.projects.media', [$project, $media]))
            ->assertForbidden();
    }

    public function test_deleting_project_removes_uploaded_files_from_disk(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Cleanup project',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
        ]);

        $mediaPath = 'client-projects/'.$project->id.'/media/shot.png';
        $filePath = 'client-projects/'.$project->id.'/attachments/brief.pdf';
        Storage::disk('local')->put($mediaPath, 'img');
        Storage::disk('local')->put($filePath, 'pdf');

        ProjectMedia::query()->create(['client_project_id' => $project->id, 'type' => ProjectMedia::TYPE_IMAGE, 'path' => $mediaPath, 'position' => 0]);
        ProjectFile::query()->create([
            'client_project_id' => $project->id,
            'name' => 'brief.pdf',
            'path' => $filePath,
            'size' => 3,
            'mime' => 'application/pdf',
            'kind' => ProjectFile::KIND_ATTACHMENT,
        ]);

        Livewire::actingAs($admin)
            ->test(AdminClientProjects::class)
            ->call('delete', $project->id);

        Storage::disk('local')->assertMissing($mediaPath);
        Storage::disk('local')->assertMissing($filePath);
        $this->assertDatabaseMissing('client_projects', ['id' => $project->id]);
    }

    public function test_projects_page_shows_start_cta_and_client_brief_badge(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Brief order',
            'status' => ClientProject::STATUS_PENDING,
            'source' => ClientProject::SOURCE_CLIENT,
            'offer_price' => 750,
            'offer_status' => ClientProject::OFFER_PENDING,
        ]);

        Livewire::actingAs($customer)
            ->test(Projects::class)
            ->assertSee('Start a Project')
            ->assertSee('Brief order')
            ->assertSee('Client brief')
            ->assertSee('Offer: $750');
    }
}
