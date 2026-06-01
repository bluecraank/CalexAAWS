<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use App\Models\RoomEvent;
use Filament\Widgets\ChartWidget;

class BookingHistoryWidget extends ChartWidget
{
    protected ?string $heading = 'Belegung letzte 7 Tage';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());

        $labels = $days->map(fn ($day) => $day->translatedFormat('D d.m.'))->toArray();

        $colors = [
            'rgba(59,130,246,0.7)',
            'rgba(16,185,129,0.7)',
            'rgba(245,158,11,0.7)',
            'rgba(239,68,68,0.7)',
            'rgba(139,92,246,0.7)',
            'rgba(236,72,153,0.7)',
            'rgba(20,184,166,0.7)',
        ];

        $rooms = Room::all();

        $datasets = $rooms->map(function (Room $room, int $index) use ($days, $colors): array {
            $data = $days->map(fn ($day) => RoomEvent::where('room_id', $room->id)
                ->whereDate('start', $day)
                ->count()
            )->toArray();

            return [
                'label'           => $room->name,
                'data'            => $data,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderRadius'    => 4,
            ];
        })->toArray();

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1],
                    'title'       => [
                        'display' => true,
                        'text'    => 'Buchungen',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
