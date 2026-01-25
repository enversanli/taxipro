<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Models\Company;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $recordTitleAttribute = 'first_name';

    public static function getNavigationGroup(): ?string
    {
        return __('common.vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3) // 1. Create a 3-column grid
            ->schema([

                // --- LEFT COLUMN (2/3): PERSONAL INFO ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Fahrerprofil')
                            ->description('Persönliche Daten und Kontaktinformationen')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('first_name')
                                        ->label(__('common.first_name'))
                                        ->required()
                                        ->prefixIcon('heroicon-m-user'),

                                    TextInput::make('last_name')
                                        ->label(__('common.last_name'))
                                        ->required()
                                        ->prefixIcon('heroicon-m-user'),
                                ]),

                                Grid::make(2)->schema([
                                    TextInput::make('email')
                                        ->label(__('common.email'))
                                        ->email()
                                        ->prefixIcon('heroicon-m-envelope'),

                                    TextInput::make('phone')
                                        ->label(__('common.phone'))
                                        ->tel()
                                        ->prefixIcon('heroicon-m-phone'),
                                ]),

                                TextInput::make('address')
                                    ->label(__('common.address'))
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // --- RIGHT COLUMN (1/3): EMPLOYMENT & SETTINGS ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Einstellungen')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                // Company (Admin Only)
                                auth()->user()->role === 'admin'
                                    ? Select::make('company_id')
                                    ->label(__('common.company'))
                                    ->options(Company::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon('heroicon-m-building-office')
                                    : Forms\Components\Hidden::make('company_id')
                                    ->default(auth()->user()->company_id),

                                Select::make('work_model')
                                    ->label(__('common.work_model'))
                                    ->options([
                                        'taxi' => __('common.taxi'),
                                        'rent' => __('common.rent'),
                                        'employee' => 'Angestellt',
                                    ])
                                    ->required()
                                    ->native(false),

                                TextInput::make('provision_model')
                                    ->label(__('common.provision_model'))
                                    ->numeric()
                                    ->suffix('%'),

                                // Replaces MultiSelect
                                Select::make('vehicles')
                                    ->label(__('common.assigned_vehicles'))
                                    ->relationship('vehicles', 'license_plate')
                                    ->multiple() // Allows selecting multiple vehicles
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Combined Name & Email Column
                TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->getStateUsing(fn (Driver $record) => "{$record->first_name} {$record->last_name}")
                    ->description(fn (Driver $record) => $record->email)
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(['first_name'])
                    ->icon('heroicon-m-user-circle'),

                TextColumn::make('phone')
                    ->label(__('common.phone'))
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->searchable(),

                // Work Model Badge
                TextColumn::make('work_model')
                    ->label(__('common.work_model'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'taxi' => 'warning', // Yellow/Orange
                        'rent' => 'info',    // Blue
                        'employee' => 'success', // Green
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                // Vehicles List (Badge Style)
                TextColumn::make('vehicles.license_plate')
                    ->label(__('Fahrzeuge'))
                    ->badge()
                    ->color('gray')
                    ->limitList(2) // Shows "Plate 1, Plate 2, +1 more"
                    ->separator(','),

                TextColumn::make('company.name')
                    ->label(__('common.company'))
                    ->visible(fn() => auth()->user()->role === 'admin')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('work_model')
                    ->label(__('common.work_model'))
                    ->options([
                        'taxi' => __('common.taxi'),
                        'rent' => __('common.rent'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(), // SlideOver is cleaner
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers here if needed (e.g. Invoices)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
