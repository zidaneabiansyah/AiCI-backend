<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Log;

/**
 * IsAdmin Middleware
 * 
 * Security middleware untuk protect admin-only routes
 * Hanya user dengan role ADMIN yang bisa akses
 * 
 * Usage:
 * - Applied to Filament admin panel
 * - Applied to admin-only routes
 * - Checks user role after authentication
 * - Logs unauthorized access attempts
 */
class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has admin role
        if (!$request->user()->isAdmin()) {
            // Log unauthorized access attempt
            Log::channel('activity')->warning('Unauthorized admin access attempt', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}

