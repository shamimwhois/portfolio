<?php

namespace App\Livewire\Concerns;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\TechPackage;
use App\Support\Payments;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

/**
 * Shared direct-buy flow for sellable items. The Packages section, the
 * Service List marketplace and the Group Buy marketplace all use the exact
 * same checkout state machine, so it lives here rather than being copied.
 *
 * Subclasses point the flow at their own sellable model via checkoutModel()
 * and checkoutForeignKey(); everything else is resolved generically.
 */
trait InteractsWithPackageCheckout
{
    use RedirectsToGatewayCheckout;

    public ?string $message = null;

    public ?int $selectedPackageId = null;

    public ?int $lastOrderId = null;

    public string $paymentMethod = '';

    public string $notes = '';

    public bool $termsAccepted = false;

    public bool $reviewed = false;

    public function purchase(int $packageId): void
    {
        $this->message = null;
        $this->lastOrderId = null;

        $package = $this->findPurchasablePackage($packageId);

        if (! $package->isSellable()) {
            $this->message = 'This package is listed for reference only and is not for sale.';

            return;
        }

        if (! auth()->check()) {
            $this->redirect($this->guestRedirectUrl($package));

            return;
        }

        if ($this->hasActiveLicense($package->id)) {
            $this->message = 'You already have an active license for this package. Check your panel.';

            return;
        }

        $this->selectedPackageId = $package->id;
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
        if (! $this->selectedPackageId) {
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
        $this->selectedPackageId = null;
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
        if (! $this->selectedPackageId) {
            return null;
        }

        $this->validate([
            'paymentMethod' => ['required', Rule::in(array_keys(Payments::methods()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'termsAccepted' => ['accepted'],
        ]);

        $package = $this->findPurchasablePackage($this->selectedPackageId);

        if (! $package->isSellable()) {
            $this->message = 'This package is listed for reference only and is not for sale.';
            $this->cancelCheckout();

            return null;
        }

        if ($this->hasActiveLicense($package->id)) {
            $this->message = 'You already have an active license for this package. Check your panel.';
            $this->cancelCheckout();

            return null;
        }

        $method = $this->paymentMethod;
        $notes = $this->buildInvoiceNotes($package);

        $invoice = Invoice::query()->create([
            'user_id' => auth()->id(),
            $this->checkoutForeignKey() => $package->id,
            'invoice_no' => 'INV-'.date('Y').'-'.str_pad((string) Invoice::query()->max('id') + 1, 4, '0', STR_PAD_LEFT),
            'amount' => (float) $package->price,
            'currency' => $this->checkoutCurrency(),
            'status' => 'pending',
            'payment_method' => $method,
            'purchased_at' => now(),
            'notes' => $notes,
            'terms_accepted' => $this->termsAccepted,
        ]);

        ActivityLog::record($this->purchaseActivityKey(), auth()->user(), [
            'package' => $package->title,
            'price' => $package->displayPrice(),
            'method' => $method,
        ]);

        // Gateway methods redirect the customer to the hosted checkout page.
        if ($redirect = $this->startGatewayCheckout($invoice)) {
            return $redirect;
        }

        if ($this->gatewayUnavailable) {
            return null;
        }

        $this->message = 'Package '.$package->title.' requested via '.(Payments::method($method)['label'] ?? $method).'. Follow the payment instructions to finalize.';
        $this->lastOrderId = $invoice->id;
        $this->cancelCheckout();
    }

    private function hasActiveLicense(int $packageId): bool
    {
        return Invoice::query()
            ->where('user_id', auth()->id())
            ->where($this->checkoutForeignKey(), $packageId)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();
    }

    /**
     * Resolve the item for purchase — must still be published so an item
     * unpublished mid-checkout cannot be bought.
     */
    private function findPurchasablePackage(int $packageId): object
    {
        $model = $this->checkoutModel();

        return $model::query()->published()->findOrFail($packageId);
    }

    #[Computed]
    public function checkoutPackage()
    {
        if (! $this->selectedPackageId) {
            return null;
        }

        $model = $this->checkoutModel();

        return $model::query()->published()->find($this->selectedPackageId);
    }

    #[Computed]
    public function lastOrder()
    {
        return $this->lastOrderId
            ? Invoice::query()->with('package', 'groupPackage')->find($this->lastOrderId)
            : null;
    }

    /**
     * Hook: sellable model class this checkout flow resolves.
     */
    protected function checkoutModel(): string
    {
        return TechPackage::class;
    }

    /**
     * Hook: invoice foreign key that links to the sellable model.
     */
    protected function checkoutForeignKey(): string
    {
        return 'tech_package_id';
    }

    /**
     * Hook: invoice currency for purchases made through this flow.
     */
    protected function checkoutCurrency(): string
    {
        return Payments::currency();
    }

    /**
     * Hook: where guests are sent to sign up (may carry a preselect param).
     */
    protected function guestRedirectUrl(object $package): string
    {
        return route('register', ['pkg' => $package->id]);
    }

    /**
     * Hook: extra context appended to the invoice notes (e.g. chosen validity).
     */
    protected function buildInvoiceNotes(object $package): ?string
    {
        $notes = trim($this->notes);

        return $notes ?: null;
    }

    /**
     * Hook: ActivityLog action key for the purchase.
     */
    protected function purchaseActivityKey(): string
    {
        return 'package.purchased';
    }
}
