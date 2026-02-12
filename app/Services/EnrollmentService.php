<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Exceptions\BusinessException;
use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\User;
use Exception;

/**
 * Service untuk mengelola Enrollment
 * 
 * Responsibilities:
 * 1. Validate enrollment eligibility
 * 2. Create enrollment record
 * 3. Manage capacity
 * 4. Handle enrollment lifecycle (confirm, cancel, complete)
 * 5. Send notifications
 */
class EnrollmentService extends BaseService
{
    /**
     * Create new enrollment
     * 
     * Flow:
     * 1. Validate class availability
     * 2. Check prerequisites
     * 3. Check age requirement
     * 4. Check capacity
     * 5. Generate enrollment number
     * 6. Create enrollment record
     * 7. Update capacity counters
     * 8. Send confirmation email
     * 
     * @param User $user
     * @param ClassModel $class
     * @param array $data
     * @return Enrollment
     * @throws BusinessException
     */
    public function createEnrollment(
        User $user,
        ClassModel $class,
        array $data
    ): Enrollment {
        return $this->transaction(function () use ($user, $class, $data) {
            // 1. Validate class is active
            if (!$class->is_active) {
                throw new BusinessException('Kelas tidak tersedia saat ini.');
            }

            // 2. Check prerequisites
            $this->validatePrerequisites($class, $data);

            // 3. Check age requirement
            $this->validateAgeRequirement($class, $data['student_age']);

            // 4. Check capacity
            $schedule = null;
            if (isset($data['class_schedule_id'])) {
                $schedule = ClassSchedule::findOrFail($data['class_schedule_id']);
                
                if (!$schedule->hasAvailableSlots()) {
                    throw new BusinessException('Jadwal kelas sudah penuh.');
                }
            } else {
                if (!$class->hasAvailableSlots()) {
                    throw new BusinessException('Kelas sudah penuh.');
                }
            }

            // 5. Check duplicate enrollment with lock to prevent race condition
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                ->where('class_id', $class->id)
                ->whereIn('status', [
                    EnrollmentStatus::PENDING->value,
                    EnrollmentStatus::CONFIRMED->value,
                ])
                ->lockForUpdate()
                ->exists();

            if ($existingEnrollment) {
                throw new BusinessException('Anda sudah terdaftar di kelas ini.');
            }

            // 6. Generate enrollment number
            $enrollmentNumber = $this->generateEnrollmentNumber();

            // 7. Create enrollment
            $enrollment = Enrollment::create([
                'enrollment_number' => $enrollmentNumber,
                'user_id' => $user->id,
                'class_id' => $class->id,
                'class_schedule_id' => $data['class_schedule_id'] ?? null,
                'test_result_id' => $data['test_result_id'] ?? null,
                'status' => EnrollmentStatus::PENDING,
                'student_name' => $data['student_name'],
                'student_email' => $data['student_email'],
                'student_phone' => $data['student_phone'],
                'student_age' => $data['student_age'],
                'student_grade' => $data['student_grade'] ?? null,
                'parent_name' => $data['parent_name'] ?? null,
                'parent_phone' => $data['parent_phone'] ?? null,
                'parent_email' => $data['parent_email'] ?? null,
                'special_requirements' => $data['special_requirements'] ?? null,
                'notes' => $data['notes'] ?? null,
                'enrolled_at' => now(),
            ]);

            // 8. Update capacity counters
            $class->incrementEnrolled();
            if ($schedule) {
                $schedule->incrementEnrolled();
            }

            $this->log('Enrollment created', [
                'enrollment_id' => $enrollment->id,
                'enrollment_number' => $enrollmentNumber,
                'user_id' => $user->id,
                'class_id' => $class->id,
            ]);

            // 9. Send confirmation email (akan dihandle di controller/event)

            return $enrollment;
        });
    }

    /**
     * Validate prerequisites
     * 
     * Check if user meets class prerequisites:
     * - Minimum score from placement test
     * - Previous class completion
     * 
     * @param ClassModel $class
     * @param array $data
     * @throws BusinessException
     */
    protected function validatePrerequisites(ClassModel $class, array $data): void
    {
        // Check minimum score requirement
        if ($class->min_score > 0) {
            // If user has test result, check score
            if (isset($data['test_result_id'])) {
                $testResult = \App\Models\TestResult::find($data['test_result_id']);
                
                if (!$testResult || $testResult->overall_score < $class->min_score) {
                    throw new BusinessException(
                        "Skor placement test Anda ({$testResult->overall_score}) belum memenuhi syarat minimum ({$class->min_score})."
                    );
                }
            } else {
                // No test result, but class requires minimum score
                throw new BusinessException(
                    'Kelas ini memerlukan placement test. Silakan ikuti placement test terlebih dahulu.'
                );
            }
        }

        // Check other prerequisites (e.g., previous class completion)
        // Note: Prerequisites are currently informational only
        // Future: Implement strict prerequisite checking
        if ($class->prerequisites !== null && $class->prerequisites !== '') {
            $this->log('Class has prerequisites (informational only)', [
                'class_id' => $class->id,
                'prerequisites' => $class->prerequisites,
            ], 'info');
        }
    }

    /**
     * Validate age requirement
     * 
     * @param ClassModel $class
     * @param int $studentAge
     * @throws BusinessException
     */
    protected function validateAgeRequirement(ClassModel $class, int $studentAge): void
    {
        if ($class->min_age && $studentAge < $class->min_age) {
            throw new BusinessException(
                "Usia minimal untuk kelas ini adalah {$class->min_age} tahun."
            );
        }

        if ($class->max_age && $studentAge > $class->max_age) {
            throw new BusinessException(
                "Usia maksimal untuk kelas ini adalah {$class->max_age} tahun."
            );
        }
    }

    /**
     * Generate unique enrollment number
     * 
     * Format: ENR-YYYYMMDD-XXXXXX
     * Example: ENR-20260205-000001
     * 
     * @return string
     */
    protected function generateEnrollmentNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "ENR-{$date}-";

        // Get last enrollment number for today
        $lastEnrollment = Enrollment::where('enrollment_number', 'like', $prefix . '%')
            ->orderBy('enrollment_number', 'desc')
            ->first();

        if ($lastEnrollment) {
            // Extract sequence number and increment
            $lastSequence = (int) substr($lastEnrollment->enrollment_number, -6);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad($newSequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Confirm enrollment
     * 
     * Called after payment is successful
     * 
     * @param Enrollment $enrollment
     * @return Enrollment
     * @throws BusinessException
     */
    public function confirmEnrollment(Enrollment $enrollment): Enrollment
    {
        return $this->transaction(function () use ($enrollment) {
            if ($enrollment->status !== EnrollmentStatus::PENDING) {
                throw new BusinessException('Enrollment tidak dalam status pending.');
            }

            $enrollment->confirm();
            $enrollment->class->incrementEnrolled();

            $this->log('Enrollment confirmed', [
                'enrollment_id' => $enrollment->id,
                'enrollment_number' => $enrollment->enrollment_number,
            ]);

            // Send confirmation email (akan dihandle di event)

            return $enrollment;
        });
    }

    /**
     * Cancel enrollment
     * 
     * Flow:
     * 1. Validate can be cancelled
     * 2. Update status
     * 3. Decrement capacity counters
     * 4. Handle refund (if applicable)
     * 5. Send cancellation email
     * 
     * @param Enrollment $enrollment
     * @param string $reason
     * @return Enrollment
     * @throws BusinessException
     */
    public function cancelEnrollment(Enrollment $enrollment, string $reason): Enrollment
    {
        return $this->transaction(function () use ($enrollment, $reason) {
            // Validate: Cannot cancel if already completed
            if ($enrollment->status === EnrollmentStatus::COMPLETED) {
                throw new BusinessException('Enrollment yang sudah selesai tidak dapat dibatalkan.');
            }

            // Validate: Cannot cancel if already cancelled
            if ($enrollment->status === EnrollmentStatus::CANCELLED) {
                throw new BusinessException('Enrollment sudah dibatalkan sebelumnya.');
            }

            // Cancel enrollment
            $enrollment->cancel($reason);

            // Decrement capacity counters
            $enrollment->class->decrementEnrolled();
            if ($enrollment->schedule) {
                $enrollment->schedule->decrementEnrolled();
            }

            $this->log('Enrollment cancelled', [
                'enrollment_id' => $enrollment->id,
                'enrollment_number' => $enrollment->enrollment_number,
                'reason' => $reason,
            ]);

            // Handle refund if payment exists and is paid
            if ($enrollment->payment && $enrollment->payment->isPaid()) {
                $paymentService = app(PaymentService::class);
                $paymentService->processRefund($enrollment->payment, "Pembatalan Enrollment: {$reason}");
                
                $this->log('Refund processed automatically during cancellation', [
                    'enrollment_id' => $enrollment->id,
                    'payment_id' => $enrollment->payment->id,
                    'amount' => $enrollment->payment->refund_amount,
                ]);
            }

            // Send cancellation email (akan dihandle di event)

            return $enrollment;
        });
    }

    /**
     * Complete enrollment
     * 
     * Called when class is finished
     * 
     * @param Enrollment $enrollment
     * @return Enrollment
     * @throws BusinessException
     */
    public function completeEnrollment(Enrollment $enrollment): Enrollment
    {
        return $this->transaction(function () use ($enrollment) {
            if ($enrollment->status !== EnrollmentStatus::CONFIRMED) {
                throw new BusinessException('Hanya enrollment yang confirmed yang dapat diselesaikan.');
            }

            $enrollment->update([
                'status' => EnrollmentStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $this->log('Enrollment completed', [
                'enrollment_id' => $enrollment->id,
                'enrollment_number' => $enrollment->enrollment_number,
            ]);

            // Send completion certificate email (akan dihandle di event)

            return $enrollment;
        });
    }

    /**
     * Get user's enrollments
     * 
     * @param User $user
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserEnrollments(User $user, ?string $status = null)
    {
        $query = Enrollment::where('user_id', $user->id)
            ->with(['class.program', 'schedule', 'payment'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Check if user can enroll in class
     * 
     * @param User $user
     * @param ClassModel $class
     * @return array ['can_enroll' => bool, 'reason' => string|null]
     */
    public function canEnroll(User $user, ClassModel $class): array
    {
        // Check if class is active
        if (!$class->is_active) {
            return [
                'can_enroll' => false,
                'reason' => 'Kelas tidak tersedia saat ini.',
            ];
        }

        // Check capacity
        if (!$class->hasAvailableSlots()) {
            return [
                'can_enroll' => false,
                'reason' => 'Kelas sudah penuh.',
            ];
        }

        // Check existing enrollment
        $existingEnrollment = Enrollment::where('user_id', $user->id)
            ->where('class_id', $class->id)
            ->whereIn('status', [
                EnrollmentStatus::PENDING->value,
                EnrollmentStatus::CONFIRMED->value,
            ])
            ->exists();

        if ($existingEnrollment) {
            return [
                'can_enroll' => false,
                'reason' => 'Anda sudah terdaftar di kelas ini.',
            ];
        }

        return [
            'can_enroll' => true,
            'reason' => null,
        ];
    }
}
