<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Judgment extends Model
{
    use HasNeighbors;

    protected $fillable = [
        'citation', 'title', 'court_id', 'year',
        'subject', 'judges', 'full_text', 'summary',
        'source_url', 'ingested_at',
    ];

    protected $casts = [
        'judges'      => 'array',
        'embedding'   => Vector::class,
        'ingested_at' => 'datetime',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}