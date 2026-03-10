<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'attachment_size',
        'email_sent',
        'error_message',
        'status',
        'read_at',
        'resolved_at',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
