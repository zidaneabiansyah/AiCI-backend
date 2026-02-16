<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = [
        'name',
        'position',
        'role_type',
        'photo',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected $appends = ['role_type_display'];

    public function getRoleTypeDisplayAttribute(): string
    {
        return match($this->role_type) {
            'OPERASIONAL' => 'Operasional',
            'TUTOR' => 'Tutor',
            default => $this->role_type,
        };
    }
}
