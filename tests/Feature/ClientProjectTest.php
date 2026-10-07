<?php

namespace Tests\Feature;

use App\Livewire\Admin\ClientProjects;
use App\Livewire\Admin\Invoices as AdminInvoices;
use App\Livewire\Panel\ProjectDetail;
use App\Livewire\Panel\Projects;
use App\Models\ClientProject;
use App\Models\Invoice;
use App\Models\ProjectFeature;
use App\Models\ProjectFile;
use App\Models\ProjectTask;
use App\Models\ProjectUpdate;
use App\Models\ServiceTier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ClientProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_create_client_project(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        Livewire::actingAs($admin)
            ->test(ClientProjects::class)
            ->call('create')
            ->set('userId', $customer->id)
            ->set('title', 'Dashboard Build')
            ->set('status', 'in_progress')
            ->set('start_date', now()->toDateString())
            ->set('deadline', now()->addDays(14)->toDateString())
            ->set('requirements', "1. Login\n2. Dashboard")
            ->set('agreement', 'Standard agreement.')
            ->set('cancel_policy', 'Cancellation terms.')
            ->set('return_policy', 'Return terms.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('client_projects', [
            'user_id' => $customer->id,
            'title' => 'Dashboard Build',
            'status' => 'in_progress',
        ]);
    }

    public function test_admin_list_has_view_action_that_opens_detail_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Viewable Project',
            'status' => ClientProject::STATUS_IN_PROGRESS,
        ]);

        Livewire::actingAs($admin)
            ->test(ClientProjects::class)
            // The row exposes a dedicated View action that opens the detail page.
            ->assertSee('View')
            ->assertSeeHtml('wire:click="openDetail('.$project->id.')"')
            ->call('openDetail', $project->id)
            ->assertSet('detailId', $project->id)
            ->assertSee('Viewable Project')
            ->assertSee('Customer')
            ->assertSee('Task List');
    }

    public function test_admin_can_manage_tasks_and_features(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Managed Project',
            'status' => ClientProject::STATUS_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(ClientProjects::class)
            ->call('openDetail', $project->id)
            ->set('taskTitle', 'Setup auth')
            ->call('addTask')
            ->assertHasNoErrors()
            ->set('featureTitle', 'Admin panel')
            ->call('addFeature')
            ->assertHasNoErrors();

        $task = ProjectTask::query()->where('client_project_id', $project->id)->where('title', 'Setup auth')->firstOrFail();
        $feature = ProjectFeature::query()->where('client_project_id', $project->id)->where('title', 'Admin panel')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ClientProjects::class)
            ->call('openDetail', $project->id)
            ->call('toggleTask', $task->id)
            ->call('toggleFeature', $feature->id);

        $this->assertTrue($task->fresh()->is_complete);
        $this->assertTrue($feature->fresh()->is_complete);
        $this->assertSame(100, $project->fresh()->progress());
    }

    public function test_admin_can_post_updates_and_upload_files(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'File Project',
            'status' => ClientProject::STATUS_IN_PROGRESS,
        ]);

        Livewire::actingAs($admin)
            ->test(ClientProjects::class)
            ->call('openDetail', $project->id)
            ->set('updateMessage', 'Milestone one done!')
            ->set('updateType', 'milestone')
            ->call('postUpdate')
            ->assertHasNoErrors()
            ->set('fileUpload', UploadedFile::fake()->create('handoff.zip', 120))
            ->call('uploadFile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('project_updates', [
            'client_project_id' => $project->id,
            'type' => 'milestone',
            'message' => 'Milestone one done!',
        ]);

        $file = ProjectFile::query()->where('client_project_id', $project->id)->firstOrFail();
        Storage::disk('local')->assertExists($file->path);
    }

    public function test_marking_invoice_paid_auto_creates_client_project(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $tier = ServiceTier::query()->firstOrFail();

        $invoice = Invoice::query()->create([
            'user_id' => $customer->id,
            'service_tier_id' => $tier->id,
            'invoice_no' => 'INV-AUTO-001',
            'amount' => 99.00,
            'status' => 'pending',
            'payment_method' => 'bkash',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminInvoices::class)
            ->call('markPaid', $invoice->id);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertDatabaseHas('client_projects', [
            'user_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'title' => $invoice->itemLabel(),
        ]);
    }

    public function test_customer_sees_own_projects_only(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        ClientProject::query()->create(['user_id' => $customer->id, 'title' => 'My Project', 'status' => 'in_progress']);
        ClientProject::query()->create(['user_id' => $other->id, 'title' => 'Secret Project', 'status' => 'in_progress']);

        Livewire::actingAs($customer)
            ->test(Projects::class)
            ->assertSee('My Project')
            ->assertDontSee('Secret Project');
    }

    public function test_customer_cannot_view_another_customers_project_detail(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create(['user_id' => $other->id, 'title' => 'Secret', 'status' => 'pending']);

        $this->withoutExceptionHandling();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        Livewire::actingAs($customer)
            ->test(ProjectDetail::class, ['project' => $project]);
    }

    public function test_project_detail_renders_checklists_updates_and_docs(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create([
            'user_id' => $customer->id,
            'title' => 'Rendered Project',
            'status' => 'in_progress',
            'deadline' => now()->addDays(7)->toDateString(),
            'requirements' => 'Requirement one.',
            'return_policy' => 'Refund within 14 days.',
        ]);

        ProjectTask::query()->create(['client_project_id' => $project->id, 'title' => 'Task A', 'is_complete' => true, 'sort_order' => 0]);
        ProjectFeature::query()->create(['client_project_id' => $project->id, 'title' => 'Feature B', 'is_complete' => false, 'sort_order' => 0]);
        ProjectUpdate::query()->create(['client_project_id' => $project->id, 'user_id' => $customer->id, 'type' => 'milestone', 'message' => 'Big milestone!']);

        Livewire::actingAs($customer)
            ->test(ProjectDetail::class, ['project' => $project])
            ->assertSee('Rendered Project')
            ->assertSee('Task A')
            ->assertSee('Feature B')
            ->assertSee('Big milestone!')
            ->assertSee('Project requirements')
            ->assertSee('Return & refund policy')
            ->assertSee('50%')
            ->assertSee('Overall progress');
    }

    public function test_guest_cannot_access_panel_projects(): void
    {
        $this->get(route('panel.projects'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_download_own_project_file(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create(['user_id' => $customer->id, 'title' => 'Download Project', 'status' => 'completed']);

        $path = 'client-projects/'.$project->id.'/handoff.zip';
        Storage::disk('local')->put($path, 'zip-content');
        $file = ProjectFile::query()->create([
            'client_project_id' => $project->id,
            'name' => 'handoff.zip',
            'path' => $path,
            'size' => 11,
            'mime' => 'application/zip',
        ]);

        $response = $this->actingAs($customer)->get(route('panel.projects.file', [$project, $file]));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=handoff.zip');
        $this->assertSame('zip-content', $response->streamedContent());
    }

    public function test_customer_cannot_download_another_customers_file(): void
    {
        Storage::fake('local');

        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $project = ClientProject::query()->create(['user_id' => $other->id, 'title' => 'Their Project', 'status' => 'completed']);

        $path = 'client-projects/'.$project->id.'/secret.zip';
        Storage::disk('local')->put($path, 'secret');
        $file = ProjectFile::query()->create([
            'client_project_id' => $project->id,
            'name' => 'secret.zip',
            'path' => $path,
            'size' => 6,
            'mime' => 'application/zip',
        ]);

        $this->actingAs($customer)
            ->get(route('panel.projects.file', [$project, $file]))
            ->assertForbidden();
    }
}
