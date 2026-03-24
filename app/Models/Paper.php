<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    protected $fillable = ['document', 'status', 'raw_text'];
    public function embedings()
    {
        return $this->hasMany(Embeding::class);
    }
}
