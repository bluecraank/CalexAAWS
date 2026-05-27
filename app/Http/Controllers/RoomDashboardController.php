<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class RoomDashboardController extends Controller
{
    public function show($token)
    {
        $room = Room::where('dashboard_token', $token)
            ->with('events')
            ->firstOrFail();

        $logoPath = Setting::get('logo_path');
        $logoUrl  = Setting::get('logo_url');
        $logoSrc  = $logoPath ? Storage::url($logoPath) : ($logoUrl ?: null);

        return view('room.dashboard', compact('room', 'logoSrc'));
    }
}