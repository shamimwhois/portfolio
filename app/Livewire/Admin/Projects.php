<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Projects extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $link = '';

    public string $thumbnail = '';

    public string $tech = '';

    public string $status = 'Live';

    public bool $featured = true;

    public int $sort_order = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(Project $project): void
    {
        $this->resetForm();
        $this->editingId = $project->id;
        $this->showForm = true;
        $this->title = $project->title;
        $this->description = $project->description ?? '';
        $this->link = $project->link ?? '';
        $this->thumbnail = $project->thumbnail ?? '';
        $this->tech = implode(', ', $project->tech ?? []);
        $this->status = $project->status;
        $this->featured = (bool) $project->featured;
        $this->sort_order = $project->sort_order;
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
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'link' => ['nullable', 'url', 'max:500'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'tech' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'max:40'],
            'featured' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $data = [
            ...$validated,
            'tech' => collect(explode(',', $validated['tech']))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->values()
                ->all(),
        ];

        if ($this->editingId) {
            $project = Project::query()->findOrFail($this->editingId);
            $project->update($data);
            ActivityLog::record('admin.project.updated', auth()->user(), ['project' => $project->title]);
            session()->flash('status', 'Project updated.');
        } else {
            $project = Project::query()->create($data);
            ActivityLog::record('admin.project.created', auth()->user(), ['project' => $project->title]);
            session()->flash('status', 'Project created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Project $project): void
    {
        ActivityLog::record('admin.project.deleted', auth()->user(), ['project' => $project->title]);
        $project->delete();
        session()->flash('status', 'Project deleted.');
    }

    public function toggleFeatured(Project $project): void
    {
        $project->update(['featured' => ! $project->featured]);
    }

    public function setStatus(Project $project, string $status): void
    {
        if (! in_array($status, Project::STATUSES, true)) {
            return;
        }

        $project->update(['publish_status' => $status]);
        ActivityLog::record('admin.project.status_changed', auth()->user(), ['project' => $project->title, 'status' => $status]);
        session()->flash('status', 'Project marked as '.ucfirst($status).'.');
    }

    #[Computed]
    public function projects()
    {
        return Project::query()->orderBy('sort_order')->get();
    }

    private function resetForm(): void
    {
        $this->reset('title', 'description', 'link', 'thumbnail', 'tech', 'status', 'featured', 'sort_order');
        $this->status = 'Live';
        $this->featured = true;
    }

    public function render()
    {
        return view('livewire.admin.projects');
    }
}
