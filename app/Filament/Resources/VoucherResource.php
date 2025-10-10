<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Resources\VoucherResource\RelationManagers;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Taxi Vouchers';
    protected static ?string $pluralLabel = 'Taxi Vouchers';
    protected static ?string $modelLabel = 'Taxi Voucher';

    public static function getNavigationGroup(): ?string
    {
        return __('common.invoices');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('driver_id')
                    ->label('Driver')
                    ->relationship('driver', 'first_name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('vehicle_id')
                    ->label('Vehicle')
                    ->relationship('vehicle', 'license_plate')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('issuer')
                    ->required(),
                Forms\Components\TextInput::make('passenger_name'),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\DatePicker::make('valid_until'),
                Forms\Components\TextInput::make('from')
                    ->label('Nereden')
                    ->nullable(),
                Forms\Components\TextInput::make('to')
                    ->label('Nereye')
                    ->nullable(),
                Forms\Components\TextInput::make('note')
                    ->label('Note')
                    ->nullable(),
                Forms\Components\Toggle::make('is_paid')
                    ->label('Paid')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('issuer')->searchable(),
                Tables\Columns\TextColumn::make('passenger_name')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('eur'),
                Tables\Columns\TextColumn::make('valid_until')->date(),
                Tables\Columns\TextColumn::make('from')->label('Nereden'),
                Tables\Columns\TextColumn::make('to')->label('Nereye'),
                Tables\Columns\BadgeColumn::make('is_paid')
                    ->label('Paid Status')
                    ->formatStateUsing(fn ($state): string => $state ? 'Paid' : 'Not Paid')
                    ->colors([
                        'success' => fn ($state): bool => $state == 1,
                        'danger' => fn ($state): bool => $state == 0,
                    ])
                    ->sortable(),

            ])
            ->filters([
                //
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
            //
        ];
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
