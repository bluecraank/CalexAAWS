<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

class GlobalSettings extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Einstellungen';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'logo_path'          => Setting::get('logo_path'),
            'logo_url'           => Setting::get('logo_url'),
            'warning_threshold'  => (int) Setting::get('warning_threshold', 15),
            'refresh_interval'   => (int) Setting::get('refresh_interval', 30),
            'booking_durations'  => json_decode(Setting::get('booking_durations', '["30","60","120"]'), true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Logo')
                    ->description('Entweder eine Datei hochladen oder eine URL angeben. Die hochgeladene Datei hat Vorrang.')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo hochladen')
                            ->image()
                            ->disk('public')
                            ->directory('logos')
                            ->maxSize(2048)
                            ->nullable(),

                        TextInput::make('logo_url')
                            ->label('Logo-URL')
                            ->url()
                            ->placeholder('https://example.com/logo.png')
                            ->nullable(),
                    ]),

                Section::make('Dashboard')
                    ->schema([
                        TextInput::make('warning_threshold')
                            ->label('"Bald belegt"-Schwellenwert (Minuten)')
                            ->helperText('Ab wie vielen Minuten vor einem Termin der Raum als "bald belegt" gilt.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->default(15)
                            ->required(),

                        TextInput::make('refresh_interval')
                            ->label('Auto-Refresh-Intervall (Sekunden)')
                            ->helperText('Wie oft sich das Dashboard automatisch aktualisiert.')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(300)
                            ->default(30)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Buchungsoptionen')
                    ->description('Welche Buchungsdauern sollen auf dem Dashboard angeboten werden?')
                    ->schema([
                        CheckboxList::make('booking_durations')
                            ->label('Verfügbare Buchungsdauern')
                            ->options([
                                '15'  => '15 Minuten',
                                '30'  => '30 Minuten',
                                '45'  => '45 Minuten',
                                '60'  => '1 Stunde',
                                '90'  => '1,5 Stunden',
                                '120' => '2 Stunden',
                                '180' => '3 Stunden',
                                '240' => '4 Stunden',
                            ])
                            ->columns(4)
                            ->required(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make($this->getFormActions())
                        ->key('form-actions'),
                ]),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Speichern')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if ($data['logo_path']) {
            Setting::set('logo_path', $data['logo_path']);
        } elseif (array_key_exists('logo_path', $data)) {
            Setting::set('logo_path', null);
        }

        Setting::set('logo_url', $data['logo_url'] ?? null);
        Setting::set('warning_threshold', $data['warning_threshold'] ?? 15);
        Setting::set('refresh_interval', $data['refresh_interval'] ?? 30);
        Setting::set('booking_durations', json_encode($data['booking_durations'] ?? ['30', '60', '120']));

        Notification::make()
            ->title('Einstellungen gespeichert')
            ->success()
            ->send();
    }
}
