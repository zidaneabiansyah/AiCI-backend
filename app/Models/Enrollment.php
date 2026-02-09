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
        'data_anonymized_at',
        'scheduled_deletion_at',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'student_age' => 'integer',
        'enrolled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'data_anonymized_at' => 'datetime',
        'scheduled_deletion_at' => 'datetime',
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
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => EnrollmentStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * ============================================
     * DATA MASKING ACCESSORS
     * ============================================
     * 
     * Accessor methods untuk protect PII (Personally Identifiable Information)
     * Digunakan di admin panel untuk conditional masking based on user role
     */

    /**
     * Get masked student email
     * Admin: john.doe@example.com
     * Non-admin: j***@example.com
     */
    public function getMaskedStudentEmailAttribute(): string
    {
        if (!shouldMaskData()) {
            return $this->student_email ?? '-';
        }
        
        return maskEmail($this->student_email);
    }

    /**
     * Get masked student phone
     * Admin: 081234567890
     * Non-admin: 0812****7890
     */
    public function getMaskedStudentPhoneAttribute(): string
    {
        if (!shouldMaskData()) {
            return $this->student_phone ?? '-';
        }
        
        return maskPhone($this->student_phone);
    }

    /**
     * Get masked student name
     * Admin: John Doe
     * Non-admin: John D***
     */
    public function getMaskedStudentNameAttribute(): string
    {
        if (!shouldMaskData()) {
            return $this->student_name ?? '-';
        }
        
        return maskName($this->student_name);
    }

    /**
     * Get masked parent name
     * Admin: Jane Doe
     * Non-admin: Jane D***
     */
    public function getMaskedParentNameAttribute(): string
    {
        if (!shouldMaskData()) {
            return $this->parent_name ?? '-';
        }
        
        return maskName($this->parent_name);
    }

    /**
     * Get masked parent phone
     * Admin: 081234567890
     * Non-admin: 0812****7890
     */
    public function getMaskedParentPhoneAttribute(): string
    {
        if (!shouldMaskData()) {
            return $this->parent_phone ?? '-';
        }
        
        return maskPhone($this->parent_phone);
    }

    /**
     * Get masked parent email
     * Admin: parent@example.com
     * Non-admin: p***@example.com
     */
    public function getMaskedParentEmailAttribute(): string
    {
        if (!shouldMaskData()) {
            return $this->parent_email ?? '-';
        }
        
        return maskEmail($this->parent_email);
    }
}
