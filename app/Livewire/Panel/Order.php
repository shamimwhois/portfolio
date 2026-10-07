<?php

namespace App\Livewire\Panel;

use App\Models\ActivityLog;
use App\Models\ClientProject;
use App\Models\ProjectFile;
use App\Models\ProjectMedia;
use App\Models\ServiceTier;
use App\Models\TechPackage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.panel')]
class Order extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?int $editingProjectId = null;

    // Step 1 — basics
    public string $title = '';

    public string $description = '';

    public ?int $packageId = null;

    public ?int $tierId = null;

    public string $timelinePreference = '';

    public ?string $preferredDeadline = null;

    // Step 2 — requirements
    public string $requirements = '';

    // Step 3 — references & design
    public string $exampleUrl = '';

    public string $demoUrl = '';

    public string $designNotes = '';

    public array $colorPalette = [];

    public string $colorInput = '';

    // Step 4 — budget
    public ?string $budgetFrom = null;

    public ?string $budgetTo = null;

    // Step 5 — materials
    public array $docs = [];

    public array $images = [];

    public $video = null;

    public function mount(?ClientProject $project = null): void
    {
        if (! $project) {
            return;
        }

        abort_unless($project->user_id === auth()->id() && $project->isEditableByCustomer(), 403);

        $this->editingProjectId = $project->id;
        $this->title = $project->title;
        $this->description = $project->description ?? '';
        $this->packageId = $project->tech_package_id;
        $this->tierId = $project->service_tier_id;
        $this->timelinePreference = $project->timeline_preference ?? '';
        $this->preferredDeadline = $project->preferred_deadline?->format('Y-m-d');
        $this->requirements = $project->requirements ?? '';
        $this->exampleUrl = $project->example_url ?? '';
        $this->demoUrl = $project->demo_url ?? '';
        $this->designNotes = $project->design_notes ?? '';
        $this->colorPalette = $project->color_palette ?? [];
        $this->budgetFrom = $project->budget_from;
        $this->budgetTo = $project->budget_to;
    }

    public function nextStep(): void
    {
        $this->validate($this->stepRules($this->step));

        $this->step = min(6, $this->step + 1);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step < $this->step) {
            $this->step = $step;
        }
    }

    public function addColor(): void
    {
        $this->validate(['colorInput' => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i']]);

        if (count($this->colorPalette) >= 5) {
            return;
        }

        $this->colorPalette[] = strtolower($this->colorInput);
        $this->colorInput = '';
    }

    public function removeColor(int $index): void
    {
        unset($this->colorPalette[$index]);
        $this->colorPalette = array_values($this->colorPalette);
    }

    public function updatedImages(): void
    {
        $this->validateOnly('images');
    }

    public function updatedVideo(): void
    {
        $this->validateOnly('video');
    }

    public function removeExistingMedia(int $mediaId): void
    {
        $media = ProjectMedia::query()->findOrFail($mediaId);

        abort_unless($media->client_project_id === $this->editingProjectId, 403);

        Storage::disk('local')->delete($media->path);
        $media->delete();
    }

    public function removeExistingDoc(int $fileId): void
    {
        $file = ProjectFile::query()->findOrFail($fileId);

        abort_unless($file->client_project_id === $this->editingProjectId && $file->isAttachment(), 403);

        Storage::disk('local')->delete($file->path);
        $file->delete();
    }

    /**
     * Step-aware form submission: Enter/Submit on steps 1-5 advances the
     * wizard instead of running full validation for every step at once.
     */
    public function handleSubmit()
    {
        if ($this->step < 6) {
            $this->nextStep();

            return;
        }

        return $this->submit();
    }

    public function submit()
    {
        foreach ([1, 2, 3, 4, 5] as $step) {
            $this->validate($this->stepRules($step));
        }

        $data = [
            'title' => trim($this->title),
            'description' => trim($this->description) ?: null,
            'tech_package_id' => $this->packageId,
            'service_tier_id' => $this->tierId,
            'timeline_preference' => $this->timelinePreference ?: null,
            'preferred_deadline' => $this->preferredDeadline ?: null,
            'requirements' => trim($this->requirements),
            'example_url' => trim($this->exampleUrl) ?: null,
            'demo_url' => trim($this->demoUrl) ?: null,
            'design_notes' => trim($this->designNotes) ?: null,
            'color_palette' => $this->colorPalette ?: null,
            'budget_from' => $this->budgetFrom !== '' && $this->budgetFrom !== null ? $this->budgetFrom : null,
            'budget_to' => $this->budgetTo !== '' && $this->budgetTo !== null ? $this->budgetTo : null,
        ];

        if ($this->editingProjectId) {
            $project = ClientProject::query()->findOrFail($this->editingProjectId);

            abort_unless($project->user_id === auth()->id() && $project->isEditableByCustomer(), 403);

            // A revised brief resets any outstanding negotiation offer.
            $project->update([...$data, 'offer_price' => null, 'offer_note' => null, 'offer_status' => null]);

            ActivityLog::record('order.updated', auth()->user(), ['project' => $project->title]);
        } else {
            $project = ClientProject::query()->create([
                'user_id' => auth()->id(),
                'status' => ClientProject::STATUS_PENDING,
                'source' => ClientProject::SOURCE_CLIENT,
                'submitted_at' => now(),
                ...$data,
            ]);

            ActivityLog::record('order.submitted', auth()->user(), ['project' => $project->title]);
        }

        $this->persistUploads($project);

        session()->flash('status', 'Your order has been submitted — the team will review your brief and respond with an offer.');

        return redirect()->route('panel.projects.show', $project);
    }

    private function persistUploads(ClientProject $project): void
    {
        foreach ($this->docs as $doc) {
            $path = $doc->store('client-projects/'.$project->id.'/attachments', 'local');

            ProjectFile::query()->create([
                'client_project_id' => $project->id,
                'name' => $doc->getClientOriginalName(),
                'path' => $path,
                'size' => $doc->getSize(),
                'mime' => $doc->getMimeType(),
                'kind' => ProjectFile::KIND_ATTACHMENT,
            ]);
        }

        $position = $project->media()->count();

        foreach ($this->images as $image) {
            $path = $image->store('client-projects/'.$project->id.'/media', 'local');

            ProjectMedia::query()->create([
                'client_project_id' => $project->id,
                'type' => ProjectMedia::TYPE_IMAGE,
                'path' => $path,
                'position' => $position++,
            ]);
        }

        if ($this->video) {
            $path = $this->video->store('client-projects/'.$project->id.'/media', 'local');

            ProjectMedia::query()->create([
                'client_project_id' => $project->id,
                'type' => ProjectMedia::TYPE_VIDEO,
                'path' => $path,
                'position' => $position++,
            ]);
        }
    }

    /**
     * Full rule set across all brief steps — required by Livewire's
     * validateOnly() in the upload hooks (updatedImages/updatedVideo).
     */
    protected function rules(): array
    {
        return array_merge(
            $this->stepRules(1),
            $this->stepRules(2),
            $this->stepRules(3),
            $this->stepRules(4),
            $this->stepRules(5),
        );
    }

    private function stepRules(int $step): array
    {
        return match ($step) {
            1 => [
                'title' => ['required', 'string', 'max:190'],
                'description' => ['nullable', 'string', 'max:2000'],
                'packageId' => ['nullable', 'exists:tech_packages,id'],
                'tierId' => ['nullable', 'exists:service_tiers,id'],
                'timelinePreference' => ['nullable', Rule::in(array_keys(ClientProject::TIMELINE_PREFERENCES))],
                'preferredDeadline' => ['nullable', 'date', 'after_or_equal:today'],
            ],
            2 => [
                'requirements' => ['required', 'string', 'max:5000'],
            ],
            3 => [
                'exampleUrl' => ['nullable', 'url', 'max:255'],
                'demoUrl' => ['nullable', 'url', 'max:255'],
                'designNotes' => ['nullable', 'string', 'max:2000'],
                'colorPalette' => ['array', 'max:5'],
                'colorPalette.*' => ['regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            ],
            4 => [
                'budgetFrom' => ['nullable', 'numeric', 'min:0'],
                'budgetTo' => ['nullable', 'numeric', 'min:0', 'gte:budgetFrom'],
            ],
            5 => [
                'docs' => ['array', 'max:5'],
                'docs.*' => ['file', 'mimes:pdf,doc,docx,txt,md,rtf,odt', 'max:20480'],
                'images' => ['array', 'max:6'],
                'images.*' => ['image', 'mimes:jpeg,png,webp,gif', 'max:10240'],
                'video' => ['nullable', 'file', 'mimes:mp4,webm,ogg,ogv,mov', 'max:65536'],
            ],
            default => [],
        };
    }

    #[Computed]
    public function editingProject()
    {
        return $this->editingProjectId
            ? ClientProject::query()->find($this->editingProjectId)
            : null;
    }

    #[Computed]
    public function existingMedia()
    {
        return $this->editingProjectId
            ? ProjectMedia::query()->where('client_project_id', $this->editingProjectId)->orderBy('position')->get()
            : collect();
    }

    #[Computed]
    public function existingDocs()
    {
        return $this->editingProjectId
            ? ProjectFile::query()->where('client_project_id', $this->editingProjectId)->where('kind', ProjectFile::KIND_ATTACHMENT)->latest()->get()
            : collect();
    }

    #[Computed]
    public function packages()
    {
        return TechPackage::query()->whereNotNull('price')->orderBy('sort_order')->get();
    }

    #[Computed]
    public function tiers()
    {
        return ServiceTier::query()->with('category')->orderBy('sort_order')->get();
    }

    #[Computed]
    public function stepMeta()
    {
        return [
            1 => ['label' => 'Basics', 'icon' => 'briefcase'],
            2 => ['label' => 'Requirements', 'icon' => 'doc'],
            3 => ['label' => 'Design', 'icon' => 'sparkle'],
            4 => ['label' => 'Budget', 'icon' => 'cash'],
            5 => ['label' => 'Materials', 'icon' => 'upload'],
            6 => ['label' => 'Review', 'icon' => 'check-circle'],
        ];
    }

    public function render()
    {
        return view('livewire.panel.order');
    }
}
