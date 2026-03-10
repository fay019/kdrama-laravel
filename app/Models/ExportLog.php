<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    protected $fillable = [
        'user_id',
        'format',
        'item_count',
        'file_size',
        'cache_hash',
        'was_cached',
        'generation_time',
        'filters',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'item_count' => 'integer',
        'file_size' => 'integer',
        'was_cached' => 'boolean',
        'generation_time' => 'integer',
        'filters' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
