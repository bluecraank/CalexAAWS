<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use App\Models\RoomEvent;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopRoomsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Beliebte Räume';

    protected function getStats(): array
    {
        $topToday = Room::withCount([
            'events as events_count' => fn ($q) => $q->whereDate('start', today()),
        ])->orderByDesc('events_count')->first();

        $topWeek = Room::withCount([
            'events as events_count' => fn ($q) => $q->whereBetween('start', [
                now()->startOfWeek(), now()->endOfWeek(),
            ]),
        ])->orderByDesc('events_count')->first();

        $totalToday = RoomEvent::whereDate('start', today())->count();
        $totalWeek  = RoomEvent::whereBetween('start', [
            now()->startOfWeek(), now()->endOfWeek(),
        ])->count();

        return [
            Stat::make('Beliebtester Raum heute', $topToday?->events_count > 0 ? $topToday->name : '–')
                ->description($topToday?->events_count > 0 ? $topToday->events_count . ' Buchungen' : 'Keine Buchungen')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),

            Stat::make('Beliebtester Raum diese Woche', $topWeek?->events_count > 0 ? $topWeek->name : '–')
                ->description($topWeek?->events_count > 0 ? $topWeek->events_count . ' Buchungen' : 'Keine Buchungen')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('Buchungen heute', $totalToday)
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Buchungen diese Woche', $totalWeek)
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
