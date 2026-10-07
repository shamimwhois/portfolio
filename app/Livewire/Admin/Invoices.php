<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Invoice;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Invoices extends Component
{
    public string $status = 'all';

    public string $search = '';

    /** @var int Items per page chunk — pagination via Load more. */
    public int $limit = 10;

    public ?int $viewingId = null;

    public function updateStatus(string $status): void
    {
        $this->status = $status;
    }

    public function view(Invoice $invoice): void
    {
        $this->viewingId = $invoice->id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function markPaid(Invoice $invoice): void
    {
        if ($invoice->status !== 'pending') {
            session()->flash('error', 'Only pending orders can be marked as paid.');

            return;
        }

        $invoice->update(['status' => 'paid', 'paid_at' => $invoice->paid_at ?? now()]);

        if (! $invoice->clientProject()->exists()) {
            $invoice->clientProject()->create([
                'user_id' => $invoice->user_id,
                'title' => $invoice->itemLabel(),
                'description' => 'Project created automatically from order '.$invoice->invoice_no.'.',
                'status' => \App\Models\ClientProject::STATUS_PENDING,
                'start_date' => now(),
            ]);

            session()->flash('status', "Order {$invoice->invoice_no} marked as paid. A client project was created for tracking.");
        } else {
            session()->flash('status', "Order {$invoice->invoice_no} marked as paid.");
        }

        ActivityLog::record('invoice.marked_paid', auth()->user(), ['invoice' => $invoice->invoice_no]);
    }

    public function cancel(Invoice $invoice): void
    {
        if (! in_array($invoice->status, ['pending', 'paid'])) {
            session()->flash('error', 'This order cannot be cancelled.');

            return;
        }

        $invoice->update(['status' => 'cancelled']);
        ActivityLog::record('invoice.cancelled', auth()->user(), ['invoice' => $invoice->invoice_no]);
        session()->flash('status', "Order {$invoice->invoice_no} cancelled.");
    }

    public function refund(Invoice $invoice): void
    {
        if ($invoice->status !== 'paid') {
            session()->flash('error', 'Only paid orders can be refunded.');

            return;
        }

        $invoice->update(['status' => 'refunded']);
        ActivityLog::record('invoice.refunded', auth()->user(), ['invoice' => $invoice->invoice_no]);
        session()->flash('status', "Order {$invoice->invoice_no} refunded.");
    }

    #[Computed]
    public function viewingInvoice()
    {
        return $this->viewingId
            ? Invoice::query()
                ->with('user', 'tier.category', 'package', 'groupPackage', 'clientProject')
                ->find($this->viewingId)
            : null;
    }

    public function loadMore(): void
    {
        $this->limit += 10;
    }

    public function updatedSearch(): void
    {
        $this->limit = 10;
    }

    public function updatedStatus(): void
    {
        $this->limit = 10;
    }

    #[Computed]
    public function invoices()
    {
        return Invoice::query()
            ->with('user', 'tier.category', 'package')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when(trim($this->search) !== '', fn ($q) => $q->where(function ($q) {
                $q->where('invoice_no', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'));
            }))
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->invoices->count() > $this->limit;
    }

    #[Computed]
    public function matchingTotal(): int
    {
        return Invoice::query()
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when(trim($this->search) !== '', fn ($q) => $q->where(function ($q) {
                $q->where('invoice_no', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'));
            }))
            ->count();
    }

    #[Computed]
    public function counts(): array
    {
        return [
            'all' => Invoice::query()->count(),
            'pending' => Invoice::query()->where('status', 'pending')->count(),
            'paid' => Invoice::query()->where('status', 'paid')->count(),
            'cancelled' => Invoice::query()->where('status', 'cancelled')->count(),
            'refunded' => Invoice::query()->where('status', 'refunded')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.invoices');
    }
}
