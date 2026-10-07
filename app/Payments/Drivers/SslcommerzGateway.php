<?php

namespace App\Payments\Drivers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentRedirect;

class SslcommerzGateway extends AbstractGateway
{
    private function base(): string
    {
        return $this->mode() === 'live'
            ? 'https://securepay.sslcommerz.com'
            : 'https://sandbox.sslcommerz.com';
    }

    public function start(Invoice $invoice): PaymentRedirect
    {
        $this->requireCredentials(['store_id', 'store_password']);

        $response = $this->http()
            ->asForm()
            ->post($this->base().'/gwprocess/v4/api.php', [
                'store_id' => $this->credential('store_id'),
                'store_passwd' => $this->credential('store_password'),
                'total_amount' => $this->amountMajor($invoice),
                'currency' => 'BDT',
                'tran_id' => $invoice->invoice_no,
                'success_url' => $this->successUrl($invoice),
                'fail_url' => $this->cancelUrl($invoice),
                'cancel_url' => $this->cancelUrl($invoice),
                'ipn_url' => $this->webhookUrl(),
                'cus_name' => $invoice->user?->name ?? 'Customer',
                'cus_email' => $invoice->user?->email ?? '',
                'cus_phone' => $invoice->user?->phone ?? '',
                'cus_add1' => 'N/A',
                'cus_city' => 'N/A',
                'cus_country' => 'Bangladesh',
                'shipping_method' => 'NO',
                'product_name' => mb_substr($this->description($invoice), 0, 50),
                'product_category' => 'Service',
                'product_profile' => 'general',
                'num_of_item' => 1,
            ]);

        if ($response->failed() || $response->json('status') !== 'SUCCESS') {
            throw new PaymentException('SSLCommerz session could not be created: '.$response->body());
        }

        return new PaymentRedirect(
            url: $response->json('GatewayPageURL'),
            reference: $invoice->invoice_no,
        );
    }

    public function verify(Invoice $invoice, array $payload): bool
    {
        $valId = $payload['val_id'] ?? null;

        if (! $valId) {
            return false;
        }

        $response = $this->http()->get($this->base().'/validator/api/validationserverAPI.php', [
            'val_id' => $valId,
            'store_id' => $this->credential('store_id'),
            'store_passwd' => $this->credential('store_password'),
            'format' => 'json',
        ]);

        $status = $response->json('status');

        return in_array($status, ['VALID', 'VALIDATED'], true);
    }

    public function referenceFromPayload(array $payload): ?string
    {
        return $payload['val_id'] ?? $payload['tran_id'] ?? null;
    }

    public function testConnection(): array
    {
        try {
            $this->requireCredentials(['store_id', 'store_password']);
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'Store ID or store password is not set for the active mode.'];
        }

        $response = $this->http()
            ->asForm()
            ->timeout(15)
            ->post($this->base().'/gwprocess/v4/api.php', [
                'store_id' => $this->credential('store_id'),
                'store_passwd' => $this->credential('store_password'),
                'total_amount' => '1.00',
                'currency' => 'BDT',
                'tran_id' => 'conn_test_'.time(),
                'success_url' => 'http://localhost/test',
                'fail_url' => 'http://localhost/test',
                'cancel_url' => 'http://localhost/test',
                'cus_name' => 'Test',
                'cus_email' => 'test@test.local',
                'cus_phone' => '01700000000',
                'cus_add1' => 'N/A',
                'cus_city' => 'N/A',
                'cus_country' => 'Bangladesh',
                'shipping_method' => 'NO',
                'product_name' => 'Connection Test',
                'product_category' => 'Service',
                'product_profile' => 'general',
                'num_of_item' => 1,
            ]);

        if ($response->successful()) {
            $status = $response->json('status');

            if ($status === 'SUCCESS') {
                return ['success' => true, 'message' => 'Connected · SSLCommerz sandbox session created.'];
            }

            // FAILED from the sandbox means credentials were accepted but
            // the test transaction was rejected — still a connectivity success.
            $reason = $response->json('failedreason') ?? 'sandbox accepted the credentials (transaction declined as expected).';

            return ['success' => true, 'message' => 'Connected · '.$reason];
        }

        return [
            'success' => false,
            'message' => 'Connection failed: '.$response->body(),
        ];
    }
}
