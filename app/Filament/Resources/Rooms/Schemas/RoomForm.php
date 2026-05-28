<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->label('Mailbox')
                    ->email()
                    ->required(),

                TextInput::make('username')
                    ->label('Exchange Username')
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->required(),

                TextInput::make('capacity')
                    ->numeric()
                    ->required(),

                CheckboxList::make('equipment')
                    ->label('Ausstattung')
                    ->options([
                        'computer' => 'Computer',
                        'beamer'   => 'Beamer',
                        'wireless' => 'Wireless Präsentation',
                        'monitor'  => 'Monitor',
                        'meeting'  => 'Mikrofon / Lautsprecher',
                    ])
                    ->columns(2),

                Section::make('Exchange Sync')
                    ->visibleOn('edit')
                    ->schema([
                        Placeholder::make('sync_status')
                            ->label('Status')
                            ->content(function ($record): HtmlString {
                                return match ($record?->sync_status) {
                                    'ok'    => new HtmlString('<span style="color:#16a34a;font-weight:600;">✔ OK</span>'),
                                    'error' => new HtmlString('<span style="color:#dc2626;font-weight:600;">✘ Fehler</span>'),
                                    default => new HtmlString('<span style="color:#6b7280;">– Noch nie synchronisiert</span>'),
                                };
                            }),

                        Placeholder::make('last_sync_at')
                            ->label('Letzter Sync')
                            ->content(fn ($record) => $record?->last_sync_at?->format('d.m.Y H:i') ?? '–'),

                        Placeholder::make('sync_message')
                            ->label('Letzte Meldung')
                            ->content(fn ($record) => $record?->sync_message ?? '–')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
