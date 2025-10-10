<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverResource\Pages;
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

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function getNavigationGroup(): ?string
    {
        return __('common.vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                auth()->user()->role === 'admin'
                    ? Forms\Components\Select::make('company_id')
                    ->label(__('common.company'))
                    ->options(Company::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    : Forms\Components\Hidden::make('company_id')
                    ->default(auth()->user()->company_id),

                TextInput::make('first_name')
                    ->label(__('common.first_name'))
                    ->required(),

                TextInput::make('last_name')
                    ->label(__('common.last_name'))
                    ->required(),

                TextInput::make('phone')
                    ->label(__('common.phone'))
                    ->tel(),

                TextInput::make('email')
                    ->label(__('common.email'))
                    ->email(),

                TextInput::make('address')
                    ->label(__('common.address')),

                Select::make('work_model')
                    ->label(__('common.work_model'))
                    ->required()
                    ->options([
                        'taxi' => __('common.taxi'),
                        'rent' => __('common.rent'),
                    ]),

                TextInput::make('provision_model')
                    ->label(__('common.provision_model')),

                MultiSelect::make('vehicles')
                    ->relationship('vehicles', 'license_plate')
                    ->preload()
                    ->label(__('common.assigned_vehicles')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('common.first_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label(__('common.last_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('common.phone'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label(__('common.address'))
                    ->limit(20),

                TextColumn::make('email')
                    ->label(__('common.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('work_model')
                    ->label(__('common.work_model'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'taxi' => 'success',
                        'rent' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => __("common.$state")),

                TextColumn::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('work_model')
                    ->label(__('common.work_model'))
                    ->options([
                        'taxi' => __('common.taxi'),
                        'rent' => __('common.rent'),
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
