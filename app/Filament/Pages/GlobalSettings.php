<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class GlobalSettings extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Einstellungen';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // logo_path bewusst NICHT befüllen – FileUpload nur für neue Uploads
            'logo_url'          => Setting::get('logo_url'),
            'warning_threshold' => (int) Setting::get('warning_threshold', 15),
            'refresh_interval'  => (int) Setting::get('refresh_interval', 30),
            'booking_durations' => json_decode(Setting::get('booking_durations', '["30","60","120"]'), true),
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
                        Placeholder::make('logo_preview')
                            ->label('Aktuelles Logo')
                            ->content(function (): HtmlString {
                                $path = Setting::get('logo_path');
                                $url  = $path
                                    ? Storage::disk('public')->url($path)
                                    : Setting::get('logo_url');

                                if (! $url) {
                                    return new HtmlString('<span style="color:#9ca3af">Kein Logo gesetzt</span>');
                                }

                                return new HtmlString(
                                    '<img src="' . e($url) . '" style="max-height:64px;max-width:240px;object-fit:contain;">'
                                );
                            }),

                        FileUpload::make('logo_path')
                            ->label('Neues Logo hochladen (ersetzt das aktuelle)')
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

        // Nur überschreiben wenn wirklich ein neues Bild hochgeladen wurde
        if (! empty($data['logo_path'])) {
            Setting::set('logo_path', $data['logo_path']);
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
