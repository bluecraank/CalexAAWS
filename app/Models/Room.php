<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class Room extends Model
{

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'capacity',
        'equipment',
        'last_sync_at',
        'dashboard_token'
    ];

    protected $casts = [
        'equipment' => 'array',
        'last_sync_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($room) {
            $room->dashboard_token = Str::random(12);
        });
    }

    public function events(): HasMany
    {
        return $this->hasMany(RoomEvent::class);
    }
}
