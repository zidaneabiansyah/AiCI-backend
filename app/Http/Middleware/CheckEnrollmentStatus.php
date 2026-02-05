<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\EnrollmentStatus;

class CheckEnrollmentStatus
{
    /**
     * Handle an incoming request.
     *
     * Check if user has active enrollment before accessing certain features
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has any active enrollment
        $hasActiveEnrollment = $request->user()
            ->enrollments()
            ->whereIn('status', [
                EnrollmentStatus::CONFIRMED->value,
                EnrollmentStatus::PENDING->value
            ])
            ->exists();

        if (!$hasActiveEnrollment) {
            return redirect()
                ->route('join')
                ->with('warning', 'You need to enroll in a class first.');
        }

        return $next($request);
    }
}
