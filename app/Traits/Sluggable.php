<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait for auto-generating slugs from title/name
 */
trait Sluggable
{
    /**
     * Boot the sluggable trait
     */
    protected static function bootSluggable(): void
    {
        static::creating(function ($model) {
            if ($model->slug === null || $model->slug === '') {
                $model->slug = $model->generateSlug();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->getSlugSource()) && ($model->slug === null || $model->slug === '')) {
                $model->slug = $model->generateSlug();
            }
        });
    }

    /**
     * Generate unique slug
     */
    protected function generateSlug(): string
    {
        $source = $this->{$this->getSlugSource()};
        $slug = Str::slug($source);
        $originalSlug = $slug;
        $count = 1;

        // Ensure uniqueness
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }

        return $query->exists();
    }

    /**
     * Get the source field for slug generation
     */
    protected function getSlugSource(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'title';
    }
}
