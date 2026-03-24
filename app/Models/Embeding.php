<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Embeding extends Model
{
    protected $fillable = [
        'paper_id',
        'embedding',
        'origin'
    ];
    protected $casts = [
        'embedding' => 'array'
    ];

    public function document()
    {
        return $this->belongsTo(Paper::class);
    }
}
