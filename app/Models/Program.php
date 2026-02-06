<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasSlug;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes, Sluggable, HasSlug, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'education_level',
        'description',
        'objectives',
        'image',
        'min_age',
        'max_age',
        'duration_weeks',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'objectives' => 'array',
        'is_active' => 'boolean',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'duration_weeks' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $slugSource = 'name';

    /**
     * Get classes for this program
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get updater
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope active programs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope with active classes
     */
    public function scopeWithActiveClasses($query)
    {
        return $query->whereHas('classes', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope by education level
     */
    public function scopeForEducationLevel($query, string $level)
    {
        return $query->where('education_level', $level);
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
