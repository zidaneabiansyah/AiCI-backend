<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\PaymentException;
use App\Models\Enrollment;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Service untuk Payment Integration dengan Xendit
 * 
 * Responsibilities:
 * 1. Create Xendit invoice
 * 2. Handle payment callback/webhook
 * 3. Verify payment status
 * 4. Generate receipt
 * 5. Handle refunds
 * 
 * Security:
 * - API keys stored in .env (NEVER commit to git)
 * - Webhook signature verification
 * - Idempotency handling
 */
class PaymentService extends BaseService
{
    /**
     * Xendit API base URL
     */
    protected string $xenditApiUrl;

    /**
     * Xendit secret key (untuk server-side API calls)
     */
    protected string $xenditSecretKey;

    /**
     * Xendit public key (untuk client-side, not used in this service)
     */
    protected string $xenditPublicKey;

    /**
     * Xendit webhook token (untuk verify webhook signature)
     */
    protected string $xenditWebhookToken;

    /**
     * Constructor - Load Xendit config dari .env
     */
    public function __construct()
    {
        $this->xenditApiUrl = config('xendit.api_url');
        $this->xenditSecretKey = config('xendit.secret_key');
        $this->xenditPublicKey = config('xendit.public_key');
        $this->xenditWebhookToken = config('xendit.webhook_token');

        // Validate config
        if (empty($this->xenditSecretKey)) {
            throw new Exception('Xendit secret key not configured. Check XENDIT_SECRET_KEY in .env');
        }
    }

    /**
     * Create payment for enrollment
     * 
     * Flow:
     * 1. Calculate total amount (class price + admin fee)
     * 2. Create payment record (status: pending)
     * 3. Create Xendit invoice
     * 4. Update payment with Xendit data
     * 5. Return payment with invoice URL
     * 
     * @param Enrollment $enrollment
     * @return Payment
     * @throws PaymentException
     */
    public function createPayment(Enrollment $enrollment): Payment
    {
        return $this->transaction(function () use ($enrollment) {
            // Validate: Enrollment harus pending
            if ($enrollment->status->value !== 'pending') {
                throw new PaymentException('Hanya enrollment dengan status pending yang dapat dibayar.');
            }

            // Validate: Belum ada payment atau payment expired/failed
            if ($enrollment->payment) {
                $existingPayment = $enrollment->payment;
                
                if ($existingPayment->status === PaymentStatus::PAID) {
                    throw new PaymentException('Enrollment ini sudah dibayar.');
                }
                
                if ($existingPayment->status === PaymentStatus::PENDING) {
                    // Return existing payment jika masih pending
                    return $existingPayment;
                }
            }

            // 1. Calculate amounts
            $classPrice = $enrollment->class->price;
            $adminFee = $this->calculateAdminFee($classPrice);
            $totalAmount = $classPrice + $adminFee;

            // 2. Generate invoice number
            $invoiceNumber = generateInvoiceNumber('INV');

            // 3. Create payment record
            $payment = Payment::create([
                'invoice_number' => $invoiceNumber,
                'enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'amount' => $classPrice,
                'admin_fee' => $adminFee,
                'total_amount' => $totalAmount,
                'currency' => 'IDR',
                'payment_method' => 'pending', // Will be updated when user selects method
                'status' => PaymentStatus::PENDING,
                'expired_at' => now()->addHours(24), // 24 hours to pay
            ]);

            // 4. Create Xendit invoice
            try {
                $xenditInvoice = $this->createXenditInvoice($payment, $enrollment);
                
                // 5. Update payment with Xendit data
                $payment->update([
                    'xendit_invoice_id' => $xenditInvoice['id'],
                    'xendit_invoice_url' => $xenditInvoice['invoice_url'],
                    'xendit_external_id' => $xenditInvoice['external_id'],
                    'xendit_response' => json_encode($xenditInvoice),
                    'expired_at' => $xenditInvoice['expiry_date'],
                ]);

                $this->log('Payment created', [
                    'payment_id' => $payment->id,
                    'invoice_number' => $invoiceNumber,
                    'xendit_invoice_id' => $xenditInvoice['id'],
                    'amount' => $totalAmount,
                ], 'info');

                // Log to payment channel
                Log::channel('payment')->info('Payment created', [
                    'payment_id' => $payment->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $totalAmount,
                    'user_id' => $enrollment->user_id,
                ]);

                // Send payment created email
                Mail::to($enrollment->student_email)
                    ->send(new \App\Mail\Payment\PaymentCreated($payment));

            } catch (Exception $e) {
                // Rollback payment if Xendit fails
                $payment->delete();
                
                $this->log('Xendit invoice creation failed', [
                    'error' => $e->getMessage(),
                    'enrollment_id' => $enrollment->id,
                ], 'error');

                throw new PaymentException('Gagal membuat invoice pembayaran. Silakan coba lagi.');
            }

            return $payment->fresh();
        });
    }

    /**
     * Create Xendit invoice via API
     * 
     * @param Payment $payment
     * @param Enrollment $enrollment
     * @return array Xendit invoice response
     * @throws Exception
     */
    protected function createXenditInvoice(Payment $payment, Enrollment $enrollment): array
    {
        $externalId = 'AICI-' . $payment->invoice_number;

        // Prepare invoice data
        $invoiceData = [
            'external_id' => $externalId,
            'amount' => $payment->total_amount,
            'description' => "Pembayaran kelas: {$enrollment->class->name}",
            'invoice_duration' => 86400, // 24 hours in seconds
            'currency' => 'IDR',
            'reminder_time' => 1, // Send reminder 1 hour before expiry
            
            // Customer info
            'payer_email' => $enrollment->student_email,
            'customer' => [
                'given_names' => $enrollment->student_name,
                'email' => $enrollment->student_email,
                'mobile_number' => $enrollment->student_phone,
            ],
            
            // Items
            'items' => [
                [
                    'name' => $enrollment->class->name,
                    'quantity' => 1,
                    'price' => $enrollment->class->price,
                    'category' => 'Education',
                ],
                [
                    'name' => 'Biaya Admin',
                    'quantity' => 1,
                    'price' => $payment->admin_fee,
                    'category' => 'Fee',
                ],
            ],
            
            // Success redirect URL
            'success_redirect_url' => route('payments.success', ['payment' => $payment->id]),
            'failure_redirect_url' => route('payments.failed', ['payment' => $payment->id]),
            
            // Webhook callback URL
            // 'callback_virtual_account_id' => route('webhooks.xendit'),
        ];

        // Call Xendit API
        $response = Http::withBasicAuth($this->xenditSecretKey, '')
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->xenditApiUrl . '/v2/invoices', $invoiceData);

        if (!$response->successful()) {
            $error = $response->json();
            
            $this->log('Xendit API error', [
                'status' => $response->status(),
                'error' => $error,
            ], 'error');

            throw new Exception('Xendit API error: ' . ($error['message'] ?? 'Unknown error'));
        }

        return $response->json();
    }

    /**
     * Calculate admin fee
     * 
     * Business rule: 2.5% dari class price, minimum Rp 5,000
     * 
     * @param float $amount
     * @return float
     */
    protected function calculateAdminFee(float $amount): float
    {
        $fee = $amount * 0.025; // 2.5%
        return max($fee, 5000); // Minimum Rp 5,000
    }

    /**
     * Handle Xendit webhook callback
     * 
     * Flow:
     * 1. Verify webhook signature (security)
     * 2. Find payment by external_id
     * 3. Update payment status
     * 4. Confirm enrollment if paid
     * 5. Send confirmation email
     * 
     * @param array $webhookData
     * @return bool
     * @throws PaymentException
     */
    public function handleWebhook(array $webhookData): bool
    {
        return $this->transaction(function () use ($webhookData) {
            // 1. Verify webhook (akan dihandle di controller dengan middleware)

            // 2. Extract data
            $externalId = $webhookData['external_id'] ?? null;
            $status = $webhookData['status'] ?? null;
            $paidAmount = $webhookData['paid_amount'] ?? 0;
            $paidAt = $webhookData['paid_at'] ?? null;

            if (!$externalId) {
                throw new PaymentException('Invalid webhook data: missing external_id');
            }

            // 3. Find payment
            $payment = Payment::where('xendit_external_id', $externalId)->first();

            if (!$payment) {
                $this->log('Payment not found for webhook', [
                    'external_id' => $externalId,
                ], 'warning');
                
                return false;
            }

            // 4. Idempotency check: Jika sudah paid, skip
            if ($payment->status === PaymentStatus::PAID) {
                $this->log('Payment already paid, skipping webhook', [
                    'payment_id' => $payment->id,
                ], 'info');
                
                return true;
            }

            // 5. Update payment based on status
            $this->updatePaymentStatus($payment, $status, $paidAmount, $paidAt);

            // 6. If paid, confirm enrollment
            if ($payment->status === PaymentStatus::PAID) {
                $enrollmentService = app(EnrollmentService::class);
                $enrollmentService->confirmEnrollment($payment->enrollment);

                $this->log('Enrollment confirmed after payment', [
                    'payment_id' => $payment->id,
                    'enrollment_id' => $payment->enrollment_id,
                ], 'info');

                // Send payment success email
                Mail::to($payment->enrollment->student_email)
                    ->send(new \App\Mail\Payment\PaymentSuccess($payment));

                // Send enrollment confirmed email
                Mail::to($payment->enrollment->student_email)
                    ->send(new \App\Mail\Enrollment\EnrollmentConfirmed($payment->enrollment));
            }

            // 7. If failed or expired, send failure email
            if (in_array($payment->status, [PaymentStatus::FAILED, PaymentStatus::EXPIRED])) {
                $reason = $payment->status === PaymentStatus::EXPIRED 
                    ? 'Invoice telah kadaluarsa. Silakan buat invoice baru.'
                    : 'Pembayaran gagal diproses.';

                Mail::to($payment->enrollment->student_email)
                    ->send(new \App\Mail\Payment\PaymentFailed($payment, $reason));
            }

            Log::channel('payment')->info('Webhook processed', [
                'payment_id' => $payment->id,
                'status' => $status,
                'external_id' => $externalId,
            ]);

            return true;
        });
    }

    /**
     * Update payment status based on Xendit status
     * 
     * Xendit status mapping:
     * - PENDING → pending
     * - PAID → paid
     * - EXPIRED → expired
     * - FAILED → failed
     * 
     * @param Payment $payment
     * @param string $xenditStatus
     * @param float $paidAmount
     * @param string|null $paidAt
     */
    protected function updatePaymentStatus(
        Payment $payment,
        string $xenditStatus,
        float $paidAmount,
        ?string $paidAt
    ): void {
        $status = match(strtoupper($xenditStatus)) {
            'PAID', 'SETTLED' => PaymentStatus::PAID,
            'EXPIRED' => PaymentStatus::EXPIRED,
            'FAILED' => PaymentStatus::FAILED,
            default => PaymentStatus::PENDING,
        };

        $updateData = [
            'status' => $status,
        ];

        if ($status === PaymentStatus::PAID) {
            $updateData['paid_at'] = $paidAt ? now()->parse($paidAt) : now();
            
            // Verify amount
            if ($paidAmount !== $payment->total_amount) {
                $this->log('Payment amount mismatch', [
                    'payment_id' => $payment->id,
                    'expected' => $payment->total_amount,
                    'received' => $paidAmount,
                ], 'warning');
            }
        }

        $payment->update($updateData);
    }

    /**
     * Check payment status from Xendit
     * 
     * Manual check untuk sync payment status
     * 
     * @param Payment $payment
     * @return Payment
     * @throws PaymentException
     */
    public function checkPaymentStatus(Payment $payment): Payment
    {
        if (!$payment->xendit_invoice_id) {
            throw new PaymentException('Payment tidak memiliki Xendit invoice ID.');
        }

        try {
            // Call Xendit API to get invoice status
            $response = Http::withBasicAuth($this->xenditSecretKey, '')
                ->get($this->xenditApiUrl . '/v2/invoices/' . $payment->xendit_invoice_id);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch invoice from Xendit');
            }

            $invoice = $response->json();

            // Update payment status
            $this->updatePaymentStatus(
                $payment,
                $invoice['status'],
                $invoice['paid_amount'] ?? 0,
                $invoice['paid_at'] ?? null
            );

            // If paid, confirm enrollment
            if ($payment->status === PaymentStatus::PAID && $payment->enrollment->status->value === 'pending') {
                $enrollmentService = app(EnrollmentService::class);
                $enrollmentService->confirmEnrollment($payment->enrollment);
            }

            return $payment->fresh();

        } catch (Exception $e) {
            $this->log('Failed to check payment status', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ], 'error');

            throw new PaymentException('Gagal memeriksa status pembayaran.');
        }
    }

    /**
     * Verify Xendit webhook signature
     * 
     * Security: Verify bahwa webhook benar-benar dari Xendit menggunakan HMAC SHA256
     * 
     * Xendit Webhook Verification:
     * 1. Get x-callback-token header (webhook verification token)
     * 2. For Invoice webhooks: compare token dengan configured token
     * 3. For other webhooks: verify HMAC signature if provided
     * 
     * @param string $webhookToken - x-callback-token header
     * @param string $signature - x-signature header (optional, for some webhook types)
     * @param string $payload - raw request body
     * @return bool
     */
    public function verifyWebhookSignature(string $webhookToken, string $signature, string $payload): bool
    {
        // 1. Check if webhook token is configured
        if (empty($this->xenditWebhookToken)) {
            $this->log('Xendit webhook token not configured', [], 'error');
            return false;
        }

        // 2. Verify callback token (primary verification for Invoice webhooks)
        if ($webhookToken !== $this->xenditWebhookToken) {
            $this->log('Invalid webhook token', [
                'expected_prefix' => substr($this->xenditWebhookToken, 0, 10) . '...',
                'received_prefix' => substr($webhookToken, 0, 10) . '...',
            ], 'warning');
            return false;
        }

        // 3. Additional signature verification if provided (for enhanced security)
        // Some Xendit webhooks include x-signature header with HMAC
        if (!empty($signature)) {
            // Compute HMAC SHA256 signature
            $computedSignature = hash_hmac('sha256', $payload, $this->xenditWebhookToken);
            
            if (!hash_equals($computedSignature, $signature)) {
                $this->log('Invalid webhook HMAC signature', [
                    'computed_prefix' => substr($computedSignature, 0, 10) . '...',
                    'received_prefix' => substr($signature, 0, 10) . '...',
                ], 'warning');
                return false;
            }
        }

        // Verification passed
        return true;
    }

    /**
     * Get payment by invoice number
     * 
     * @param string $invoiceNumber
     * @return Payment|null
     */
    public function getPaymentByInvoiceNumber(string $invoiceNumber): ?Payment
    {
        return Payment::where('invoice_number', $invoiceNumber)
            ->with(['enrollment.class', 'user'])
            ->first();
    }

    /**
     * Generate payment receipt (simple text receipt)
     * 
     * TODO: Implement PDF generation dengan library seperti DomPDF
     * 
     * @param Payment $payment
     * @return array
     */
    public function generateReceipt(Payment $payment): array
    {
        if ($payment->status !== PaymentStatus::PAID) {
            throw new PaymentException('Hanya payment yang sudah dibayar yang dapat generate receipt.');
        }

        return [
            'invoice_number' => $payment->invoice_number,
            'payment_date' => $payment->paid_at->format('d F Y H:i'),
            'student_name' => $payment->enrollment->student_name,
            'class_name' => $payment->enrollment->class->name,
            'amount' => formatCurrency($payment->amount),
            'admin_fee' => formatCurrency($payment->admin_fee),
            'total_amount' => formatCurrency($payment->total_amount),
            'payment_method' => $payment->payment_method,
            'status' => 'LUNAS',
        ];
    }
}
