<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Program;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_students' => User::where('role', 'public')->count(),
            'total_enrollments' => Enrollment::count(),
            'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'total_programs' => Program::count(),
            'total_test_attempts' => TestAttempt::count(),
        ];

        $recent_enrollments = Enrollment::with(['user', 'class'])
            ->latest()
            ->take(5)
            ->get();

        $recent_payments = Payment::with(['user', 'enrollment'])
            ->where('status', 'paid')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_enrollments' => $recent_enrollments,
            'recent_payments' => $recent_payments,
        ]);
    }
}
