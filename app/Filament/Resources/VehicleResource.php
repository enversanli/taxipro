<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Company;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'List';
    protected static ?string $pluralLabel = 'vehicles';
    protected static ?string $title = 'List';


    public static function getNavigationGroup(): ?string
    {
        return __('common.vehicles');
    }
    public static function getNavigationLabel(): string
    {
        return __('common.vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn () => auth()->user()->role === 'admin'),

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
                    ->required(),

                Forms\Components\TextInput::make('license_plate')
                    ->label('License Plate')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('model')
                    ->label('Model')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('usage_type')
                    ->label('Usage Type')
                    ->options([
                        'taxi' => 'Taxi',
                        'rent' => 'Mietwagen',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('color')
                    ->label('Color')
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->label('Vehicle Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('tuv_date')
                ->label('TÜV Date'),

                Forms\Components\DatePicker::make('insurance_date')
                    ->label('Insurance Date')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('license_plate')
                    ->label('License Plate')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usage_type')
                    ->label('Usage Type')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'taxi' => 'success',
                        'rent' => 'warning',
                    }),
                TextColumn::make('brand')
                    ->label('Brand'),
                TextColumn::make('color')
                    ->label('Color')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
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
