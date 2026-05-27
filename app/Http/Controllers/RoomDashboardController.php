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

        $warningThreshold = (int) Setting::get('warning_threshold', 15);
        $refreshInterval  = (int) Setting::get('refresh_interval', 30) * 1000;
        $bookingDurations = json_decode(Setting::get('booking_durations', '["30","60","120"]'), true);

        return view('room.dashboard', compact('room', 'logoSrc', 'warningThreshold', 'refreshInterval', 'bookingDurations'));
    }
}