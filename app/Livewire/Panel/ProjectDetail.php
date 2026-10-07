<?php

namespace App\Livewire\Panel;

use App\Models\ActivityLog;
use App\Models\ClientProject;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class ProjectDetail extends Component
{
    public ClientProject $project;

    public function mount(ClientProject $project): void
    {
        abort_unless($project->user_id === auth()->id(), 403);

        $this->project = $project;
    }

    #[Computed]
    public function detail()
    {
        return $this->project->loadMissing([
            'invoice.tier.category',
            'invoice.package',
            'package',
            'tier',
            'tasks',
            'features',
            'updates.author',
            'deliverables',
            'attachments',
            'media',
        ]);
    }

    public function acceptOffer()
    {
        abort_unless($this->project->user_id === auth()->id(), 403);

        if (! $this->project->offerPending()) {
            session()->flash('error', 'There is no pending offer to accept.');

            return;
        }

        $this->project->update(['offer_status' => ClientProject::OFFER_ACCEPTED]);

        ActivityLog::record('order.offer.accepted', auth()->user(), ['project' => $this->project->title, 'amount' => $this->project->offer_price]);

        session()->flash('status', 'Offer accepted — we will confirm the start date shortly.');

        return redirect()->route('panel.projects.show', $this->project);
    }

    public function declineOffer()
    {
        abort_unless($this->project->user_id === auth()->id(), 403);

        if (! $this->project->offerPending()) {
            session()->flash('error', 'There is no pending offer to decline.');

            return;
        }

        $this->project->update(['offer_status' => ClientProject::OFFER_DECLINED]);

        ActivityLog::record('order.offer.declined', auth()->user(), ['project' => $this->project->title]);

        session()->flash('status', 'Offer declined. You can edit your brief or wait for a revised offer.');

        return redirect()->route('panel.projects.show', $this->project);
    }

    public function cancelOrder()
    {
        abort_unless($this->project->user_id === auth()->id() && $this->project->isEditableByCustomer(), 403);

        $this->project->update(['status' => ClientProject::STATUS_CANCELLED]);

        ActivityLog::record('order.cancelled', auth()->user(), ['project' => $this->project->title]);

        session()->flash('status', 'Your order has been cancelled.');

        return redirect()->route('panel.projects');
    }

    public function render()
    {
        return view('livewire.panel.project-detail');
    }
}
