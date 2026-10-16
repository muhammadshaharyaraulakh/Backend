<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'hierarchy_level',
    ];
     public function judgments()
    {
        return $this->hasMany(Judgment::class);
    }
}
