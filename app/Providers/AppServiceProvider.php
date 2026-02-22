<?php

namespace App\Providers;

use App\Cache\UpstashStore;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Register custom Upstash cache driver
        $this->app['cache']->extend('upstash', function ($app) {
            $baseUrl = config('cache.stores.upstash.url');
            $token = config('cache.stores.upstash.token');
            
            return $app['cache']->repository(
                new UpstashStore($baseUrl, $token)
            );
        });
        
        // Define API rate limiter with database store to avoid Upstash limitations
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
