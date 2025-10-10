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
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'List';
    protected static ?string $pluralLabel = 'Vehicles';
    protected static ?string $modelLabel = 'Vehicle';

    public static function getNavigationGroup(): ?string
    {
        return __('common.vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Vehicle Information')
                ->description('Basic details about the vehicle')
                ->schema([

                    Select::make('company_id')
                        ->label('Company')
                        ->options(fn () => Company::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn () => auth()->user()->role === 'admin')
                        ->required(),

                    Select::make('brand')
                        ->label('Brand')
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
                        ->searchable(),

                    Forms\Components\TextInput::make('model')
                        ->label('Model')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('license_plate')
                        ->label('License Plate')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('usage_type')
                        ->label('Usage Type')
                        ->options([
                            'taxi' => 'Taxi',
                            'rent' => 'Mietwagen',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('color')
                        ->label('Color')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('code')
                        ->label('Vehicle Code')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Internal reference code or identifier.'),

                    Forms\Components\DatePicker::make('tuv_date')
                        ->label('TÜV Date')
                        ->displayFormat('d.m.Y'),

                    Forms\Components\DatePicker::make('insurance_date')
                        ->label('Insurance Date')
                        ->displayFormat('d.m.Y'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('license_plate')
                    ->label('License Plate')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-identification'),

                TextColumn::make('brand')
                    ->label('Brand')
                    ->sortable(),

                TextColumn::make('model')
                    ->label('Model')
                    ->sortable(),

                TextColumn::make('usage_type')
                    ->label('Usage Type')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'taxi' => 'success',
                        'rent' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->visible(fn() => auth()->user()->role === 'admin'),
            ])
            ->filters([
                SelectFilter::make('usage_type')
                    ->label('Usage Type')
                    ->options([
                        'taxi' => 'Taxi',
                        'rent' => 'Mietwagen',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
