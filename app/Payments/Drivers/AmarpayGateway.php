<?php

namespace App\Payments\Drivers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentRedirect;

class AmarpayGateway extends AbstractGateway
{
    private function base(): string
    {
        return $this->mode() === 'live'
            ? 'https://secure.aamarpay.com'
            : 'https://sandbox.aamarpay.com';
    }

    public function start(Invoice $invoice): PaymentRedirect
    {
        $this->requireCredentials(['store_id', 'signature_key']);

        $response = $this->http()
            ->asForm()
            ->post($this->base().'/jsonpost.php', [
                'store_id' => $this->credential('store_id'),
                'signature_key' => $this->credential('signature_key'),
                'tran_id' => $invoice->invoice_no,
                'amount' => $this->amountMajor($invoice),
                'currency' => 'BDT',
                'desc' => $this->description($invoice),
                'cus_name' => $invoice->user?->name ?? 'Customer',
                'cus_email' => $invoice->user?->email ?? '',
                'cus_phone' => $invoice->user?->phone ?? '',
                'success_url' => $this->successUrl($invoice),
                'fail_url' => $this->cancelUrl($invoice),
                'cancel_url' => $this->cancelUrl($invoice),
                'type' => 'json',
            ]);

        if ($response->failed() || $response->json('status') !== 'SUCCESS') {
            throw new PaymentException('AmarPay checkout could not be created: '.$response->body());
        }

        return new PaymentRedirect(
            url: $response->json('payment_url'),
            reference: $invoice->invoice_no,
        );
    }

    public function verify(Invoice $invoice, array $payload): bool
    {
        $response = $this->http()
            ->asForm()
            ->post($this->base().'/api/v1/trxcheck/request.php', [
                'store_id' => $this->credential('store_id'),
                'signature_key' => $this->credential('signature_key'),
                'request_id' => $payload['request_id'] ?? $invoice->invoice_no,
                'type' => 'json',
            ]);

        return $response->json('status') === 'VALID';
    }

    public function referenceFromPayload(array $payload): ?string
    {
        return $payload['request_id'] ?? $payload['tran_id'] ?? null;
    }

    public function testConnection(): array
    {
        try {
            $this->requireCredentials(['store_id', 'signature_key']);
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'Store ID or signature key is not set for the active mode.'];
        }

        $response = $this->http()
            ->asForm()
            ->timeout(15)
            ->post($this->base().'/jsonpost.php', [
                'store_id' => $this->credential('store_id'),
                'signature_key' => $this->credential('signature_key'),
                'tran_id' => 'conn_test_'.time(),
                'amount' => '1.00',
                'currency' => 'BDT',
                'desc' => 'Connection Test',
                'cus_name' => 'Test',
                'cus_email' => 'test@test.local',
                'cus_phone' => '01700000000',
                'success_url' => 'http://localhost/test',
                'fail_url' => 'http://localhost/test',
                'cancel_url' => 'http://localhost/test',
                'type' => 'json',
            ]);

        if ($response->successful()) {
            $status = $response->json('status');

            if ($status === 'SUCCESS') {
                return ['success' => true, 'message' => 'Connected · AmarPay sandbox checkout initiated.'];
            }

            $reason = $response->json('reason') ?? 'sandbox accepted the credentials (transaction declined as expected).';

            return ['success' => true, 'message' => 'Connected · '.$reason];
        }

        return [
            'success' => false,
            'message' => 'Connection failed: '.$response->body(),
        ];
    }
}
