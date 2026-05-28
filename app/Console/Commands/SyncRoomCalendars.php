<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;
use App\Models\RoomEvent;
use App\Services\EwsCalendarService;
use Throwable;

class SyncRoomCalendars extends Command
{
    protected $signature = 'rooms:sync-calendars';
    protected $description = 'Sync Exchange calendar events for all rooms';

    public function handle(EwsCalendarService $ews)
    {
        $rooms = Room::all();

        foreach ($rooms as $room) {
            try {
                RoomEvent::where('room_id', $room->id)
                    ->whereDate('start', today())
                    ->delete();

                $events = $ews->findRoomCalendarItems(
                    room: $room,
                    startDate: now()->startOfDay()->format('Y-m-d\TH:i:s'),
                    endDate: now()->endOfDay()->format('Y-m-d\TH:i:s'),
                );

                foreach ($events as $event) {
                    RoomEvent::updateOrCreate(
                        [
                            'ews_item_id'   => $event['id'],
                            'ews_change_key' => $event['changeKey'],
                            'room_id'       => $room->id,
                            'start'         => $event['start'],
                            'end'           => $event['end'],
                        ],
                        [
                            'subject'  => $event['subject'],
                            'location' => $event['location'],
                        ]
                    );
                }

                $room->update([
                    'last_sync_at'  => now(),
                    'sync_status'   => 'ok',
                    'sync_message'  => count($events) . ' Termin(e) synchronisiert',
                ]);

                $this->info("[$room->name] OK – " . count($events) . ' Termine');

            } catch (Throwable $e) {
                $room->update([
                    'last_sync_at' => now(),
                    'sync_status'  => 'error',
                    'sync_message' => $e->getMessage(),
                ]);

                $this->error("[$room->name] Fehler: " . $e->getMessage());
            }
        }
    }
}
