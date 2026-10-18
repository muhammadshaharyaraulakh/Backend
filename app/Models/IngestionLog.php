<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngestionLog extends Model
{
    protected $fillable = [
        'status',
        'fetched',
        'inserted',
        'skipped',
        'failed',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];
}