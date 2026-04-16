<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobHistory extends Model
{
    protected $table = 'job_history';

    protected $fillable = [
        'job_class',
        'queue',
        'payload',
        'attempts',
        'output',
        'status',
        'exception',
        'duration_seconds',
        'metadata',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'json',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
