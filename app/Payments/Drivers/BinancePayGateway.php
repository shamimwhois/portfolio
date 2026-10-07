<?php

namespace App\Payments\Drivers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentRedirect;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

/**
 * Binance Pay — B2B merchant API (Crypto Gateway).
 *
 * Official docs: https://developers.binance.com/docs/binance-pay
 *
 * - Sandbox: https://bpay-pre.binanceapi.com
 * - Live:    https://bpay.binanceapi.com
 *
 * Every request is signed with the merchant secret key:
 *   signature = strtoupper(HMAC-SHA512(secret, timestamp . "\n" . nonce . "\n" . body . "\n"))
 * sent via the BinancePay-Timestamp / BinancePay-Nonce / BinancePay-Certificate-SN /
 * BinancePay-Signature headers. Webhooks carry the same signature headers and are
 * verified over the raw body.
 */
class BinancePayGateway extends AbstractGateway
{
    private const BASE = 'https://bpay.binanceapi.com';

    private const BASE_SANDBOX = 'https://bpay-pre.binanceapi.com';

    /**
     * Create a hosted checkout order (tradeType WEB) and redirect the customer
     * to the checkoutUrl returned by the API.
     */
    public function start(Invoice $invoice): PaymentRedirect
    {
        $this->requireCredentials(['merchant_id', 'api_key', 'secret_key']);

        $body = json_encode([
            'merchantId' => $this->credential('merchant_id'),
            'merchantTradeNo' => $invoice->invoice_no,
            'totalFee' => $this->amountMajor($invoice),
            // B2B settles in USDT, matching the manual Binance P2P wallet method.
            'currency' => 'USDT',
            'productType' => 'Other',
            'productName' => mb_substr($this->description($invoice), 0, 120),
            'productDetail' => 'Invoice '.$invoice->invoice_no,
            'returnUrl' => $this->successUrl($invoice),
            'cancelUrl' => $this->cancelUrl($invoice),
            'webhookUrl' => $this->webhookUrl(),
            'tradeType' => 'WEB',
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->signedPost('/binancepay/openapi/v2/order', $body);

        if ($response->failed() || $response->json('status') !== 'SUCCESS') {
            throw new PaymentException('Binance Pay order could not be created: '.$this->errorMessage($response));
        }

        $checkoutUrl = $response->json('data.checkoutUrl');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new PaymentException('Binance Pay order was created but no checkout URL was returned.');
        }

        return new PaymentRedirect(
            url: $checkoutUrl,
            reference: $response->json('data.prepayId'),
        );
    }

    /**
     * Query the order server-side; only a PAID status confirms the payment.
     */
    public function verify(Invoice $invoice, array $payload): bool
    {
        $body = json_encode([
            'merchantTradeNo' => $invoice->invoice_no,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->signedPost('/binancepay/openapi/v2/query-order', $body);

        return ! $response->failed()
            && $response->json('status') === 'SUCCESS'
            && $response->json('data.status') === 'PAID';
    }

    public function referenceFromPayload(array $payload): ?string
    {
        return $payload['prepayId'] ?? $payload['data']['prepayId'] ?? null;
    }

    /**
     * Ping the API with a signed query for a non-existent order. A business
     * error (e.g. "order not found") proves the credentials were accepted;
     * only signature/auth failures reject the connection.
     */
    public function testConnection(): array
    {
        try {
            $this->requireCredentials(['merchant_id', 'api_key', 'secret_key']);
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'Merchant ID, API key and secret are not all set for the active mode.'];
        }

        try {
            $body = json_encode(['merchantTradeNo' => 'CONNECTION_TEST'], JSON_UNESCAPED_SLASHES);
            $response = $this->signedPost('/binancepay/openapi/v2/query-order', $body);

            if ($response->failed()) {
                return ['success' => false, 'message' => 'Connection failed: '.$this->errorMessage($response)];
            }

            $code = (string) ($response->json('code') ?? '');

            // 400001/400002/400003 are signature & auth errors — credentials wrong.
            if (in_array($code, ['400001', '400002', '400003'], true)) {
                return ['success' => false, 'message' => 'Connection failed: '.$this->errorMessage($response)];
            }

            return [
                'success' => true,
                'message' => 'Connected · Binance Pay API accepted the credentials.',
            ];
        } catch (PaymentException $e) {
            return ['success' => false, 'message' => 'Connection failed: '.$e->getMessage()];
        }
    }

    /**
     * Webhooks are signed over the raw body: signature = HMAC-SHA512(body, secret).
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = $this->credential('secret_key');

        if (! $secret || ! $signature) {
            return false;
        }

        $computed = strtoupper(hash_hmac('sha512', $payload, $secret));

        return hash_equals($computed, strtoupper($signature));
    }

    public function invoiceNoFromWebhook(array $payload): ?string
    {
        return $payload['data']['merchantTradeNo']
            ?? $payload['merchantTradeNo']
            ?? null;
    }

    public function webhookSignatureHeader(): string
    {
        return 'BinancePay-Signature';
    }

    private function baseUrl(): string
    {
        return $this->mode() === 'live' ? self::BASE : self::BASE_SANDBOX;
    }

    /**
     * Signed POST — the exact JSON string is hashed, so it must be the string
     * actually sent (JSON_UNESCAPED_SLASHES keeps URLs intact).
     */
    private function signedPost(string $path, string $body): Response
    {
        $timestamp = (string) (round(microtime(true) * 1000));
        $nonce = (string) Str::uuid();

        $payload = $timestamp."\n".$nonce."\n".$body."\n";
        $signature = strtoupper(hash_hmac('sha512', $payload, $this->credential('secret_key')));

        return $this->http()
            ->withHeaders([
                'BinancePay-Timestamp' => $timestamp,
                'BinancePay-Nonce' => $nonce,
                'BinancePay-Certificate-SN' => $this->credential('api_key'),
                'BinancePay-Signature' => $signature,
            ])
            ->withBody($body, 'application/json')
            ->post($this->baseUrl().$path);
    }

    private function errorMessage(Response $response): string
    {
        return $response->json('message')
            ?? $response->json('data.message')
            ?? $response->body();
    }
}
