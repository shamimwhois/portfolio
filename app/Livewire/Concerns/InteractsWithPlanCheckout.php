<?php

namespace App\Livewire\Concerns;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\ServiceTier;
use App\Support\Payments;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

/**
 * Shared subscription-plan checkout flow (ServiceCategory → ServiceTier).
 * Used by the home #services section and the standalone /plan page so the
 * purchase state machine lives in exactly one place.
 */
trait InteractsWithPlanCheckout
{
    use RedirectsToGatewayCheckout;

    public ?string $message = null;

    public ?int $selectedTierId = null;

    public ?int $lastOrderId = null;

    public string $paymentMethod = '';

    public string $notes = '';

    public bool $termsAccepted = false;

    public bool $reviewed = false;

    public function purchase(ServiceTier $tier): void
    {
        $this->message = null;
        $this->lastOrderId = null;

        if (! auth()->check()) {
            $this->redirect(route('register', ['plan' => $tier->id]));

            return;
        }

        if ($this->hasActiveOrder($tier->id)) {
            $this->message = 'You already have an active order for this plan. Check your billing panel.';

            return;
        }

        $this->selectedTierId = $tier->id;
        $this->paymentMethod = '';
        $this->notes = '';
        $this->termsAccepted = false;
        $this->reviewed = false;
    }

    public function setPaymentMethod(string $method): void
    {
        if (array_key_exists($method, Payments::methods())) {
            $this->paymentMethod = $method;
        }
    }

    public function goToPayment(): void
    {
        if (! $this->selectedTierId) {
            return;
        }

        $this->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'termsAccepted' => ['accepted'],
        ]);

        $this->reviewed = true;
    }

    public function backToReview(): void
    {
        $this->reviewed = false;
        $this->paymentMethod = '';
    }

    public function cancelCheckout(): void
    {
        $this->selectedTierId = null;
        $this->paymentMethod = '';
        $this->notes = '';
        $this->termsAccepted = false;
        $this->reviewed = false;
    }

    public function closeCheckout(): void
    {
        $this->lastOrderId = null;
        $this->cancelCheckout();
    }

    public function confirmPurchase()
    {
        if (! $this->selectedTierId) {
            return null;
        }

        $this->validate([
            'paymentMethod' => ['required', Rule::in(array_keys(Payments::methods()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'termsAccepted' => ['accepted'],
        ]);

        $tier = ServiceTier::query()->findOrFail($this->selectedTierId);

        if ($this->hasActiveOrder($tier->id)) {
            $this->message = 'You already have an active order for this plan. Check your billing panel.';
            $this->cancelCheckout();

            return null;
        }

        $method = $this->paymentMethod;

        $invoice = Invoice::query()->create([
            'user_id' => auth()->id(),
            'service_tier_id' => $tier->id,
            'invoice_no' => 'INV-'.date('Y').'-'.str_pad((string) Invoice::query()->max('id') + 1, 4, '0', STR_PAD_LEFT),
            'amount' => (float) preg_replace('/[^0-9.]/', '', $tier->price),
            'currency' => Payments::currency(),
            'status' => 'pending',
            'payment_method' => $method,
            'purchased_at' => now(),
            'notes' => trim($this->notes) ?: null,
            'terms_accepted' => $this->termsAccepted,
        ]);

        ActivityLog::record('invoice.requested', auth()->user(), [
            'tier' => $tier->name,
            'price' => $tier->price,
            'method' => $method,
        ]);

        // Gateway methods redirect the customer to the hosted checkout page.
        if ($redirect = $this->startGatewayCheckout($invoice)) {
            return $redirect;
        }

        if ($this->gatewayUnavailable) {
            return null;
        }

        $this->message = 'Plan '.$tier->name.' requested via '.(Payments::method($method)['label'] ?? $method).'. Follow the payment instructions to finalize.';
        $this->lastOrderId = $invoice->id;
        $this->cancelCheckout();
    }

    private function hasActiveOrder(int $tierId): bool
    {
        return Invoice::query()
            ->where('user_id', auth()->id())
            ->where('service_tier_id', $tierId)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();
    }

    #[Computed]
    public function checkoutTier()
    {
        return $this->selectedTierId
            ? ServiceTier::query()->published()->with('category')->find($this->selectedTierId)
            : null;
    }

    #[Computed]
    public function lastOrder()
    {
        return $this->lastOrderId
            ? Invoice::query()->with('tier.category', 'package')->find($this->lastOrderId)
            : null;
    }
}
