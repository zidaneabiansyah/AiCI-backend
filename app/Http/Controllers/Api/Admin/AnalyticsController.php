<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\TestAttempt;
use App\Models\PlacementTest;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get overview analytics
     */
    public function overview(Request $request)
    {
        $range = $request->input('range', '6months');
        $dateFrom = $this->getDateFrom($range);

        return response()->json([
            'revenue' => $this->getRevenueAnalytics($dateFrom),
            'enrollments' => $this->getEnrollmentAnalytics($dateFrom),
            'students' => $this->getStudentAnalytics($dateFrom),
            'tests' => $this->getTestAnalytics($dateFrom),
        ]);
    }

    /**
     * Get revenue analytics
     */
    private function getRevenueAnalytics($dateFrom)
    {
        // Total revenue
        $totalRevenue = Payment::where('status', 'paid')
            ->where('paid_at', '>=', $dateFrom)
            ->sum('amount');

        // Monthly revenue trend
        $monthlyRevenue = Payment::where('status', 'paid')
            ->where('paid_at', '>=', $dateFrom)
            ->select(
                DB::raw('DATE_FORMAT(paid_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month)->format('M Y'),
                    'amount' => (float) $item->amount,
                ];
            });

        // Revenue by class
        $revenueByClass = Payment::where('status', 'paid')
            ->where('paid_at', '>=', $dateFrom)
            ->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->select(
                'classes.name as class_name',
                DB::raw('SUM(payments.amount) as amount')
            )
            ->groupBy('classes.id', 'classes.name')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($item) {
                return [
                    'class_name' => $item->class_name,
                    'amount' => (float) $item->amount,
                ];
            });

        // Revenue by payment method
        $revenueByMethod = Payment::where('status', 'paid')
            ->where('paid_at', '>=', $dateFrom)
            ->select(
                'payment_method as method',
                DB::raw('SUM(amount) as amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => ucfirst($item->method),
                    'amount' => (float) $item->amount,
                    'count' => $item->count,
                ];
            });

        return [
            'total' => (float) $totalRevenue,
            'monthly' => $monthlyRevenue,
            'by_class' => $revenueByClass,
            'by_method' => $revenueByMethod,
        ];
    }

    /**
     * Get enrollment analytics
     */
    private function getEnrollmentAnalytics($dateFrom)
    {
        // Total enrollments
        $totalEnrollments = Enrollment::where('enrolled_at', '>=', $dateFrom)->count();

        // Monthly enrollment trend
        $monthlyEnrollments = Enrollment::where('enrolled_at', '>=', $dateFrom)
            ->select(
                DB::raw('DATE_FORMAT(enrolled_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month)->format('M Y'),
                    'count' => $item->count,
                ];
            });

        // Enrollment by status
        $enrollmentsByStatus = Enrollment::where('enrolled_at', '>=', $dateFrom)
            ->select(
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count,
                ];
            });

        // Enrollment by education level
        $enrollmentsByLevel = Enrollment::where('enrolled_at', '>=', $dateFrom)
            ->join('classes', 'enrollments.class_id', '=', 'classes.id')
            ->select(
                'classes.level',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('classes.level')
            ->get()
            ->map(function ($item) {
                return [
                    'level' => $item->level,
                    'count' => $item->count,
                ];
            });

        return [
            'total' => $totalEnrollments,
            'monthly' => $monthlyEnrollments,
            'by_status' => $enrollmentsByStatus,
            'by_level' => $enrollmentsByLevel,
        ];
    }

    /**
     * Get student analytics
     */
    private function getStudentAnalytics($dateFrom)
    {
        // Total students
        $totalStudents = User::where('role', 'student')->count();

        // Active students (with confirmed enrollments)
        $activeStudents = User::where('role', 'student')
            ->whereHas('enrollments', function ($query) {
                $query->where('status', 'confirmed');
            })
            ->count();

        // Monthly student growth
        $monthlyGrowth = User::where('role', 'student')
            ->where('created_at', '>=', $dateFrom)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month)->format('M Y'),
                    'count' => $item->count,
                ];
            });

        // Students by age group (from enrollments)
        $studentsByAge = Enrollment::where('enrolled_at', '>=', $dateFrom)
            ->select(
                DB::raw('CASE 
                    WHEN student_age < 10 THEN "< 10"
                    WHEN student_age BETWEEN 10 AND 12 THEN "10-12"
                    WHEN student_age BETWEEN 13 AND 15 THEN "13-15"
                    WHEN student_age BETWEEN 16 AND 18 THEN "16-18"
                    ELSE "> 18"
                END as age_group'),
                DB::raw('COUNT(DISTINCT user_id) as count')
            )
            ->groupBy('age_group')
            ->orderByRaw('MIN(student_age)')
            ->get()
            ->map(function ($item) {
                return [
                    'age_group' => $item->age_group,
                    'count' => $item->count,
                ];
            });

        return [
            'total' => $totalStudents,
            'active' => $activeStudents,
            'monthly_growth' => $monthlyGrowth,
            'by_age_group' => $studentsByAge,
        ];
    }

    /**
     * Get test analytics
     */
    private function getTestAnalytics($dateFrom)
    {
        // Total attempts
        $totalAttempts = TestAttempt::where('started_at', '>=', $dateFrom)->count();

        // Completion rate
        $completedAttempts = TestAttempt::where('started_at', '>=', $dateFrom)
            ->where('status', 'completed')
            ->count();
        $completionRate = $totalAttempts > 0 
            ? round(($completedAttempts / $totalAttempts) * 100) 
            : 0;

        // Average score
        $averageScore = TestAttempt::where('started_at', '>=', $dateFrom)
            ->where('status', 'completed')
            ->avg('score');
        $averageScore = $averageScore ? round($averageScore) : 0;

        // Performance by test
        $performanceByTest = TestAttempt::where('started_at', '>=', $dateFrom)
            ->where('status', 'completed')
            ->join('placement_tests', 'test_attempts.test_id', '=', 'placement_tests.id')
            ->select(
                'placement_tests.title as test_name',
                DB::raw('COUNT(*) as attempts'),
                DB::raw('ROUND(AVG(test_attempts.score)) as avg_score')
            )
            ->groupBy('placement_tests.id', 'placement_tests.title')
            ->orderByDesc('attempts')
            ->get()
            ->map(function ($item) {
                return [
                    'test_name' => $item->test_name,
                    'attempts' => $item->attempts,
                    'avg_score' => (int) $item->avg_score,
                ];
            });

        // Pass/Fail distribution
        $passFailData = TestAttempt::where('started_at', '>=', $dateFrom)
            ->where('status', 'completed')
            ->join('placement_tests', 'test_attempts.test_id', '=', 'placement_tests.id')
            ->select(
                DB::raw('CASE 
                    WHEN test_attempts.score >= placement_tests.passing_score THEN "Pass"
                    ELSE "Fail"
                END as status'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                ];
            });

        return [
            'total_attempts' => $totalAttempts,
            'completion_rate' => $completionRate,
            'average_score' => $averageScore,
            'by_test' => $performanceByTest,
            'pass_fail' => $passFailData,
        ];
    }

    /**
     * Get revenue analytics only
     */
    public function revenue(Request $request)
    {
        $range = $request->input('range', '6months');
        $dateFrom = $this->getDateFrom($range);

        return response()->json($this->getRevenueAnalytics($dateFrom));
    }

    /**
     * Get enrollment analytics only
     */
    public function enrollments(Request $request)
    {
        $range = $request->input('range', '6months');
        $dateFrom = $this->getDateFrom($range);

        return response()->json($this->getEnrollmentAnalytics($dateFrom));
    }

    /**
     * Get student analytics only
     */
    public function students(Request $request)
    {
        $range = $request->input('range', '6months');
        $dateFrom = $this->getDateFrom($range);

        return response()->json($this->getStudentAnalytics($dateFrom));
    }

    /**
     * Get test analytics only
     */
    public function tests(Request $request)
    {
        $range = $request->input('range', '6months');
        $dateFrom = $this->getDateFrom($range);

        return response()->json($this->getTestAnalytics($dateFrom));
    }

    /**
     * Helper: Get date from based on range
     */
    private function getDateFrom($range)
    {
        return match ($range) {
            '1month' => Carbon::now()->subMonth(),
            '3months' => Carbon::now()->subMonths(3),
            '6months' => Carbon::now()->subMonths(6),
            '1year' => Carbon::now()->subYear(),
            'all' => Carbon::parse('2000-01-01'),
            default => Carbon::now()->subMonths(6),
        };
    }
}
