<?php

namespace App\Http\Controllers;

use App\Models\Room;

class RoomDashboardController extends Controller
{
    public function show($token)
    {
        $room = Room::where('dashboard_token', $token)
            ->with('events')
            ->firstOrFail();

        return view('room.dashboard', compact('room'));
    }
}