<?php

namespace App\Providers;

use App\Cache\UpstashStore;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app['cache']->extend('upstash', function ($app) {
            $baseUrl = config('cache.stores.upstash.url');
            $token = config('cache.stores.upstash.token');
            
            return $app['cache']->repository(
                new UpstashStore($baseUrl, $token)
            );
        });
    }
}
