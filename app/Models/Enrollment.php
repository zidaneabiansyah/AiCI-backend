<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes, HasStatus;

    protected $fillable = [
        'enrollment_number',
        'user_id',
        'class_id',
        'class_schedule_id',
        'test_result_id',
        'status',
        'student_name',
        'student_email',
        'student_phone',
        'student_age',
        'student_grade',
        'parent_name',
        'parent_phone',
        'parent_email',
        'special_requirements',
        'notes',
        'enrolled_at',
        'confirmed_at',
        'cancelled_at',
        'completed_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'student_age' => 'integer',
        'enrolled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get class
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get schedule
     */
    public function classSchedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    /**
     * Get test result
     */
    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }

    /**
     * Get payment
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Scope confirmed enrollments
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', EnrollmentStatus::CONFIRMED->value);
    }

    /**
     * Scope pending enrollments
     */
    public function scopePending($query)
    {
        return $query->where('status', EnrollmentStatus::PENDING->value);
    }

    /**
     * Check if enrollment is paid
     */
    public function isPaid(): bool
    {
        return $this->payment && $this->payment->isPaid();
    }

    /**
     * Confirm enrollment
     */
    public function confirm(): void
    {
        $this->update([
            'status' => EnrollmentStatus::CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Cancel enrollment
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => EnrollmentStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }
}
