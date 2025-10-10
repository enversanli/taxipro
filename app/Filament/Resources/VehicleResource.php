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
    protected static ?string $pluralLabel = 'vehicles';
    protected static ?string $modelLabel = 'vehicle';

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
        return $form->schema([
            Forms\Components\Section::make(__('common.vehicle_information'))
                ->description(__('common.vehicle_description'))
                ->schema([

                    Select::make('company_id')
                        ->label(__('common.company'))
                        ->options(fn () => Company::pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn () => auth()->user()->role === 'admin')
                        ->required(),

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
                        ->searchable(),

                    Forms\Components\TextInput::make('model')
                        ->label(__('common.model'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('license_plate')
                        ->label(__('common.license_plate'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('usage_type')
                        ->label(__('common.usage_type'))
                        ->options([
                            'taxi' => __('common.taxi'),
                            'rent' => __('common.rent_car'),
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('color')
                        ->label(__('common.color'))
                        ->maxLength(255),

                    Forms\Components\TextInput::make('code')
                        ->label(__('common.vehicle_code'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('common.vehicle_code_helper')),

                    Forms\Components\DatePicker::make('tuv_date')
                        ->label(__('common.tuv_date'))
                        ->displayFormat('d.m.Y'),

                    Forms\Components\DatePicker::make('insurance_date')
                        ->label(__('common.insurance_date'))
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
                    ->label(__('common.license_plate'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-identification'),

                TextColumn::make('brand')
                    ->label(__('common.brand'))
                    ->sortable(),

                TextColumn::make('model')
                    ->label(__('common.model'))
                    ->sortable(),

                TextColumn::make('usage_type')
                    ->label(__('common.usage_type'))
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'taxi' => 'success',
                        'rent' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('company.name')
                    ->label(__('common.company'))
                    ->visible(fn() => auth()->user()->role === 'admin'),
            ])
            ->filters([
                SelectFilter::make('usage_type')
                    ->label(__('common.usage_type'))
                    ->options([
                        'taxi' => __('common.taxi'),
                        'rent' => __('common.rent_car'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('common.edit')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label(__('common.delete_selected')),
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
