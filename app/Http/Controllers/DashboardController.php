<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends BaseController
{
    /**
     * Display the user's dashboard with their tests and enrollments
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get recent test attempts
        $testAttempts = TestAttempt::where('user_id', $user->id)
            ->with(['placementTest', 'result'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'test_title' => $attempt->placementTest->title,
                    'status' => $attempt->status->value,
                    'score' => $attempt->score,
                    'level_result' => $attempt->level_result,
                    'completed_at' => $attempt->completed_at ? formatDateTime($attempt->completed_at) : null,
                ];
            });

        // Get active enrollments
        $enrollments = Enrollment::where('user_id', $user->id)
            ->with(['class.program', 'payment'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'enrollment_number' => $enrollment->enrollment_number,
                    'status' => $enrollment->status->value,
                    'status_label' => $enrollment->status->label(),
                    'enrolled_at' => formatDateTime($enrollment->enrolled_at),
                    'class_name' => $enrollment->class->name,
                    'program_name' => $enrollment->class->program->name,
                    'payment' => $enrollment->payment ? [
                        'id' => $enrollment->payment->id,
                        'status' => $enrollment->payment->status->value,
                        'total_amount' => formatCurrency($enrollment->payment->total_amount),
                    ] : null,
                ];
            });

        return Inertia::render('Dashboard', [
            'testAttempts' => $testAttempts,
            'enrollments' => $enrollments,
        ]);
    }
}
