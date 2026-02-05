<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    /**
     * Execute database transaction
     *
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    protected function transaction(callable $callback): mixed
    {
        try {
            return DB::transaction($callback);
        } catch (Exception $e) {
            Log::error('Transaction failed: ' . $e->getMessage(), [
                'service' => static::class,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Log service activity
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        Log::$level($message, array_merge([
            'service' => static::class,
        ], $context));
    }

    /**
     * Handle service exception
     */
    protected function handleException(Exception $e, string $context = ''): void
    {
        $this->log(
            "Exception in {$context}: " . $e->getMessage(),
            [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ],
            'error'
        );
    }
}
