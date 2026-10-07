<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Payments\AbstractGateway;
use App\Payments\PaymentException;
use App\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service) {}

    /**
     * Customer returns from the gateway after paying (or being redirected).
     */
    public function success(Request $request, string $invoiceNo): View
    {
        $invoice = $this->invoice($invoiceNo);

        abort_unless($this->validToken($request, $invoice), 403);

        if ($invoice->isPending()) {
            try {
                $this->service->complete($invoice, $request->all());
            } catch (PaymentException $e) {
                Log::warning('Payment verification failed', ['invoice' => $invoiceNo, 'error' => $e->getMessage()]);
            }
        }

        return view('payment.status', [
            'invoice' => $invoice,
            'status' => $invoice->isPaid() ? 'success' : ($invoice->status === 'cancelled' ? 'cancelled' : 'pending'),
        ]);
    }

    /**
     * Customer cancelled at the gateway (or was sent to the cancel URL).
     */
    public function cancel(Request $request, string $invoiceNo): View
    {
        $invoice = $this->invoice($invoiceNo);

        abort_unless($this->validToken($request, $invoice), 403);

        $this->service->cancel($invoice);

        return view('payment.status', [
            'invoice' => $invoice,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Server-to-server callback from a gateway (bKash execute flow, etc.).
     */
    public function callback(Request $request, string $invoiceNo, string $gateway): JsonResponse
    {
        $invoice = $this->invoice($invoiceNo);

        abort_unless($this->validToken($request, $invoice), 403);

        if ($invoice->gateway !== $gateway && $invoice->payment_method !== $gateway) {
            return response()->json(['error' => 'Gateway mismatch'], 400);
        }

        try {
            $this->service->complete($invoice, $request->all());
        } catch (PaymentException $e) {
            Log::warning('Payment callback failed', ['invoice' => $invoiceNo, 'gateway' => $gateway, 'error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['status' => $invoice->status]);
    }

    /**
     * Webhook endpoint (Stripe signed events, SSLCommerz IPN, ...).
     */
    public function webhook(Request $request, string $gateway): JsonResponse
    {
        $payload = $request->getContent();

        try {
            $manager = app(\App\Payments\GatewayManager::class);

            if (! $manager->has($gateway)) {
                return response()->json(['error' => 'Unknown gateway'], 404);
            }

            $driver = $manager->driver($gateway);

            $signature = $request->header($driver->webhookSignatureHeader(), '');

            if (! $driver->verifyWebhookSignature($payload, $signature)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $data = json_decode($payload, true) ?: [];
            $invoiceNo = $driver->invoiceNoFromWebhook($data);

            if ($invoiceNo) {
                $invoice = Invoice::query()->where('invoice_no', $invoiceNo)->first();

                if ($invoice && ! $invoice->isPaid()) {
                    $this->service->complete($invoice, $data);
                }
            }

            return response()->json(['received' => true]);
        } catch (PaymentException $e) {
            Log::warning('Payment webhook failed', ['gateway' => $gateway, 'error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function invoice(string $invoiceNo): Invoice
    {
        return Invoice::query()->where('invoice_no', $invoiceNo)->firstOrFail();
    }

    private function validToken(Request $request, Invoice $invoice): bool
    {
        $provided = (string) $request->query('token', '');

        return $provided !== '' && hash_equals(AbstractGateway::signature($invoice), $provided);
    }
}
