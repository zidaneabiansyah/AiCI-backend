<?php

namespace App\Traits;

/**
 * Trait for models with status field
 */
trait HasStatus
{
    /**
     * Scope to filter by status
     */
    public function scopeWhereStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by multiple statuses
     */
    public function scopeWhereStatusIn($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * Check if status matches
     */
    public function hasStatus($status): bool
    {
        return $this->status === $status || $this->status->value === $status;
    }

    /**
     * Update status
     */
    public function updateStatus($status): bool
    {
        return $this->update(['status' => $status]);
    }
}
