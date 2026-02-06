<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HasStatus;

    protected $fillable = [
        'invoice_number',
        'enrollment_id',
        'user_id',
        'amount',
        'admin_fee',
        'total_amount',
        'currency',
        'payment_method',
        'payment_channel',
        'status',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'xendit_external_id',
        'xendit_response',
        'account_number',
        'paid_at',
        'expired_at',
        'payment_proof',
        'refunded_at',
        'refund_amount',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'refunded_at' => 'datetime',
        'status' => PaymentStatus::class,
    ];

    /**
     * Get enrollment
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', PaymentStatus::PAID->value);
    }

    /**
     * Scope pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING->value);
    }

    /**
     * Scope payments this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    /**
     * Check if payment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
        ]);
    }
}
