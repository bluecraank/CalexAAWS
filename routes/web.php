<?php

use Illuminate\Support\Facades\Route;
use App\Services\EwsCalendarService;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomDashboardController;
use App\Models\Room;
use App\Models\RoomEvent;

Route::get('/room-book/{token}', function ($token) {

    $room = Room::where('dashboard_token', $token)->firstOrFail();

    $duration = (int) request('duration', 30);

    $start = now();
    $end = now()->addMinutes($duration);

    $conflict = $room->events()
        ->where(function ($q) use ($start, $end) {
            $q->whereBetween('start', [$start, $end])
                ->orWhereBetween('end', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('start', '<=', $start)
                        ->where('end', '>=', $end);
                });
        })
        ->exists();

    if ($conflict) {
        return response()->json([
            'success' => false,
            'message' => 'Raum ist bereits belegt'
        ]);
    }

    $ews = new EwsCalendarService();

    $ews->createEntry($room, $start, $end);

    RoomEvent::create([
        'room_id' => $room->id,
        'start' => $start,
        'subject' => 'Ad-hoc Meeting',
        'end' => $end
    ]);

    return response()->json([
        'success' => true
    ]);
});

Route::get('/room-end/{token}', function ($token) {

    $room = Room::where('dashboard_token', $token)->firstOrFail();

    $ews = new EwsCalendarService();

    $success = $ews->deleteEntry($room);

    $event = $room->events()
        ->where('start','<=',now())
        ->where('end','>=',now())
        ->delete();

    if(!$event){
        return response()->json(['success'=>false]);
    }

    return response()->json(['success'=>true]);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/room-dashboard/{token}', [RoomDashboardController::class, 'show']);

Route::resource('rooms', RoomController::class);
