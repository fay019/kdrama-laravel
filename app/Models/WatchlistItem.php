<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchlistItem extends Model
{
    const CREATED_AT = 'added_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = ['user_id', 'tmdb_id', 'is_in_watchlist', 'is_watching', 'is_watched', 'notes', 'rating'];

    protected $casts = [
        'is_in_watchlist' => 'boolean',
        'is_watching' => 'boolean',
        'is_watched' => 'boolean',
        'tmdb_id' => 'integer',
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kdrama()
    {
        return $this->belongsTo(Kdrama::class, 'tmdb_id', 'tmdb_id');
    }
}
