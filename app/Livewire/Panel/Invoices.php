<?php

namespace App\Livewire\Panel;

use App\Payments\PaymentException;
use App\Payments\PaymentService;
use App\Support\Payments;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Invoices extends Component
{
    public string $status = '';

    public function payNow(\App\Models\Invoice $invoice)
    {
        abort_unless($invoice->user_id === auth()->id(), 403);

        if (! $invoice->isPending() || ! Payments::isGateway((string) $invoice->payment_method)) {
            return null;
        }

        try {
            return app(PaymentService::class)->start($invoice);
        } catch (PaymentException) {
            session()->flash('error', 'The payment gateway is temporarily unavailable. Please try again shortly.');

            return null;
        }
    }

    #[Computed]
    public function invoices()
    {
        return auth()->user()
            ->invoices()
            ->with('tier.category', 'package')
            ->when(
                $this->status === 'cancelled',
                fn ($q) => $q->whereIn('status', ['refunded', 'cancelled'])
            )
            ->when($this->status && $this->status !== 'cancelled', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->get();
    }

    #[Computed]
    public function counts()
    {
        $invoices = auth()->user()->invoices();

        return [
            'all' => (clone $invoices)->count(),
            'pending' => (clone $invoices)->where('status', 'pending')->count(),
            'paid' => (clone $invoices)->where('status', 'paid')->count(),
            'cancelled' => (clone $invoices)->whereIn('status', ['refunded', 'cancelled'])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.panel.invoices');
    }
}
