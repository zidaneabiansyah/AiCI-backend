<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'address',
        'email',
        'phone',
        'whatsapp',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'facebook_url',
    ];
}
