<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Skill;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Skills extends Component
{
    /** @var int Skills per page chunk — pagination via Load more. */
    public int $limit = 10;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public int $level = 50;

    public string $icon = '';

    public int $sort_order = 0;

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function edit(Skill $skill): void
    {
        $this->resetForm();
        $this->editingId = $skill->id;
        $this->showForm = true;
        $this->name = $skill->name;
        $this->level = $skill->level;
        $this->icon = $skill->icon ?? '';
        $this->sort_order = $skill->sort_order;
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
            'name' => ['required', 'string', 'max:120'],
            'level' => ['required', 'integer', 'between:0,100'],
            'icon' => ['nullable', 'string', 'max:8'],
            'sort_order' => ['integer'],
        ]);

        if ($this->editingId) {
            $skill = Skill::query()->findOrFail($this->editingId);
            $skill->update($validated);
            session()->flash('status', 'Skill updated.');
        } else {
            $skill = Skill::query()->create($validated);
            ActivityLog::record('admin.skill.created', auth()->user(), ['skill' => $skill->name]);
            session()->flash('status', 'Skill created.');
        }

        $this->editingId = null;
        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(Skill $skill): void
    {
        ActivityLog::record('admin.skill.deleted', auth()->user(), ['skill' => $skill->name]);
        $skill->delete();
        session()->flash('status', 'Skill deleted.');
    }

    public function setStatus(Skill $skill, string $status): void
    {
        if (! in_array($status, Skill::STATUSES, true)) {
            return;
        }

        $skill->update(['publish_status' => $status]);
        ActivityLog::record('admin.skill.status_changed', auth()->user(), ['skill' => $skill->name, 'status' => $status]);
        session()->flash('status', 'Skill marked as '.ucfirst($status).'.');
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    #[Computed]
    public function skills()
    {
        return Skill::query()->orderBy('sort_order')->limit($this->limit + 1)->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->skills->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return Skill::query()->count();
    }

    private function resetForm(): void
    {
        $this->reset('name', 'level', 'icon', 'sort_order');
        $this->level = 50;
    }

    public function render()
    {
        return view('livewire.admin.skills');
    }
}
