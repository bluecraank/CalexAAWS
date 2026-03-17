<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;

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
                        'beamer' => 'Beamer',
                        'wireless' => 'Wireless Präsentation',
                        'monitor' => 'Monitor',
                        'meeting' => 'Mikrofon / Lautsprecher',
                    ])
                    ->columns(2),
            ]);
    }
}