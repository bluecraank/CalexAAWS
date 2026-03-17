<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;
use App\Models\RoomEvent;
use App\Services\EwsCalendarService;
use Carbon\Carbon;

class SyncRoomCalendars extends Command
{
    protected $signature = 'rooms:sync-calendars';
    protected $description = 'Sync Exchange calendar events for all rooms';

    public function handle(EwsCalendarService $ews)
    {
        $start = now()->startOfDay()->format('Y-m-d\TH:i:s');
        $end = now()->endOfDay()->format('Y-m-d\TH:i:s');

        $rooms = Room::all();

        foreach ($rooms as $room) {
            RoomEvent::where('room_id', $room->id)
                ->whereDate('start', today())
                ->delete();

            $events = $ews->findRoomCalendarItems(
                room: $room,
                startDate: now()->startOfDay()->format('Y-m-d\TH:i:s'),
                endDate: now()->endOfDay()->format('Y-m-d\TH:i:s'),
            );

            if ($events) {
                $room->update(['last_sync_at' => now()]);
            }

            foreach ($events as $event) {
                RoomEvent::updateOrCreate(
                    [
                        'ews_item_id' => $event['id'],
                        'ews_change_key' => $event['changeKey'],
                        'room_id' => $room->id,
                        'start' => $event['start'],
                        'end' => $event['end'],
                    ],
                    [
                        'subject' => $event['subject'],
                        'location' => $event['location'],
                    ]
                );
            }
        }
    }
}
