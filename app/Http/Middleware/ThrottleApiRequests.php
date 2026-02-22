<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottleApiRequests
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request with database-backed rate limiting.
     * Uses database cache instead of default cache to avoid Upstash limitations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use database cache for rate limiting (supports atomic operations)
        $cacheStore = Cache::store('database');
        $key = 'api_rate_limit_' . ($request->user()?->id ?: $request->ip());
        $limit = 60; // requests per minute
        
        $current = $cacheStore->get($key, 0);
        
        if ($current >= $limit) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
            ], 429);
        }
        
        // Increment counter
        if ($current === 0) {
            // First request, set expiration to 1 minute
            $cacheStore->put($key, 1, 60);
        } else {
            // Increment existing counter
            $cacheStore->increment($key);
        }
        
        $response = $next($request);
        
        // Add rate limit headers
        return $response
            ->header('X-RateLimit-Limit', $limit)
            ->header('X-RateLimit-Remaining', max(0, $limit - $current - 1))
            ->header('X-RateLimit-Reset', now()->addMinute()->timestamp);
    }
}
