<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomEvent extends Model
{
    protected $fillable = [
        'room_id',
        'subject',
        'start',
        'end',
        'location',
        'exchange_id',
        'ews_change_key',
        'ews_item_id'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];
}
