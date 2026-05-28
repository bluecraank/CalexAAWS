<?php

namespace App\Filament\Pages;

use App\Models\Room;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomOverview extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Raumübersicht';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::with(['events' => fn ($q) => $q->whereDate('start', today())])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Raum')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('capacity')
                    ->label('Kapazität')
                    ->formatStateUsing(fn ($state) => $state . ' Pers.')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(function (Room $room): string {
                        $now = Carbon::now();
                        $current = $room->events
                            ->first(fn ($e) => $e->start <= $now && $e->end >= $now);
                        if ($current) return 'belegt';
                        $next = $room->events
                            ->where('start', '>', $now)
                            ->sortBy('start')
                            ->first();
                        if ($next && $next->start->diffInMinutes($now) <= 15) return 'bald';
                        return 'frei';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'belegt' => 'danger',
                        'bald'   => 'warning',
                        default  => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belegt' => 'Belegt',
                        'bald'   => 'Bald belegt',
                        default  => 'Frei',
                    }),

                TextColumn::make('equipment')
                    ->label('Ausstattung')
                    ->state(function (Room $room): string {
                        $labels = [
                            'computer' => 'Computer',
                            'beamer'   => 'Beamer',
                            'wireless' => 'Wireless',
                            'monitor'  => 'Monitor',
                            'meeting'  => 'Mikrofon',
                        ];
                        return collect($room->equipment ?? [])
                            ->map(fn ($e) => $labels[$e] ?? $e)
                            ->join(', ');
                    })
                    ->wrap(),
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}
