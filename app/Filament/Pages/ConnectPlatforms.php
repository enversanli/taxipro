<?php

namespace App\Filament\Pages;

use App\Models\PlatformConnection;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ConnectPlatforms extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationLabel = 'Integrationen';
    protected static ?string $title = 'Plattform Verbindungen';
    protected static string $view = 'filament.pages.connect-platforms';

    // This holds the live connection status for the view
    public array $connections = [];

    public function mount()
    {
        $this->refreshConnections();
    }

    public function refreshConnections()
    {
        // Fetch existing connections from DB
        $data = PlatformConnection::where('company_id', Auth::user()->company_id ?? null)
            ->get()
            ->keyBy('platform');

        // Map them to a simple array for the view
        $this->connections = $data->map(fn($item) => $item->is_active)->toArray();
    }

    /**
     * Define the Groups and Platforms here.
     * This makes it easy to add 'Stark' or 'TaxiFusion' later.
     */
    public function getGroups(): array
    {
        return [
            'Global Players' => [
                [
                    'id' => 'bolt',
                    'name' => 'Bolt',
                    'icon' => 'heroicon-m-bolt',
                    'color' => 'success',
                    'schema' => [
                        TextInput::make('client_id')->label('Client ID')->required(),
                        TextInput::make('client_secret')->label('Client Secret')->password()->required(),
                    ]
                ],
                [
                    'id' => 'uber',
                    'name' => 'Uber',
                    'icon' => 'heroicon-m-globe-alt',
                    'color' => 'gray', // Uber black
                    'schema' => [
                        TextInput::make('client_id')->label('Client ID')->required(),
                        TextInput::make('client_secret')->label('Client Secret')->password()->required(),
                        TextInput::make('organization_id')->label('Organization ID (Optional)'),
                    ]
                ],
                [
                    'id' => 'freenow',
                    'name' => 'Free Now',
                    'icon' => 'heroicon-m-paper-airplane',
                    'color' => 'danger',
                    'schema' => [
                        TextInput::make('api_key')->label('API Key')->password()->required(),
                    ]
                ],
            ],
            'Dispatch Software' => [
                [
                    'id' => 'taxiwin',
                    'name' => 'TaxiWin',
                    'icon' => 'heroicon-m-computer-desktop',
                    'color' => 'info',
                    'schema' => [
                        TextInput::make('username')->required(),
                        TextInput::make('password')->password()->required(),
                        TextInput::make('endpoint')->label('API URL')->default('https://api.taxiwin.com'),
                    ]
                ],
                [
                    'id' => 'stark',
                    'name' => 'Stark',
                    'icon' => 'heroicon-m-server',
                    'color' => 'warning',
                    'schema' => [
                        TextInput::make('api_token')->label('Token')->password()->required(),
                    ]
                ],
                [
                    'id' => 'taxifusion',
                    'name' => 'TaxiFusion',
                    'icon' => 'heroicon-m-arrows-right-left',
                    'color' => 'primary',
                    'schema' => [
                        TextInput::make('api_key')->password()->required(),
                    ]
                ],
                [
                    'id' => 'taxifunk',
                    'name' => 'Taxifunk / Taxi.eu',
                    'icon' => 'heroicon-m-radio',
                    'color' => 'warning',
                    'schema' => [
                        TextInput::make('user_id')->label('User ID')->required(),
                        TextInput::make('api_key')->label('Key')->password()->required(),
                    ]
                ],
            ]
        ];
    }

    /**
     * The Dynamic Action that opens the Modal
     */
    /**
     * The Dynamic Action that opens the Modal
     */
    public function connectAction(): Action
    {
        return Action::make('connect')
            ->label('Verbinden')
            ->modalWidth('md')
            ->mountUsing(function (Action $action, array $arguments) {
                // 1. Find the platform definition
                $platformId = $arguments['platform'];
                $groups = $this->getGroups();
                $definition = null;

                foreach ($groups as $g) {
                    foreach ($g as $p) {
                        if ($p['id'] === $platformId) $definition = $p;
                    }
                }

                // 2. Set the modal title and form schema dynamically
                $action->modalHeading('Verbinde ' . $definition['name']);
                $action->form(array_merge(
                    $definition['schema'],
                    [Toggle::make('is_active')->label('Aktivieren')->default(true)]
                ));

                // 3. Load existing data if we have it
                $existing = PlatformConnection::where('platform', $platformId)
                    ->where('company_id', Auth::user()->company_id ?? null)
                    ->first();

                if ($existing) {
                    // FIX: Use fillForm() instead of fill()
                    $action->fillForm(array_merge(
                        $existing->is_active ?? [],
                        ['is_active' => $existing->is_active]
                    ));
                }
            })
            ->action(function (array $data, array $arguments) {
                $platformId = $arguments['platform'];
                $isActive = $data['is_active'] ?? false;

                // --- NEW: UBER SERVICE INTEGRATION ---
                if ($platformId === 'uber') {
                    try {
                        // Assuming you pass credentials to the constructor or the connect method
                        // Adjust the path to your actual Service namespace
                        $uberService = new \App\Services\Uber\UberApiConnectService();

                        // We pass the NEW credentials from the form ($data) to verify them
                        // BEFORE we save them to the database.
                        $uberService->connect();

                    } catch (\Exception $e) {
                        // If connection fails, stop everything and show error
                        Notification::make()
                            ->danger()
                            ->title('Uber Verbindung fehlgeschlagen')
                            ->body($e->getMessage())
                            ->send();

                        // Do not save to DB if auth fails
                        $this->halt();
                    }
                }
                // -------------------------------------

                // Remove non-credential fields to save clean JSON
                unset($data['is_active']);

                PlatformConnection::updateOrCreate(
                    [
                        'company_id' => Auth::user()->company_id ?? null,
                        'platform' => $platformId
                    ],
                    [
                        'is_active' => $isActive,
                        'last_synced_at' => now(),
                    ]
                );

                Notification::make()->success()->title('Verbindung gespeichert')->send();
                $this->refreshConnections();
            });
    }}
