<?php

namespace App\Payments\Contracts;

use App\Models\Invoice;
use App\Payments\PaymentRedirect;

interface Gateway
{
    /**
     * Gateway key as used in config/payments.php and invoice.payment_method.
     */
    public function key(): string;

    public function label(): string;

    public function type(): string;

    public function mode(): string;

    public function isEnabled(): bool;

    /**
     * True when every credential for the active mode has been provided.
     */
    public function isConfigured(): bool;

    /**
     * Create a hosted checkout for the invoice. Returns where the customer's
     * browser should be redirected, plus the gateway's own reference for it.
     */
    public function start(Invoice $invoice): PaymentRedirect;

    /**
     * Server-side confirmation that the payment actually completed. The
     * payload is whatever the provider sent to our success/callback/webhook
     * endpoints.
     */
    public function verify(Invoice $invoice, array $payload): bool;

    /**
     * Extract the gateway reference (session/order/link id) from a callback
     * payload so we can persist it on the invoice.
     */
    public function referenceFromPayload(array $payload): ?string;

    public function successUrl(Invoice $invoice): string;

    public function cancelUrl(Invoice $invoice): string;

    public function callbackUrl(Invoice $invoice): string;

    public function webhookUrl(): string;

    /**
     * Ping the provider's sandbox (or live) API with the stored credentials
     * to confirm the connection actually works — no invoice required.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(): array;

    /**
     * Validate an incoming webhook signature. Gateways without signed
     * webhooks return true (they confirm via verify() instead).
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * HTTP header that carries the webhook signature for this gateway
     * (Stripe-Signature, BinancePay-Signature, ...).
     */
    public function webhookSignatureHeader(): string;

    /**
     * Resolve the invoice number from a webhook payload (defaults to common
     * tran_id / merchant_invoice_number fields).
     */
    public function invoiceNoFromWebhook(array $payload): ?string;
}
