<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\ClientProject;
use App\Models\ProjectFeature;
use App\Models\ProjectFile;
use App\Models\ProjectMedia;
use App\Models\ProjectTask;
use App\Models\ProjectUpdate;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ClientProjects extends Component
{
    use WithFileUploads;

    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public string $statusFilter = 'all';

    public string $sourceFilter = 'all';

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $detailId = null;

    public ?int $userId = null;

    public ?int $invoiceId = null;

    public string $title = '';

    public string $description = '';

    public string $status = ClientProject::STATUS_PENDING;

    public ?string $start_date = null;

    public ?string $deadline = null;

    public ?string $remind_at = null;

    public string $requirements = '';

    public string $agreement = '';

    public string $policy = '';

    public string $cancel_policy = '';

    public string $return_policy = '';

    // Task composer
    public string $taskTitle = '';

    // Feature composer
    public string $featureTitle = '';

    // Update log composer
    public string $updateMessage = '';

    public string $updateType = ProjectUpdate::TYPE_UPDATE;

    // File upload
    public $fileUpload = null;

    // Budget negotiation
    public ?string $offerPrice = null;

    public string $offerNote = '';

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(ClientProject $project): void
    {
        $this->resetForm();
        $this->editingId = $project->id;
        $this->showForm = true;
        $this->userId = $project->user_id;
        $this->invoiceId = $project->invoice_id;
        $this->title = $project->title;
        $this->description = $project->description ?? '';
        $this->status = $project->status;
        $this->start_date = $project->start_date?->format('Y-m-d');
        $this->deadline = $project->deadline?->format('Y-m-d');
        $this->remind_at = $project->remind_at?->format('Y-m-d\TH:i');
        $this->requirements = $project->requirements ?? '';
        $this->agreement = $project->agreement ?? '';
        $this->policy = $project->policy ?? '';
        $this->cancel_policy = $project->cancel_policy ?? '';
        $this->return_policy = $project->return_policy ?? '';
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'userId' => ['required', 'exists:users,id'],
            'invoiceId' => ['nullable', 'exists:invoices,id'],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:'.implode(',', array_keys(ClientProject::STATUSES))],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remind_at' => ['nullable', 'date'],
            'requirements' => ['nullable', 'string'],
            'agreement' => ['nullable', 'string'],
            'policy' => ['nullable', 'string'],
            'cancel_policy' => ['nullable', 'string'],
            'return_policy' => ['nullable', 'string'],
        ]);

        $data = [
            'user_id' => $validated['userId'],
            'invoice_id' => $validated['invoiceId'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'],
            'deadline' => $validated['deadline'],
            'remind_at' => $validated['remind_at'],
            'requirements' => $validated['requirements'],
            'agreement' => $validated['agreement'],
            'policy' => $validated['policy'],
            'cancel_policy' => $validated['cancel_policy'],
            'return_policy' => $validated['return_policy'],
        ];

        if ($this->editingId) {
            $project = ClientProject::query()->findOrFail($this->editingId);
            $project->update($data);
            ActivityLog::record('admin.client_project.updated', auth()->user(), ['project' => $project->title]);
            session()->flash('status', 'Client project updated.');
        } else {
            $project = ClientProject::query()->create($data);
            ActivityLog::record('admin.client_project.created', auth()->user(), ['project' => $project->title]);
            session()->flash('status', 'Client project created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
        $this->detailId = $project->id;
    }

    public function delete(ClientProject $project): void
    {
        ActivityLog::record('admin.client_project.deleted', auth()->user(), ['project' => $project->title]);
        $project->delete();
        session()->flash('status', 'Client project deleted.');
    }

    public function openDetail(ClientProject $project): void
    {
        $this->detailId = $project->id;
        $this->resetComposers();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        $this->resetComposers();
    }

    public function addTask(): void
    {
        if (! $this->detailId) {
            return;
        }

        $this->validate(['taskTitle' => ['required', 'string', 'max:190']]);

        $project = ClientProject::query()->findOrFail($this->detailId);

        ProjectTask::query()->create([
            'client_project_id' => $project->id,
            'title' => trim($this->taskTitle),
            'sort_order' => $project->tasks()->count(),
        ]);

        $this->taskTitle = '';
    }

    public function toggleTask(ProjectTask $task): void
    {
        $task->update(['is_complete' => ! $task->is_complete]);
    }

    public function deleteTask(ProjectTask $task): void
    {
        $task->delete();
    }

    public function addFeature(): void
    {
        if (! $this->detailId) {
            return;
        }

        $this->validate(['featureTitle' => ['required', 'string', 'max:190']]);

        $project = ClientProject::query()->findOrFail($this->detailId);

        ProjectFeature::query()->create([
            'client_project_id' => $project->id,
            'title' => trim($this->featureTitle),
            'sort_order' => $project->features()->count(),
        ]);

        $this->featureTitle = '';
    }

    public function toggleFeature(ProjectFeature $feature): void
    {
        $feature->update(['is_complete' => ! $feature->is_complete]);
    }

    public function deleteFeature(ProjectFeature $feature): void
    {
        $feature->delete();
    }

    public function postUpdate(): void
    {
        if (! $this->detailId) {
            return;
        }

        $this->validate([
            'updateMessage' => ['required', 'string', 'max:2000'],
            'updateType' => ['required', 'in:'.implode(',', [ProjectUpdate::TYPE_UPDATE, ProjectUpdate::TYPE_MILESTONE, ProjectUpdate::TYPE_NOTE, ProjectUpdate::TYPE_REMINDER])],
        ]);

        ProjectUpdate::query()->create([
            'client_project_id' => $this->detailId,
            'user_id' => auth()->id(),
            'type' => $this->updateType,
            'message' => trim($this->updateMessage),
        ]);

        $this->updateMessage = '';
    }

    public function deleteUpdate(ProjectUpdate $update): void
    {
        $update->delete();
    }

    public function uploadFile(): void
    {
        if (! $this->detailId) {
            return;
        }

        $this->validate([
            'fileUpload' => ['required', 'file', 'max:51200'], // 50MB
        ]);

        $file = $this->fileUpload;

        $path = $file->store('client-projects/'.$this->detailId, 'local');

        ProjectFile::query()->create([
            'client_project_id' => $this->detailId,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'kind' => ProjectFile::KIND_DELIVERABLE,
        ]);

        ActivityLog::record('admin.client_project.file_uploaded', auth()->user(), ['project' => $this->detailId]);

        $this->fileUpload = null;
        session()->flash('status', 'File uploaded — the customer can now download it.');
    }

    public function deleteFile(ProjectFile $file): void
    {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($file->path);
        $file->delete();
    }

    public function deleteMedia(ProjectMedia $media): void
    {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($media->path);
        $media->delete();
    }

    public function negotiateBudget(): void
    {
        if (! $this->detailId) {
            return;
        }

        $this->validate([
            'offerPrice' => ['required', 'numeric', 'min:0'],
            'offerNote' => ['nullable', 'string', 'max:1000'],
        ]);

        $project = ClientProject::query()->findOrFail($this->detailId);

        $project->update([
            'offer_price' => $this->offerPrice,
            'offer_note' => trim($this->offerNote) ?: null,
            'offer_status' => ClientProject::OFFER_PENDING,
        ]);

        ActivityLog::record('admin.client_project.offer_sent', auth()->user(), [
            'project' => $project->title,
            'amount' => '$'.number_format((float) $this->offerPrice),
        ]);

        $this->offerPrice = null;
        $this->offerNote = '';

        session()->flash('status', 'Budget offer sent — the customer can accept, decline or counter.');
    }

    public function clearOffer(): void
    {
        if (! $this->detailId) {
            return;
        }

        ClientProject::query()->findOrFail($this->detailId)->update([
            'offer_price' => null,
            'offer_note' => null,
            'offer_status' => null,
        ]);

        session()->flash('status', 'Offer cleared.');
    }

    #[Computed]
    public function customers()
    {
        return User::query()->orderBy('name')->get();
    }

    #[Computed]
    public function customerInvoices()
    {
        if (! $this->userId) {
            return collect();
        }

        return \App\Models\Invoice::query()
            ->where('user_id', $this->userId)
            ->with('tier.category', 'package')
            ->latest()
            ->get();
    }

    public function updatedStatusFilter(): void
    {
        $this->limit = 10;
    }

    public function updatedSourceFilter(): void
    {
        $this->limit = 10;
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function projects()
    {
        return ClientProject::query()
            ->with('user', 'invoice.tier.category', 'invoice.package')
            ->withCount(['tasks', 'features', 'completedTasks', 'completedFeatures'])
            ->when($this->sourceFilter !== 'all', fn ($q) => $q->where('source', $this->sourceFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->projects->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return ClientProject::query()
            ->when($this->sourceFilter !== 'all', fn ($q) => $q->where('source', $this->sourceFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->count();
    }

    #[Computed]
    public function detail()
    {
        return $this->detailId
            ? ClientProject::query()->with('user', 'invoice.tier.category', 'invoice.package', 'package', 'tier', 'tasks', 'features', 'updates.author', 'deliverables', 'attachments', 'media')->find($this->detailId)
            : null;
    }

    private function resetForm(): void
    {
        $this->reset('userId', 'invoiceId', 'title', 'description', 'status', 'start_date', 'deadline', 'remind_at', 'requirements', 'agreement', 'policy', 'cancel_policy', 'return_policy');
        $this->status = ClientProject::STATUS_PENDING;
    }

    private function resetComposers(): void
    {
        $this->reset('taskTitle', 'featureTitle', 'updateMessage', 'fileUpload', 'offerPrice', 'offerNote');
        $this->updateType = ProjectUpdate::TYPE_UPDATE;
    }

    public function render()
    {
        return view('livewire.admin.client-projects');
    }
}
