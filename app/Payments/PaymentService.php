<?php

namespace App\Payments;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Payments\Contracts\Gateway;
use App\Support\Payments;

class PaymentService
{
    public function __construct(private GatewayManager $manager) {}

    /**
     * Start checkout for a fresh invoice. Returns a redirect to the gateway's
     * hosted page when the chosen method is a configured gateway, or null for
     * manual methods (the manual confirmation flow keeps running).
     *
     * @throws PaymentException when the gateway cannot be reached or is unconfigured
     */
    public function start(Invoice $invoice)
    {
        $key = (string) $invoice->payment_method;

        if (! Payments::isGateway($key) || ! $this->manager->has($key)) {
            return null;
        }

        $gateway = $this->manager->driver($key);

        if (! $gateway->isConfigured()) {
            throw new PaymentException("Payment gateway [{$key}] is not configured for mode [{$gateway->mode()}].");
        }

        $redirect = $gateway->start($invoice);

        $invoice->update([
            'gateway' => $key,
            'gateway_reference' => $redirect->reference,
        ]);

        ActivityLog::record('payment.redirected', $invoice->user, [
            'invoice' => $invoice->invoice_no,
            'gateway' => $key,
            'reference' => $redirect->reference,
        ]);

        return redirect()->away($redirect->url);
    }

    /**
     * Handle a provider payload (success page, callback or webhook) and
     * finalize the invoice. Returns the invoice's resulting status.
     */
    public function complete(Invoice $invoice, array $payload): string
    {
        $gateway = $this->resolveGateway($invoice);

        if (! $gateway) {
            return $invoice->status;
        }

        if ($gateway->verify($invoice, $payload)) {
            $this->markPaid($invoice, $gateway->referenceFromPayload($payload));
        }

        return $invoice->status;
    }

    /**
     * Mark an invoice as paid (idempotent) with the gateway reference.
     */
    public function markPaid(Invoice $invoice, ?string $reference = null): void
    {
        if ($invoice->status === 'paid') {
            return;
        }

        $invoice->update([
            'status' => 'paid',
            'gateway_reference' => $reference ?? $invoice->gateway_reference,
            'paid_at' => now(),
        ]);

        ActivityLog::record('invoice.paid', $invoice->user, [
            'invoice' => $invoice->invoice_no,
            'gateway' => $invoice->gateway,
            'reference' => $invoice->gateway_reference,
        ]);
    }

    /**
     * Mark an invoice as cancelled (only if still pending).
     */
    public function cancel(Invoice $invoice): void
    {
        if ($invoice->status !== 'pending') {
            return;
        }

        $invoice->update(['status' => 'cancelled']);

        ActivityLog::record('invoice.cancelled', $invoice->user, [
            'invoice' => $invoice->invoice_no,
            'gateway' => $invoice->gateway,
        ]);
    }

    private function resolveGateway(Invoice $invoice): ?Gateway
    {
        $key = (string) ($invoice->gateway ?? $invoice->payment_method);

        if (! $key || ! $this->manager->has($key)) {
            return null;
        }

        return $this->manager->driver($key);
    }
}
