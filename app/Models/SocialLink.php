<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = ['platform', 'url', 'icon', 'order', 'is_visible'];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
