<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\TimelinePhase;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Timeline extends Component
{
    /** @var int Phases per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $days = '';

    public string $title = '';

    public string $description = '';

    public int $sort_order = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(TimelinePhase $phase): void
    {
        $this->resetForm();
        $this->editingId = $phase->id;
        $this->showForm = true;
        $this->days = $phase->days;
        $this->title = $phase->title;
        $this->description = $phase->description;
        $this->sort_order = $phase->sort_order;
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
            'days' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['required', 'string', 'max:2000'],
            'sort_order' => ['integer'],
        ]);

        if ($this->editingId) {
            $phase = TimelinePhase::query()->findOrFail($this->editingId);
            $phase->update($validated);
            session()->flash('status', 'Phase updated.');
        } else {
            $phase = TimelinePhase::query()->create($validated);
            ActivityLog::record('admin.timeline.created', auth()->user(), ['phase' => $phase->title]);
            session()->flash('status', 'Phase created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(TimelinePhase $phase): void
    {
        ActivityLog::record('admin.timeline.deleted', auth()->user(), ['phase' => $phase->title]);
        $phase->delete();
        session()->flash('status', 'Phase deleted.');
    }

    public function setStatus(TimelinePhase $phase, string $status): void
    {
        if (! in_array($status, TimelinePhase::STATUSES, true)) {
            return;
        }

        $phase->update(['publish_status' => $status]);
        ActivityLog::record('admin.timeline.status_changed', auth()->user(), ['phase' => $phase->title, 'status' => $status]);
        session()->flash('status', 'Phase marked as '.ucfirst($status).'.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function phases()
    {
        return TimelinePhase::query()->orderBy('sort_order')->limit($this->limit + 1)->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->phases->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return TimelinePhase::query()->count();
    }

    private function resetForm(): void
    {
        $this->reset('days', 'title', 'description', 'sort_order');
    }

    public function render()
    {
        return view('livewire.admin.timeline');
    }
}
