<?php

namespace App\Livewire\Panel;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Projects extends Component
{
    public string $statusFilter = 'all';

    public string $sourceFilter = 'all';

    #[Computed]
    public function projects()
    {
        return auth()->user()
            ->clientProjects()
            ->with('invoice.tier.category', 'invoice.package')
            ->withCount(['tasks', 'features', 'completedTasks', 'completedFeatures', 'deliverables', 'media'])
            ->when($this->sourceFilter !== 'all', fn ($q) => $q->where('source', $this->sourceFilter))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->get();
    }

    #[Computed]
    public function reminders()
    {
        return auth()->user()
            ->clientProjects()
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now())
            ->whereIn('status', ['pending', 'in_progress', 'review'])
            ->latest('remind_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.panel.projects');
    }
}
