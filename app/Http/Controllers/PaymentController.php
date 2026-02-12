<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Controller untuk Payment (API Version)
 */
class PaymentController extends BaseController
{
    /**
     * Constructor - inject service
     */
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create payment for enrollment
     * 
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function create(Enrollment $enrollment): JsonResponse
    {
        // Authorization: Only owner can create payment
        if ($enrollment->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized access to enrollment.', null, 403);
        }

        try {
            $payment = $this->paymentService->createPayment($enrollment);

            return $this->successResponse([
                'payment_id' => $payment->id,
                'xendit_invoice_url' => $payment->xendit_invoice_url
            ], 'Invoice pembayaran berhasil dibuat.');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Show payment detail & invoice
     * 
     * @param Payment $payment
     * @return JsonResponse
     */
    public function show(Payment $payment): JsonResponse
    {
        // Authorization: Only owner can view
        if ($payment->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized access to payment.', null, 403);
        }

        // Load relationships
        $payment->load(['enrollment.class.program', 'enrollment.schedule']);

        return $this->successResponse([
            'payment' => [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice_number,
                'status' => $payment->status->value,
                'status_label' => $payment->status->label(),
                'status_color' => $payment->status->color(),
                'amount' => $payment->amount,
                'amount_formatted' => formatCurrency($payment->amount),
                'admin_fee' => $payment->admin_fee,
                'admin_fee_formatted' => formatCurrency($payment->admin_fee),
                'total_amount' => $payment->total_amount,
                'total_amount_formatted' => formatCurrency($payment->total_amount),
                'payment_method' => $payment->payment_method,
                'xendit_invoice_url' => $payment->xendit_invoice_url,
                'expired_at' => $payment->expired_at,
                'expired_at_formatted' => $payment->expired_at ? formatDateTime($payment->expired_at) : null,
                'paid_at' => $payment->paid_at,
                'paid_at_formatted' => $payment->paid_at ? formatDateTime($payment->paid_at) : null,
                'created_at_formatted' => formatDateTime($payment->created_at),
            ],
            'enrollment' => [
                'id' => $payment->enrollment->id,
                'enrollment_number' => $payment->enrollment->enrollment_number,
                'student_name' => $payment->enrollment->student_name,
                'student_email' => $payment->enrollment->student_email,
            ],
            'courseClass' => [
                'name' => $payment->enrollment->class->name,
                'code' => $payment->enrollment->class->code,
                'program_name' => $payment->enrollment->class->program->name,
            ],
            'schedule' => $payment->enrollment->schedule ? [
                'batch_name' => $payment->enrollment->schedule->batch_name,
                'start_date' => formatDate($payment->enrollment->schedule->start_date),
            ] : null,
        ], 'Payment details retrieved');
    }

    /**
     * Manual check payment status
     * 
     * @param Payment $payment
     * @return JsonResponse
     */
    public function checkStatus(Payment $payment): JsonResponse
    {
        // Authorization
        if ($payment->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        try {
            $payment = $this->paymentService->checkPaymentStatus($payment);

            return $this->successResponse([
                'status' => $payment->status->value,
                'status_label' => $payment->status->label()
            ], 'Status pembayaran diperbarui.');

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Payment success redirect (dari Xendit)
     * 
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function success(Payment $payment): RedirectResponse
    {
        // Check payment status
        try {
            $payment = $this->paymentService->checkPaymentStatus($payment);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to check payment status on success page', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return redirect()->away("{$frontendUrl}/payments/success?payment_id={$payment->id}");
    }

    /**
     * Payment failed redirect (dari Xendit)
     * 
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function failed(Payment $payment): RedirectResponse
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return redirect()->away("{$frontendUrl}/payments/failed?payment_id={$payment->id}");
    }

    /**
     * Download payment receipt PDF
     * 
     * @param Payment $payment
     * @return mixed
     */
    public function receipt(Payment $payment): mixed
    {
        // Authorization
        if ($payment->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        // Validate: Payment harus paid
        if ($payment->status->value !== 'paid') {
            return $this->errorResponse('Receipt hanya tersedia untuk pembayaran yang sudah lunas.');
        }

        try {
            $pdf = $this->paymentService->generateReceiptPdf($payment);
            
            return $pdf->download("receipt-{$payment->invoice_number}.pdf");

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
