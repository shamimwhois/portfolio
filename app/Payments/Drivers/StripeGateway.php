<?php

namespace App\Payments\Drivers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentRedirect;

class StripeGateway extends AbstractGateway
{
    private const API = 'https://api.stripe.com/v1';

    public function start(Invoice $invoice): PaymentRedirect
    {
        $this->requireCredentials(['secret_key']);

        $secret = $this->credential('secret_key');

        $response = $this->http()->withToken($secret)
            ->asForm()
            ->post(self::API.'/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $this->successUrl($invoice),
                'cancel_url' => $this->cancelUrl($invoice),
                'client_reference_id' => $invoice->invoice_no,
                'customer_email' => $invoice->user?->email,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => $invoice->currency ?: 'usd',
                'line_items[0][price_data][unit_amount]' => $this->amountMinor($invoice),
                'line_items[0][price_data][product_data][name]' => mb_substr($this->description($invoice), 0, 120),
            ]);

        if ($response->failed()) {
            throw new PaymentException('Stripe checkout could not be created: '.$response->body());
        }

        return new PaymentRedirect(
            url: $response->json('url'),
            reference: $response->json('id'),
        );
    }

    public function verify(Invoice $invoice, array $payload): bool
    {
        $sessionId = $payload['session_id']
            ?? $payload['data']['object']['id']
            ?? $payload['id']
            ?? $invoice->gateway_reference;

        if (! $sessionId) {
            return false;
        }

        $response = $this->http()->withToken($this->credential('secret_key'))
            ->get(self::API.'/checkout/sessions/'.$sessionId);

        return ! $response->failed() && $response->json('payment_status') === 'paid';
    }

    public function referenceFromPayload(array $payload): ?string
    {
        return $payload['session_id'] ?? null;
    }

    public function testConnection(): array
    {
        try {
            $this->requireCredentials(['secret_key']);
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'Secret key is not set for the active mode.'];
        }

        $response = $this->http()->withToken($this->credential('secret_key'))
            ->get(self::API.'/balance');

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Connected · Stripe account balance retrieved successfully.',
            ];
        }

        $body = $response->body();
        $error = $response->json('error.message') ?? $response->json('error.type') ?? 'unknown error';

        return [
            'success' => false,
            'message' => 'Connection failed: '.$error,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = $this->credential('webhook_secret');

        if (! $secret || ! $signature) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? null;
        $expected = $parts['v1'] ?? null;

        if (! $timestamp || ! $expected) {
            return false;
        }

        $signed = $timestamp.'.'.$payload;
        $computed = hash_hmac('sha256', $signed, $secret);

        return hash_equals($computed, $expected);
    }

    public function invoiceNoFromWebhook(array $payload): ?string
    {
        return $payload['data']['object']['client_reference_id'] ?? null;
    }
}