<?php

namespace App\Filament\Pages;

use App\Services\Platforms\ImportFromPlatform;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;

class DataImport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static ?string $navigationLabel = 'Daten Import';
    protected static ?string $title = 'Plattform Import';
    protected static string $view = 'filament.pages.data-import';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'platform' => 'bolt',
            'action' => 'orders',
            'start_date' => now()->startOfMonth(),
            'end_date' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Import Konfiguration')
                    ->description('Wählen Sie die Quelle und den Datentyp.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('platform')
                                ->label('Plattform')
                                ->options([
                                    'bolt' => 'Bolt',
                                    'uber' => 'Uber (Coming Soon)',
                                    'freenow' => 'FreeNow (Coming Soon)',
                                ])
                                ->required()
                                ->live()
                                ->native(false),

                            Select::make('action')
                                ->label('Aktion')
                                ->options([
                                    // NEW: Added Drivers & Vehicles
                                    'orders'   => 'Fahrten (Orders)',
                                    'drivers'  => 'Fahrerliste (Drivers)',
                                    'vehicles' => 'Fahrzeugliste (Vehicles)',
                                ])
                                ->required()
                                ->live() // Important: Triggers visibility checks below
                                ->default('orders'),
                        ]),

                        Grid::make(2)->schema([
                            // DatePickers are ONLY visible for 'orders'
                            DatePicker::make('start_date')
                                ->label('Startdatum')
                                ->default(now()->startOfMonth())
                                ->required()
                                ->visible(fn (Get $get) => $get('action') === 'orders'),

                            DatePicker::make('end_date')
                                ->label('Enddatum')
                                ->default(now())
                                ->required()
                                ->maxDate(now())
                                ->visible(fn (Get $get) => $get('action') === 'orders'),
                        ]),
                    ])
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start_import')
                ->label('Import Starten')
                ->color('primary')
                ->icon('heroicon-m-play')
                ->action(fn () => $this->runImport()),
        ];
    }

    public function runImport()
    {
        $data = $this->form->getState();
        $importer = new ImportFromPlatform();

        try {
            // SWITCH Logic based on Action
            $result = match ($data['action']) {
                'orders'   => $importer->importOrders($data['platform'], $data['start_date'], $data['end_date']),
                'drivers'  => $importer->importDrivers($data['platform']),
                'vehicles' => $importer->importVehicles($data['platform']),
            };

            Notification::make()
                ->success()
                ->title('Import Abgeschlossen')
                ->body("{$result['imported']} Datensätze aktualisiert/importiert.")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Fehler beim Import')
                ->body($e->getMessage())
                ->send();
        }
    }
}
