<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'en_name',
        'biography',
        'profile_path',
        'birthday',
        'birthplace',
        'known_for',
        'tv_credits_count',
        'popularity',
        'external_ids',
        'last_synced_at',
    ];

    protected $casts = [
        'known_for' => 'array',
        'external_ids' => 'array',
        'birthday' => 'date',
        'last_synced_at' => 'datetime',
    ];
}
