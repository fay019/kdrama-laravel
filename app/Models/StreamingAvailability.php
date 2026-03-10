<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreamingAvailability extends Model
{
    protected $fillable = [
        'tmdb_id',
        'type',
        'region',
        'data',
        'last_updated_at'
    ];

    protected $casts = [
        'data' => 'array',
        'last_updated_at' => 'datetime'
    ];
}
