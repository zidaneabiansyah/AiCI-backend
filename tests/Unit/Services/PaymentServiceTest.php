<?php

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->paymentService = app(PaymentService::class);
    
    $this->user = User::factory()->create();
    
    $program = Program::create([
        'name' => 'Test Program',
        'education_level' => 'sd_mi',
        'description' => 'Test description',
        'min_age' => 6,
        'max_age' => 12,
        'duration_weeks' => 8,
        'is_active' => true,
    ]);
    
    $class = ClassModel::create([
        'program_id' => $program->id,
        'code' => 'TEST-001',
        'name' => 'Test Class',
        'level' => 'beginner',
        'price' => 1000000,
        'capacity' => 20,
        'is_active' => true,
    ]);
    
    $this->enrollment = Enrollment::create([
        'user_id' => $this->user->id,
        'class_id' => $class->id,
        'enrollment_number' => 'ENR-TEST-001',
        'student_name' => 'Test Student',
        'student_email' => 'test@example.com',
        'student_phone' => '08123456789',
        'student_age' => 10,
        'parent_name' => 'Test Parent',
        'parent_phone' => '08123456789',
        'status' => EnrollmentStatus::PENDING,
    ]);
});

test('it can create payment', function () {
    Http::fake([
        'https://api.xendit.co/v2/invoices' => Http::response([
            'id' => 'test-invoice-id',
            'external_id' => 'PAY-TEST-001',
            'invoice_url' => 'https://checkout.xendit.co/test',
            'expiry_date' => now()->addDays(1)->toISOString(),
            'status' => 'PENDING',
        ], 200),
    ]);

    $payment = $this->paymentService->createPayment($this->enrollment);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->enrollment_id)->toBe($this->enrollment->id)
        ->and($payment->status)->toBe(PaymentStatus::PENDING)
        ->and($payment->invoice_number)->not->toBeNull()
        ->and($payment->xendit_invoice_id)->not->toBeNull();
});

test('it calculates correct payment amount', function () {
    Http::fake([
        'https://api.xendit.co/v2/invoices' => Http::response([
            'id' => 'test-invoice-id',
            'external_id' => 'PAY-TEST-001',
            'invoice_url' => 'https://checkout.xendit.co/test',
            'expiry_date' => now()->addDays(1)->toISOString(),
            'status' => 'PENDING',
        ], 200),
    ]);

    $payment = $this->paymentService->createPayment($this->enrollment);

    $expectedAmount = $this->enrollment->class->price;
    $expectedAdminFee = max($expectedAmount * 0.025, 5000); // 2.5% with min 5000
    $expectedTotal = $expectedAmount + $expectedAdminFee;

    expect((float) $payment->amount)->toBe((float) $expectedAmount)
        ->and((float) $payment->admin_fee)->toBe((float) $expectedAdminFee)
        ->and((float) $payment->total_amount)->toBe((float) $expectedTotal);
});

test('it generates unique invoice numbers', function () {
    Http::fake([
        'https://api.xendit.co/v2/invoices' => Http::sequence()
            ->push([
                'id' => 'test-invoice-id-1',
                'external_id' => 'PAY-TEST-001',
                'invoice_url' => 'https://checkout.xendit.co/test1',
                'expiry_date' => now()->addDays(1)->toISOString(),
                'status' => 'PENDING',
            ], 200)
            ->push([
                'id' => 'test-invoice-id-2',
                'external_id' => 'PAY-TEST-002',
                'invoice_url' => 'https://checkout.xendit.co/test2',
                'expiry_date' => now()->addDays(1)->toISOString(),
                'status' => 'PENDING',
            ], 200),
    ]);

    $payment1 = $this->paymentService->createPayment($this->enrollment);
    
    $user2 = User::factory()->create();
    $enrollment2 = Enrollment::create([
        'user_id' => $user2->id,
        'class_id' => $this->enrollment->class_id,
        'enrollment_number' => 'ENR-TEST-002',
        'student_name' => 'Test Student 2',
        'student_email' => 'test2@example.com',
        'student_phone' => '08123456789',
        'student_age' => 10,
        'parent_name' => 'Test Parent',
        'parent_phone' => '08123456789',
        'status' => EnrollmentStatus::PENDING,
    ]);

    $payment2 = $this->paymentService->createPayment($enrollment2);

    expect($payment1->invoice_number)->not->toBe($payment2->invoice_number);
});

test('it can handle successful webhook', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $payment = Payment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'xendit_external_id' => 'AICI-INV-TEST-001',
        'status' => PaymentStatus::PENDING,
        'total_amount' => 1025000,
    ]);

    $webhookData = [
        'external_id' => 'AICI-INV-TEST-001',
        'status' => 'PAID',
        'paid_amount' => 1025000,
        'paid_at' => now()->toISOString(),
    ];

    $result = $this->paymentService->handleWebhook($webhookData);

    expect($result)->toBeTrue();
    expect($payment->fresh()->status)->toBe(PaymentStatus::PAID);
    expect($this->enrollment->fresh()->status)->toBe(EnrollmentStatus::CONFIRMED);
    
    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\Payment\PaymentSuccess::class);
});

test('it can handle expired payment webhook', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $payment = Payment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'xendit_external_id' => 'AICI-INV-TEST-001',
        'status' => PaymentStatus::PENDING,
    ]);

    $webhookData = [
        'external_id' => 'AICI-INV-TEST-001',
        'status' => 'EXPIRED',
    ];

    $result = $this->paymentService->handleWebhook($webhookData);

    expect($result)->toBeTrue();
    expect($payment->fresh()->status)->toBe(PaymentStatus::EXPIRED);
    
    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\Payment\PaymentFailed::class);
});

test('it handles duplicate webhook calls (idempotency)', function () {
    $payment = Payment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'xendit_external_id' => 'AICI-INV-TEST-001',
        'status' => PaymentStatus::PAID,
    ]);

    $webhookData = [
        'external_id' => 'AICI-INV-TEST-001',
        'status' => 'PAID',
    ];

    $result = $this->paymentService->handleWebhook($webhookData);

    expect($result)->toBeTrue();
    // Verify it didn't try to process again (e.g., status remains PAID)
    expect($payment->fresh()->status)->toBe(PaymentStatus::PAID);
});

test('it can check payment status from xendit', function () {
    $payment = Payment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'xendit_invoice_id' => 'test-invoice-id',
        'status' => PaymentStatus::PENDING,
        'total_amount' => 1025000,
    ]);

    Http::fake([
        'https://api.xendit.co/v2/invoices/test-invoice-id' => Http::response([
            'id' => 'test-invoice-id',
            'status' => 'PAID',
            'paid_amount' => 1025000,
            'paid_at' => now()->toISOString(),
        ], 200),
    ]);

    $updatedPayment = $this->paymentService->checkPaymentStatus($payment);

    expect($updatedPayment->status)->toBe(PaymentStatus::PAID);
    expect($this->enrollment->fresh()->status)->toBe(EnrollmentStatus::CONFIRMED);
});

test('it can verify valid callback token', function () {
    config(['xendit.webhook_token' => 'valid-token']);
    
    // Re-instantiate service to pick up config OR just trust the constructor logic
    $service = new PaymentService(); 
    
    $isValid = $service->verifyWebhookSignature('valid-token', '', '');
    expect($isValid)->toBeTrue();

    $isInvalid = $service->verifyWebhookSignature('invalid-token', '', '');
    expect($isInvalid)->toBeFalse();
});

test('it can verify valid hmac signature', function () {
    $token = 'test-token';
    config(['xendit.webhook_token' => $token]);
    $service = new PaymentService();
    
    $payload = json_encode(['data' => 'test']);
    $signature = hash_hmac('sha256', $payload, $token);
    
    $isValid = $service->verifyWebhookSignature($token, $signature, $payload);
    expect($isValid)->toBeTrue();

    $isInvalid = $service->verifyWebhookSignature($token, 'wrong-sig', $payload);
    expect($isInvalid)->toBeFalse();
});

test('it can generate receipt', function () {
    $payment = Payment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'status' => PaymentStatus::PAID,
        'amount' => 1000000,
        'admin_fee' => 25000,
        'total_amount' => 1025000,
        'paid_at' => now(),
    ]);

    $receipt = $this->paymentService->generateReceiptPdf($payment);

    expect($receipt)->toBeInstanceOf(\Barryvdh\DomPDF\PDF::class);
});
