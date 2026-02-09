<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\CancelEnrollmentRequest;
use App\Http\Requests\Enrollment\CreateEnrollmentRequest;
use App\Mail\Enrollment\EnrollmentCancelled;
use App\Mail\Enrollment\EnrollmentCreated;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

/**
 * Controller untuk Enrollment
 * 
 * Endpoints:
 * - GET /enrollments - List user's enrollments
 * - GET /enrollments/create/{class} - Show enrollment form
 * - POST /enrollments - Create enrollment
 * - GET /enrollments/{enrollment} - Show enrollment detail
 * - POST /enrollments/{enrollment}/cancel - Cancel enrollment
 */
class EnrollmentController extends BaseController
{
    /**
     * Constructor - inject service
     */
    public function __construct(
        protected EnrollmentService $enrollmentService
    ) {}

    /**
     * Display user's enrollments
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $enrollments = $this->enrollmentService->getUserEnrollments(
            auth()->user(),
            $status
        );

        return Inertia::render('Enrollments/Index', [
            'enrollments' => $enrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'enrollment_number' => $enrollment->enrollment_number,
                    'status' => $enrollment->status->value,
                    'status_label' => $enrollment->status->label(),
                    'student_name' => $enrollment->student_name,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'enrolled_at_formatted' => formatDateTime($enrollment->enrolled_at),
                    'class' => [
                        'id' => $enrollment->class->id,
                        'name' => $enrollment->class->name,
                        'slug' => $enrollment->class->slug,
                        'level' => $enrollment->class->level,
                        'price' => $enrollment->class->price,
                        'price_formatted' => formatCurrency($enrollment->class->price),
                        'program_name' => $enrollment->class->program->name,
                    ],
                    'schedule' => $enrollment->schedule ? [
                        'batch_name' => $enrollment->schedule->batch_name,
                        'start_date' => formatDate($enrollment->schedule->start_date),
                    ] : null,
                    'payment' => $enrollment->payment ? [
                        'status' => $enrollment->payment->status->value,
                        'status_label' => $enrollment->payment->status->label(),
                        'total_amount' => $enrollment->payment->total_amount,
                        'total_amount_formatted' => formatCurrency($enrollment->payment->total_amount),
                    ] : null,
                ];
            }),
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show enrollment form
     * 
     * @param ClassModel $class
     * @return \Inertia\Response
     */
    public function create(ClassModel $class)
    {
        // Check if user can enroll
        $canEnroll = $this->enrollmentService->canEnroll(auth()->user(), $class);
        
        if (!$canEnroll['can_enroll']) {
            return redirect()
                ->route('classes.show', $class)
                ->with('error', $canEnroll['reason']);
        }

        // Load schedules
        $class->load([
            'program',
            'schedules' => function ($query) {
                $query->available()->orderBy('start_date');
            }
        ]);

        // Get user's latest test result (if any)
        $testResult = auth()->user()
            ->testAttempts()
            ->completed()
            ->with('result')
            ->latest()
            ->first()
            ?->result;

        return Inertia::render('Enrollments/Create', [
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'code' => $class->code,
                'level' => $class->level,
                'price' => $class->price,
                'price_formatted' => formatCurrency($class->price),
                'min_age' => $class->min_age,
                'max_age' => $class->max_age,
                'min_score' => $class->min_score,
                'program_name' => $class->program->name,
            ],
            'schedules' => $class->schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'batch_name' => $schedule->batch_name,
                    'start_date' => formatDate($schedule->start_date),
                    'end_date' => formatDate($schedule->end_date),
                    'day_of_week' => $schedule->day_of_week,
                    'time' => $schedule->start_time . ' - ' . $schedule->end_time,
                    'location' => $schedule->location,
                    'remaining_slots' => $schedule->getRemainingSlots(),
                ];
            }),
            'testResult' => $testResult ? [
                'id' => $testResult->id,
                'overall_score' => $testResult->overall_score,
                'level_achieved' => $testResult->level_achieved,
            ] : null,
            'user' => [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ]);
    }

    /**
     * Store enrollment
     * 
     * @param CreateEnrollmentRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateEnrollmentRequest $request)
    {
        try {
            $class = ClassModel::findOrFail($request->class_id);

            $enrollment = $this->enrollmentService->createEnrollment(
                auth()->user(),
                $class,
                $request->validated()
            );

            // Send enrollment created email
            Mail::to($enrollment->student_email)
                ->send(new EnrollmentCreated($enrollment));

            return $this->redirectWithSuccess(
                'enrollments.show',
                'Pendaftaran berhasil! Silakan lakukan pembayaran.',
                ['enrollment' => $enrollment->id]
            );

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }

    /**
     * Show enrollment detail
     * 
     * @param Enrollment $enrollment
     * @return \Inertia\Response
     */
    public function show(Enrollment $enrollment)
    {
        // Authorization: Only owner can view
        if ($enrollment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to enrollment.');
        }

        // Load relationships
        $enrollment->load(['class.program', 'schedule', 'payment']);

        return Inertia::render('Enrollments/Show', [
            'enrollment' => [
                'id' => $enrollment->id,
                'enrollment_number' => $enrollment->enrollment_number,
                'status' => $enrollment->status->value,
                'status_label' => $enrollment->status->label(),
                'student_name' => $enrollment->student_name,
                'student_email' => $enrollment->student_email,
                'student_phone' => $enrollment->student_phone,
                'student_age' => $enrollment->student_age,
                'student_grade' => $enrollment->student_grade,
                'parent_name' => $enrollment->parent_name,
                'parent_phone' => $enrollment->parent_phone,
                'parent_email' => $enrollment->parent_email,
                'special_requirements' => $enrollment->special_requirements,
                'notes' => $enrollment->notes,
                'enrolled_at' => formatDateTime($enrollment->enrolled_at),
                'confirmed_at' => $enrollment->confirmed_at ? formatDateTime($enrollment->confirmed_at) : null,
                'cancelled_at' => $enrollment->cancelled_at ? formatDateTime($enrollment->cancelled_at) : null,
                'cancellation_reason' => $enrollment->cancellation_reason,
            ],
            'class' => [
                'id' => $enrollment->class->id,
                'name' => $enrollment->class->name,
                'slug' => $enrollment->class->slug,
                'code' => $enrollment->class->code,
                'level' => $enrollment->class->level,
                'price' => $enrollment->class->price,
                'price_formatted' => formatCurrency($enrollment->class->price),
                'duration_hours' => $enrollment->class->duration_hours,
                'program_name' => $enrollment->class->program->name,
            ],
            'schedule' => $enrollment->schedule ? [
                'batch_name' => $enrollment->schedule->batch_name,
                'start_date' => formatDate($enrollment->schedule->start_date),
                'end_date' => formatDate($enrollment->schedule->end_date),
                'day_of_week' => $enrollment->schedule->day_of_week,
                'time' => $enrollment->schedule->start_time . ' - ' . $enrollment->schedule->end_time,
                'location' => $enrollment->schedule->location,
            ] : null,
            'payment' => $enrollment->payment ? [
                'id' => $enrollment->payment->id,
                'invoice_number' => $enrollment->payment->invoice_number,
                'status' => $enrollment->payment->status->value,
                'status_label' => $enrollment->payment->status->label(),
                'amount' => $enrollment->payment->amount,
                'admin_fee' => $enrollment->payment->admin_fee,
                'total_amount' => $enrollment->payment->total_amount,
                'total_amount_formatted' => formatCurrency($enrollment->payment->total_amount),
                'payment_method' => $enrollment->payment->payment_method,
                'xendit_invoice_url' => $enrollment->payment->xendit_invoice_url,
                'expired_at' => $enrollment->payment->expired_at ? formatDateTime($enrollment->payment->expired_at) : null,
            ] : null,
        ]);
    }

    /**
     * Cancel enrollment
     * 
     * @param CancelEnrollmentRequest $request
     * @param Enrollment $enrollment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(CancelEnrollmentRequest $request, Enrollment $enrollment)
    {
        // Authorization: Only owner can cancel
        if ($enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->enrollmentService->cancelEnrollment(
                $enrollment,
                $request->cancellation_reason
            );

            // Send enrollment cancelled email
            Mail::to($enrollment->student_email)
                ->send(new EnrollmentCancelled($enrollment, $request->cancellation_reason));

            return $this->redirectWithSuccess(
                'enrollments.show',
                'Pendaftaran berhasil dibatalkan.',
                ['enrollment' => $enrollment->id]
            );

        } catch (\Exception $e) {
            return $this->backWithError($e->getMessage());
        }
    }
}
