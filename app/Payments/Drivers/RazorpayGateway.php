<?php

namespace App\Payments\Drivers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentRedirect;

class RazorpayGateway extends AbstractGateway
{
    private const API = 'https://api.razorpay.com/v1';

    public function start(Invoice $invoice): PaymentRedirect
    {
        $this->requireCredentials(['key_id', 'key_secret']);

        $response = $this->http()
            ->withBasicAuth($this->credential('key_id'), $this->credential('key_secret'))
            ->post(self::API.'/payment_links', [
                'amount' => $this->amountMinor($invoice),
                'currency' => $invoice->currency ?: 'INR',
                'accept_partial' => false,
                'description' => $this->description($invoice),
                'customer' => [
                    'name' => $invoice->user?->name ?? 'Customer',
                    'email' => $invoice->user?->email ?? '',
                    'contact' => $invoice->user?->phone ?? '',
                ],
                'notify' => ['email' => false, 'sms' => false],
                'callback_url' => $this->successUrl($invoice),
                'callback_method' => 'get',
                'notes' => ['invoice_no' => $invoice->invoice_no],
            ]);

        if ($response->failed() || ! $response->json('short_url')) {
            throw new PaymentException('Razorpay payment link could not be created: '.$response->body());
        }

        return new PaymentRedirect(
            url: $response->json('short_url'),
            reference: $response->json('id'),
        );
    }

    public function verify(Invoice $invoice, array $payload): bool
    {
        $linkId = $payload['razorpay_payment_link_id'] ?? $payload['id'] ?? $invoice->gateway_reference;

        if (! $linkId) {
            return false;
        }

        $response = $this->http()
            ->withBasicAuth($this->credential('key_id'), $this->credential('key_secret'))
            ->get(self::API.'/payment_links/'.$linkId);

        return ! $response->failed() && $response->json('status') === 'paid';
    }

    public function referenceFromPayload(array $payload): ?string
    {
        return $payload['razorpay_payment_link_id'] ?? null;
    }

    public function invoiceNoFromWebhook(array $payload): ?string
    {
        return $payload['payload']['payment_link']['notes']['invoice_no']
            ?? $payload['payload']['payment']['notes']['invoice_no']
            ?? null;
    }

    public function testConnection(): array
    {
        try {
            $this->requireCredentials(['key_id', 'key_secret']);
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'Key ID or key secret is not set for the active mode.'];
        }

        $response = $this->http()
            ->withBasicAuth($this->credential('key_id'), $this->credential('key_secret'))
            ->get(self::API.'/payments?count=1');

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Connected · Razorpay API credentials are valid.',
            ];
        }

        $error = $response->json('error.description') ?? $response->json('error.code') ?? 'unknown error';

        return [
            'success' => false,
            'message' => 'Connection failed: '.$error,
        ];
    }
}
