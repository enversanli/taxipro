<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Company;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ToggleButtons; // Modern replacement for small selects
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $recordTitleAttribute = 'license_plate';

    public static function getNavigationLabel(): string
    {
        return __('common.vehicles');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3) // Modern 3-column layout
            ->schema([

                // --- LEFT: VEHICLE IDENTITY (2/3) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('common.vehicle_information'))
                            ->icon('heroicon-m-identification')
                            ->schema([
                                // Admin Only: Company
                                Select::make('company_id')
                                    ->label(__('common.company'))
                                    ->options(fn () => Company::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn () => auth()->user()->role === 'admin')
                                    ->required()
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    Select::make('brand')
                                        ->label(__('common.brand'))
                                        ->options([
                                            'Mercedes-Benz' => 'Mercedes-Benz',
                                            'Volkswagen' => 'Volkswagen',
                                            'BMW' => 'BMW',
                                            'Audi' => 'Audi',
                                            'Opel' => 'Opel',
                                            'Skoda' => 'Skoda',
                                            'Ford' => 'Ford',
                                            'Toyota' => 'Toyota',
                                            'Renault' => 'Renault',
                                            'Hyundai' => 'Hyundai',
                                        ])
                                        ->required()
                                        ->searchable()
                                        ->prefixIcon('heroicon-m-sparkles'),

                                    TextInput::make('model')
                                        ->label(__('common.model'))
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('z.B. E-Klasse, Golf'),
                                ]),

                                TextInput::make('license_plate')
                                    ->label(__('common.license_plate'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-m-identification')
                                    ->extraInputAttributes(['class' => 'uppercase font-mono tracking-wider']), // Looks like a plate

                                TextInput::make('color')
                                    ->label(__('common.color'))
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-paint-brush'),
                            ]),
                    ]),

                // --- RIGHT: STATUS & COMPLIANCE (1/3) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Status & Termine')
                            ->icon('heroicon-m-clipboard-document-check')
                            ->schema([
                                ToggleButtons::make('usage_type') // Better UX than Select
                                ->label(__('common.usage_type'))
                                    ->options([
                                        'taxi' => __('common.taxi'),
                                        'rent' => __('common.rent_car'),
                                    ])
                                    ->colors([
                                        'taxi' => 'warning',
                                        'rent' => 'info',
                                    ])
                                    ->icons([
                                        'taxi' => 'heroicon-m-star',
                                        'rent' => 'heroicon-m-key',
                                    ])
                                    ->required()
                                    ->inline(),

                                TextInput::make('code')
                                    ->label(__('common.vehicle_code'))
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-qr-code')
                                    ->helperText(__('common.vehicle_code_helper')),

                                Section::make('Fristen')
                                    ->compact()
                                    ->schema([
                                        DatePicker::make('tuv_date')
                                            ->label(__('common.tuv_date'))
                                            ->displayFormat('d.m.Y')
                                            ->native(false)
                                            ->prefixIcon('heroicon-m-calendar-days'),

                                        DatePicker::make('insurance_date')
                                            ->label(__('common.insurance_date'))
                                            ->displayFormat('d.m.Y')
                                            ->native(false)
                                            ->prefixIcon('heroicon-m-shield-check'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Combined Car Info
                TextColumn::make('brand_model')
                    ->label(__('Fahrzeug'))
                    ->state(fn (Vehicle $record) => "{$record->brand} {$record->model}")
                    ->description(fn (Vehicle $record) => $record->color)
                    ->searchable(['brand', 'model'])
                    ->sortable(['brand']),

                // Stylized License Plate
                TextColumn::make('license_plate')
                    ->label(__('common.license_plate'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono') // Monospace font looks like a plate
                    ->copyable(),

                // Usage Badge
                TextColumn::make('usage_type')
                    ->label(__('common.usage_type'))
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'taxi' => 'heroicon-m-star',
                        'rent' => 'heroicon-m-key',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state) => match ($state) {
                        'taxi' => 'warning', // Yellow for Taxi
                        'rent' => 'info',    // Blue for Rent
                        default => 'gray',
                    }),

                // Smart Date Logic (Red if expired)
                TextColumn::make('tuv_date')
                    ->label('TÜV')
                    ->date('d.m.Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && $state < now() ? 'danger' : 'success')
                    ->icon(fn ($state) => $state && $state < now() ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle'),

                TextColumn::make('company.name')
                    ->label(__('common.company'))
                    ->visible(fn() => auth()->user()->role === 'admin')
                    ->badge()
                    ->color('gray'),
            ])
            ->defaultSort('brand')
            ->filters([
                SelectFilter::make('usage_type')
                    ->label(__('common.usage_type'))
                    ->options([
                        'taxi' => __('common.taxi'),
                        'rent' => __('common.rent_car'),
                    ]),
                SelectFilter::make('brand')
                    ->label(__('common.brand'))
                    ->options(fn() => Vehicle::distinct()->pluck('brand', 'brand')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(), // SlideOver is cleaner
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
