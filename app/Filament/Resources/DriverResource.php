<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
use App\Filament\Resources\DriverResource\RelationManagers;
use App\Filament\Resources\InvoiceRelationManagerResource\RelationManagers\DriversRelationManager;
use App\Models\Company;
use App\Models\Driver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MultiSelect;
use Filament\Tables\Columns\TextColumn;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    auth()->user()->role === 'admin'
                        ? Forms\Components\Select::make('company_id')
                        ->label('Company')
                        ->options(Company::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        : Forms\Components\Hidden::make('company_id')
                        ->default(auth()->user()->company_id),
                TextInput::make('first_name')->required(),
                TextInput::make('last_name')->required(),
                TextInput::make('phone')->tel(),
                TextInput::make('address'),
                Select::make('work_model')
                    ->label('Work Model')
                    ->required()
                    ->options([
                        'taxi' => 'Taxi',
                        'rent' => 'Rent',
                    ]),
                TextInput::make('provision_model'),
                MultiSelect::make('vehicles')
                    ->relationship('vehicles', 'license_plate')
                    ->preload()
                    ->label('Assigned Vehicles'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Address')
                    ->limit(20),

                TextColumn::make('work_model')
                    ->label('Work Model')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'taxi' => 'success',
                        'rent' => 'warning',
                    }),

                TextColumn::make('provision_model')
                    ->label('Provision Model')
                    ->limit(20),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('work_model')
                    ->label('Work Model')
                    ->options([
                        'taxi' => 'Taxi',
                        'rent' => 'Rent',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            DriversRelationManager::class,
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
