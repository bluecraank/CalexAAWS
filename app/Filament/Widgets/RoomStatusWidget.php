<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RoomStatusWidget extends BaseWidget
{
    protected static ?string $heading = 'Gerätestatus';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::query()
                    ->with(['events' => fn ($q) => $q->whereDate('start', today())])
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Raum'),

                TextColumn::make('online_status')
                    ->label('Gerät')
                    ->state(function (Room $room): string {
                        if (! $room->last_seen_at) return 'never';
                        return $room->last_seen_at->gt(now()->subMinutes(2)) ? 'online' : 'offline';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online'  => 'success',
                        'offline' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'online'  => 'Online',
                        'offline' => 'Offline',
                        default   => 'Noch nie gesehen',
                    }),

                TextColumn::make('last_seen_at')
                    ->label('Zuletzt gemeldet')
                    ->dateTime('d.m.Y H:i:s')
                    ->placeholder('–')
                    ->description(fn (Room $room): ?string => $room->last_seen_at
                        ? $room->last_seen_at->diffForHumans()
                        : null
                    ),

                TextColumn::make('current_meeting')
                    ->label('Aktueller Termin')
                    ->state(function (Room $room): string {
                        $now = Carbon::now();
                        $event = $room->events
                            ->first(fn ($e) => $e->start <= $now && $e->end >= $now);
                        return $event ? 'busy' : 'free';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'busy' ? 'danger' : 'success')
                    ->formatStateUsing(function (string $state, Room $room): string {
                        if ($state === 'free') return 'Frei';
                        $now = Carbon::now();
                        $event = $room->events->first(fn ($e) => $e->start <= $now && $e->end >= $now);
                        $remaining = (int) ceil($now->diffInSeconds($event->end) / 60);
                        return 'Belegt – noch ' . ($remaining <= 60
                            ? $remaining . ' Min.'
                            : (int)($remaining / 60) . ' Std. ' . ($remaining % 60) . ' Min.');
                    }),

                TextColumn::make('events_today')
                    ->label('Termine heute')
                    ->state(fn (Room $room): int => $room->events->count())
                    ->alignCenter(),

                TextColumn::make('dashboard_link')
                    ->label('Dashboard')
                    ->state('Öffnen')
                    ->url(fn (Room $room): string => url('/room-dashboard/' . $room->dashboard_token))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false)
            ->poll('30s');
    }
}
