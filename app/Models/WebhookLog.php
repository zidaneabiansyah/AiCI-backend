<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WebhookLog Model
 * 
 * Store all incoming webhook requests for security audit and debugging
 * 
 * Security Features:
 * - Track all webhook attempts
 * - Detect replay attacks by checking duplicate external_id
 * - Monitor suspicious IP addresses
 * - Audit trail for compliance
 */
class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'event_type',
        'external_id',
        'payload',
        'headers',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Scope successful webhooks
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope invalid webhooks (security threats)
     */
    public function scopeInvalid($query)
    {
        return $query->where('status', 'invalid');
    }

    /**
     * Scope by source
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Check if this external_id was already processed successfully
     * (Detect replay attacks)
     * 
     * Security Logic:
     * - Check if external_id already processed successfully
     * - Ignore failed attempts (allow retry for legitimate failures)
     * - Consider recent attempts only (last 24 hours) to prevent false positives
     * 
     * @param string $externalId
     * @param string $source
     * @return bool
     */
    public static function isReplayAttack(string $externalId, string $source): bool
    {
        return static::where('external_id', $externalId)
            ->where('source', $source)
            ->where('status', 'success')
            ->where('created_at', '>=', now()->subHours(24)) // Check last 24 hours only
            ->exists();
    }

    /**
     * Get recent failed attempts from same IP
     * (Detect brute force attacks)
     * 
     * @param string $ipAddress
     * @param int $minutes
     * @return int
     */
    public static function getRecentFailedAttempts(string $ipAddress, int $minutes = 5): int
    {
        return static::where('ip_address', $ipAddress)
            ->whereIn('status', ['failed', 'invalid'])
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}
