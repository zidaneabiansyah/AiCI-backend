<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models with slug field
 */
trait HasSlug
{
    /**
     * Scope to find by slug
     */
    public function scopeWhereSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    /**
     * Find model by slug
     */
    public static function findBySlug(string $slug)
    {
        return static::whereSlug($slug)->first();
    }

    /**
     * Find model by slug or fail
     */
    public static function findBySlugOrFail(string $slug)
    {
        return static::whereSlug($slug)->firstOrFail();
    }

    /**
     * Get route key name (for route model binding)
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
