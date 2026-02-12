<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controller untuk Payment
 * 
 * Endpoints:
 * - POST /payments/create/{enrollment} - Create payment & Xendit invoice
 * - GET /payments/{payment} - Show payment detail
 * - GET /payments/{payment}/check - Manual check payment status
 * - GET /payments/success/{payment} - Payment success redirect
 * - GET /payments/failed/{payment} - Payment failed redirect
 * - GET /payments/{payment}/receipt - Download receipt
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Enrollment $enrollment)
    {
        // Authorization: Only owner can create payment
        if ($enrollment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to enrollment.');
        }

        try {
            $payment = $this->paymentService->createPayment($enrollment);

            return $this->redirectWithSuccess(
                'payments.show',
                'Invoice pembayaran berhasil dibuat. Silakan lakukan pembayaran.',
                ['payment' => $payment->id]
            );

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }

    /**
     * Show payment detail & invoice
     * 
     * @param Payment $payment
     * @return \Inertia\Response
     */
    public function show(Payment $payment)
    {
        // Authorization: Only owner can view
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to payment.');
        }

        // Load relationships
        $payment->load(['enrollment.class.program', 'enrollment.schedule']);

        return Inertia::render('Payments/Show', [
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
        ]);
    }

    /**
     * Manual check payment status
     * 
     * User dapat click "Check Status" untuk sync dengan Xendit
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkStatus(Payment $payment)
    {
        // Authorization
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $payment = $this->paymentService->checkPaymentStatus($payment);

            if ($payment->status->value === 'paid') {
                return $this->redirectWithSuccess(
                    'payments.show',
                    'Pembayaran berhasil! Terima kasih.',
                    ['payment' => $payment->id]
                );
            }
            
            return $this->redirectWithSuccess(
                'payments.show',
                'Status pembayaran: ' . $payment->status->label(),
                ['payment' => $payment->id]
            );

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }

    /**
     * Payment success redirect (dari Xendit)
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Payment $payment)
    {
        // Check payment status
        try {
            $payment = $this->paymentService->checkPaymentStatus($payment);
        } catch (\Exception $e) {
            // Log error but continue to show success page
            \Illuminate\Support\Facades\Log::warning('Failed to check payment status on success page', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Pembayaran berhasil! Terima kasih.');
    }

    /**
     * Payment failed redirect (dari Xendit)
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function failed(Payment $payment)
    {
        return redirect()
            ->route('payments.show', $payment)
            ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
    }

    /**
     * Download payment receipt PDF
     * 
     * @param Payment $payment
     * @return \Illuminate\Http\Response
     */
    public function receipt(Payment $payment)
    {
        // Authorization
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate: Payment harus paid
        if ($payment->status->value !== 'paid') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'Receipt hanya tersedia untuk pembayaran yang sudah lunas.');
        }

        try {
            $pdf = $this->paymentService->generateReceiptPdf($payment);
            
            return $pdf->download("receipt-{$payment->invoice_number}.pdf");

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }
}
