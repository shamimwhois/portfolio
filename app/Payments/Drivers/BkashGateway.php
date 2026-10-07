<?php

namespace App\Payments\Drivers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentRedirect;

class BkashGateway extends AbstractGateway
{
    private function base(): string
    {
        return $this->mode() === 'live'
            ? 'https://tokenized.pay.bka.sh/v1.2.0-beta'
            : 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
    }

    /**
     * Obtain a merchant id_token for the tokenized checkout flow.
     */
    private function token(): string
    {
        $appKey = $this->credential('app_key');
        $appSecret = $this->credential('app_secret');

        $response = $this->http()
            ->withHeaders([
                'X-APP-Key' => $appKey,
                'X-APP-Secret' => $appSecret,
            ])
            ->post($this->base().'/tokenized/checkout/token/create', [
                'app_key' => $appKey,
                'app_secret' => $appSecret,
            ]);

        if ($response->failed() || ! $response->json('id_token')) {
            throw new PaymentException('bKash token could not be obtained: '.$response->body());
        }

        return $response->json('id_token');
    }

    public function start(Invoice $invoice): PaymentRedirect
    {
        $this->requireCredentials(['app_key', 'app_secret']);

        $token = $this->token();

        $response = $this->http()
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->credential('app_key'),
            ])
            ->post($this->base().'/tokenized/checkout/create', [
                'mode' => '0011',
                'payerReference' => '01',
                'callbackURL' => $this->callbackUrl($invoice),
                'amount' => $this->amountMajor($invoice),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $invoice->invoice_no,
            ]);

        if ($response->failed() || ! $response->json('bkashURL')) {
            throw new PaymentException('bKash checkout could not be created: '.$response->body());
        }

        return new PaymentRedirect(
            url: $response->json('bkashURL'),
            reference: $response->json('paymentID'),
        );
    }

    public function verify(Invoice $invoice, array $payload): bool
    {
        // bKash callback: status=success + paymentID → confirm via execute.
        if (($payload['status'] ?? '') !== 'success' || empty($payload['paymentID'])) {
            return false;
        }

        $response = $this->http()
            ->withHeaders([
                'Authorization' => $this->token(),
                'X-APP-Key' => $this->credential('app_key'),
            ])
            ->post($this->base().'/tokenized/checkout/execute', [
                'paymentID' => $payload['paymentID'],
            ]);

        return ! $response->failed() && $response->json('transactionStatus') === 'Completed';
    }

    public function referenceFromPayload(array $payload): ?string
    {
        return $payload['paymentID'] ?? null;
    }

    public function testConnection(): array
    {
        try {
            $this->requireCredentials(['app_key', 'app_secret']);
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'App key or app secret is not set for the active mode.'];
        }

        try {
            $this->token();

            return [
                'success' => true,
                'message' => 'Connected · bKash merchant token obtained successfully.',
            ];
        } catch (PaymentException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ];
        }
    }
}
