<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'role',
        'quote',
        'photo',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];
}
