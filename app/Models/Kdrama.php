<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kdrama extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'en_name',
        'original_name',
        'overview',
        'poster_path',
        'backdrop_path',
        'first_air_date',
        'vote_average',
        'vote_count',
        'genres',
        'origin_country',
        'status',
        'original_language',
        'number_of_episodes',
        'number_of_seasons',
        'last_air_date',
        'credits',
        'production_companies',
        'networks',
        'similar',
        'translations',
        'adult_only',
        'last_updated_at',
    ];

    protected $casts = [
        'genres' => 'array',
        'origin_country' => 'array',
        'credits' => 'array',
        'production_companies' => 'array',
        'networks' => 'array',
        'similar' => 'array',
        'translations' => 'array',
        'adult_only' => 'boolean',
        'last_updated_at' => 'datetime',
        'first_air_date' => 'date',
        'last_air_date' => 'date',
    ];

    /**
     * @deprecated Utiliser directement tmdb_id
     */
    public function getInternalId()
    {
        return null;
    }

    /**
     * @deprecated Utiliser directement tmdb_id
     */
    public function ensureInternalContent()
    {
        return 0;
    }
}
