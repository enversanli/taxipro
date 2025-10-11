<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function getPluralModelLabel(): string
    {
        return __('common.taxi_vouchers');
    }

    public static function getNavigationLabel(): string
    {
        return __('common.taxi_voucher');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.invoices');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('driver_id')
                    ->label(__('common.driver'))
                    ->relationship('driver', 'first_name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Select::make('vehicle_id')
                    ->label(__('common.vehicle'))
                    ->relationship('vehicle', 'license_plate')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\TextInput::make('number')
                    ->label(__('common.voucher_number'))
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('issuer')
                    ->label(__('common.issuer'))
                    ->required(),

                Forms\Components\TextInput::make('passenger_name')
                    ->label(__('common.passenger_name')),

                Forms\Components\TextInput::make('amount')
                    ->label(__('common.amount'))
                    ->numeric()
                    ->prefix('€'),

                Forms\Components\DatePicker::make('valid_until')
                    ->label(__('common.valid_until')),

                Forms\Components\TextInput::make('from')
                    ->label(__('common.from'))
                    ->nullable(),

                Forms\Components\TextInput::make('to')
                    ->label(__('common.to'))
                    ->nullable(),

                Forms\Components\TextInput::make('note')
                    ->label(__('common.note'))
                    ->nullable(),

                Forms\Components\Toggle::make('is_paid')
                    ->label(__('common.paid'))
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('common.voucher_number'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('issuer')
                    ->label(__('common.issuer'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('passenger_name')
                    ->label(__('common.passenger_name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->money('eur'),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('common.valid_until'))
                    ->date(),

                Tables\Columns\TextColumn::make('from')
                    ->label(__('common.from')),

                Tables\Columns\TextColumn::make('to')
                    ->label(__('common.to')),

                Tables\Columns\BadgeColumn::make('is_paid')
                    ->label(__('common.paid_status'))
                    ->formatStateUsing(fn ($state): string => $state ? __('common.paid') : __('common.not_paid'))
                    ->colors([
                        'success' => fn ($state): bool => $state == 1,
                        'danger' => fn ($state): bool => $state == 0,
                    ])
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('common.edit')),
                Tables\Actions\DeleteAction::make()->label(__('common.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label(__('common.delete_selected')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
