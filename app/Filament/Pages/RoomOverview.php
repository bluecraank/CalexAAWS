<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Room;
use App\Models\RoomEvent;
use Carbon\Carbon;
use BackedEnum;

class RoomOverview extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';
    protected string $view = 'filament.pages.room-overview';
    protected static ?string $navigationLabel = 'Raumübersicht';

    public $rooms;

    public function mount()
    {
        $this->rooms = Room::with(['events' => function ($query) {
            $query->whereDate('start', today());
        }])->get();
    }

    public function getRoomStatus($room)
    {
        $now = Carbon::now();

        $event = $room->events
            ->where('start', '<=', $now)
            ->where('end', '>=', $now)
            ->first();

        if ($event) {
            return ['status' => 'belegt', 'color' => 'red'];
        }

        $next = $room->events
            ->where('start', '>', $now)
            ->sortBy('start')
            ->first();

        if ($next && $next->start->diffInMinutes($now) <= 10) {
            return ['status' => 'bald belegt', 'color' => 'yellow'];
        }

        return ['status' => 'frei', 'color' => 'green'];
    }
}