<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $fillable = [
        'refresh_token',
        'user_id',
        'expires_at'
    ];
    protected $casts = ['expires_at' => 'datetime'];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
