<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * Log important user activities for audit trail
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if ($request->user()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log the activity
     */
    protected function logActivity(Request $request, Response $response): void
    {
        // Only log specific routes or methods
        $shouldLog = $this->shouldLogRequest($request);

        if ($shouldLog) {
            Log::channel('activity')->info('User Activity', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }

    /**
     * Determine if request should be logged
     */
    protected function shouldLogRequest(Request $request): bool
    {
        // Log POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // Log specific GET routes (e.g., admin pages, sensitive data)
        $logRoutes = [
            'admin.*',
            'enrollment.*',
            'payment.*',
        ];

        foreach ($logRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
