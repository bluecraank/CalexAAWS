<?php

use Illuminate\Support\Facades\Route;
use App\Services\EwsCalendarService;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomDashboardController;
use App\Models\Room;
use App\Models\RoomEvent;
use App\Models\Setting;

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

    $ews->deleteEntry($room);

    $event = $room->events()
        ->where('start','<=',now())
        ->where('end','>=',now())
        ->delete();

    if(!$event){
        return response()->json(['success'=>false]);
    }

    return response()->json(['success'=>true]);
});

Route::get('/room-status/{token}', function ($token) {
    $room = Room::where('dashboard_token', $token)
        ->with('events')
        ->firstOrFail();

    $warningThreshold = (int) Setting::get('warning_threshold', 15);
    $events = $room->events->sortBy('start');

    $current = $events->first(fn($e) => now()->between($e->start, $e->end));
    $next    = $events->filter(fn($e) => $e->start > now())->first();
    $minutes = $next ? (int) ceil(now()->diffInSeconds($next->start) / 60) : null;

    if ($current) {
        $status = 'busy';
    } elseif ($next && $minutes <= $warningThreshold) {
        $status = 'warning';
    } else {
        $status = 'free';
    }

    if ($current) {
        $remaining = (int) floor(now()->diffInSeconds($current->end) / 60);
        if ($remaining < 1) {
            $nextText = 'Endet in weniger als 1 Minute';
        } elseif ($remaining <= 60) {
            $nextText = "Noch {$remaining} Minuten belegt";
        } else {
            $nextText = 'Noch ' . (int)($remaining / 60) . ' Std. ' . ($remaining % 60) . ' Min. belegt';
        }
    } elseif ($minutes !== null) {
        if ($minutes <= 1) {
            $nextText = 'Nächstes Meeting in weniger als 1 Minute';
        } elseif ($minutes <= 60) {
            $nextText = "Nächstes Meeting in {$minutes} Minuten";
        } elseif ($minutes <= 119) {
            $nextText = "Für {$minutes} Minuten noch frei";
        } else {
            $nextText = 'Für ' . (int) ($minutes / 60) . ' Stunden noch frei';
        }
    } else {
        $nextText = null;
    }

    $fmt = fn($e) => [
        'start'   => $e->start->format('H:i'),
        'end'     => $e->end->format('H:i'),
        'subject' => $e->subject,
    ];

    $room->update(['last_seen_at' => now()]);

    return response()->json([
        'status'       => $status,
        'current'      => $current ? $fmt($current) : null,
        'nextText'     => $nextText,
        'futureEvents' => $events->filter(fn($e) => $e->start > now())->take(3)->values()->map($fmt),
    ]);
});

Route::get('/reload-check', function () {
    return response()->json(['token' => Setting::get('reload_token', '0')]);
});

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/room-dashboard/{token}', [RoomDashboardController::class, 'show']);

Route::resource('rooms', RoomController::class);
