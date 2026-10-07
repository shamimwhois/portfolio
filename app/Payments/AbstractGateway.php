<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Payments\Contracts\Gateway;
use App\Support\Payments;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class AbstractGateway implements Gateway
{
    public function __construct(protected string $key) {}

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return Payments::gateway($this->key)['label'] ?? ucfirst($this->key);
    }

    public function type(): string
    {
        return Payments::gateway($this->key)['type'] ?? 'Online Payment';
    }

    public function mode(): string
    {
        return Payments::gatewayMode($this->key);
    }

    public function isEnabled(): bool
    {
        return (bool) (Payments::gateway($this->key)['enabled'] ?? false);
    }

    public function isConfigured(): bool
    {
        return Payments::isGatewayAvailable($this->key);
    }

    /**
     * Resolve a credential for the active mode (env override supported).
     */
    protected function credential(string $field): ?string
    {
        return Payments::credential($this->key, $field);
    }

    /**
     * Throw unless every listed credential resolves for the active mode.
     */
    protected function requireCredentials(array $fields): void
    {
        foreach ($fields as $field) {
            if (! filled($this->credential($field))) {
                throw new PaymentException("Gateway [{$this->key}] is missing the [{$field}] credential for mode [{$this->mode()}].");
            }
        }
    }

    public function successUrl(Invoice $invoice): string
    {
        return $this->tokenize(route('payment.success', ['invoiceNo' => $invoice->invoice_no]), $invoice);
    }

    public function cancelUrl(Invoice $invoice): string
    {
        return $this->tokenize(route('payment.cancel', ['invoiceNo' => $invoice->invoice_no]), $invoice);
    }

    public function callbackUrl(Invoice $invoice): string
    {
        return $this->tokenize(route('payment.callback', ['invoiceNo' => $invoice->invoice_no, 'gateway' => $this->key]), $invoice);
    }

    public function webhookUrl(): string
    {
        return route('payment.webhook', ['gateway' => $this->key]);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Overridden by gateways with signed webhooks; others confirm via verify().
        return true;
    }

    public function webhookSignatureHeader(): string
    {
        return 'Stripe-Signature';
    }

    public function invoiceNoFromWebhook(array $payload): ?string
    {
        return $payload['tran_id']
            ?? $payload['merchant_invoice_number']
            ?? $payload['merchantInvoiceNumber']
            ?? null;
    }

    /**
     * Amount in major units, always two decimals ("12.50").
     */
    protected function amountMajor(Invoice $invoice): string
    {
        return number_format((float) $invoice->amount, 2, '.', '');
    }

    /**
     * Amount in minor units (cents / paise) as an integer.
     */
    protected function amountMinor(Invoice $invoice): int
    {
        return (int) round(((float) $invoice->amount) * 100);
    }

    /**
     * Item description for the gateway checkout page.
     */
    protected function description(Invoice $invoice): string
    {
        $label = $invoice->itemLabel();

        return trim('Invoice '.$invoice->invoice_no.($label && $label !== 'Custom order' ? ' — '.$label : ''));
    }

    /**
     * Default test connection — gateways that can ping their provider sandbox
     * override this. Returns "not supported" by default so manual/legacy
     * gateways never break.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(): array
    {
        return [
            'success' => false,
            'message' => 'Test connection is not supported for this gateway.',
        ];
    }

    /**
     * Base HTTP client with a sane timeout for all gateway calls.
     */
    protected function http(): PendingRequest
    {
        return Http::timeout(30)->acceptJson();
    }

    /**
     * Append a per-invoice signed token to a return URL so only the party
     * that was handed the URL (the gateway redirecting the customer back) can
     * trigger success/cancel handling for that invoice.
     */
    protected function tokenize(string $url, Invoice $invoice): string
    {
        return $url.(str_contains($url, '?') ? '&' : '?').'token='.static::signature($invoice);
    }

    public static function signature(Invoice $invoice): string
    {
        return hash_hmac('sha256', $invoice->invoice_no, (string) config('app.key'));
    }
}
