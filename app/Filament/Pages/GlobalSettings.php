<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Schemas\Components\Actions;
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
            'logo_path' => Setting::get('logo_path'),
            'logo_url'  => Setting::get('logo_url'),
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

        Notification::make()
            ->title('Einstellungen gespeichert')
            ->success()
            ->send();
    }
}
