<?php

namespace App\Livewire\Concerns;

use App\Models\Invoice;
use App\Payments\PaymentException;
use App\Payments\PaymentService;
use App\Support\Payments;

/**
 * Shared gateway dispatch for the two checkout flows (service plans and the
 * package-style trait). Both create a pending invoice and then need to decide
 * between redirecting to a hosted gateway page and falling back to the manual
 * confirmation message — this keeps that logic in exactly one place.
 */
trait RedirectsToGatewayCheckout
{
    /**
     * True when the last attempt to reach a gateway failed and the checkout
     * should stop (a friendly message is already set) instead of falling
     * through to the manual confirmation message.
     */
    public bool $gatewayUnavailable = false;

    /**
     * When the chosen method is a configured gateway, redirect to its hosted
     * checkout. On failure the pending invoice is discarded, a friendly
     * message is set and $gatewayUnavailable is raised so the caller stops.
     */
    protected function startGatewayCheckout(Invoice $invoice)
    {
        if (! Payments::isGateway((string) $invoice->payment_method)) {
            return null;
        }

        try {
            return app(PaymentService::class)->start($invoice);
        } catch (PaymentException) {
            $invoice->delete();
            $this->gatewayUnavailable = true;
            $this->message = 'The payment gateway is temporarily unavailable. Please try again or choose a manual method.';
            $this->cancelCheckout();

            return null;
        }
    }
}
