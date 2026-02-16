<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'website_url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
