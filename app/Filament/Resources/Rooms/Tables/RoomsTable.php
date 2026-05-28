<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->searchable(),

                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sync_status')
                    ->label('Sync-Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ok'    => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ok'    => 'OK',
                        'error' => 'Fehler',
                        default => 'Nie',
                    }),

                TextColumn::make('sync_message')
                    ->label('Letzte Sync-Meldung')
                    ->wrap()
                    ->color(fn ($record) => $record?->sync_status === 'error' ? 'danger' : 'gray')
                    ->placeholder('–'),

                TextColumn::make('last_sync_at')
                    ->label('Letzter Sync')
                    ->date('d.m.Y \u\m H:i')
                    ->placeholder('–'),

                TextColumn::make('dashboard')
                    ->label('Dashboard')
                    ->state('Öffnen')
                    ->url(fn ($record) => url('/room-dashboard/' . $record->dashboard_token))
                    ->openUrlInNewTab(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
