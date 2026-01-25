<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Company; // Ensure models are imported
use App\Models\Voucher;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $recordTitleAttribute = 'number';

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
            ->columns(3) // Modern 3-Column Layout
            ->schema([

                // --- LEFT: TICKET DETAILS (2/3) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Gutschein Details')
                            ->icon('heroicon-m-ticket')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('number')
                                        ->label(__('common.voucher_number'))
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->prefixIcon('heroicon-m-qr-code')
                                        ->extraInputAttributes(['class' => 'font-mono uppercase']),

                                    TextInput::make('issuer')
                                        ->label(__('common.issuer')) // e.g., "Health Insurance" or "Hotel"
                                        ->required()
                                        ->prefixIcon('heroicon-m-building-office-2'),
                                ]),

                                TextInput::make('passenger_name')
                                    ->label(__('common.passenger_name'))
                                    ->prefixIcon('heroicon-m-user'),
                            ]),

                        Section::make('Fahrtstrecke')
                            ->icon('heroicon-m-map')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('from')
                                        ->label(__('common.from'))
                                        ->prefixIcon('heroicon-m-map-pin')
                                        ->placeholder('Abholadresse'),

                                    TextInput::make('to')
                                        ->label(__('common.to'))
                                        ->prefixIcon('heroicon-m-flag')
                                        ->placeholder('Zieladresse'),
                                ]),

                                Textarea::make('note')
                                    ->label(__('common.note'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // --- RIGHT: PAYMENT & ASSIGNMENT (1/3) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Status & Wert')
                            ->icon('heroicon-m-currency-euro')
                            ->schema([
                                TextInput::make('amount')
                                    ->label(__('common.amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->extraInputAttributes(['class' => 'text-lg font-bold']),

                                ToggleButtons::make('is_paid')
                                    ->label(__('common.paid_status'))
                                    ->options([
                                        0 => __('common.not_paid'),
                                        1 => __('common.paid'),
                                    ])
                                    ->colors([
                                        0 => 'danger',
                                        1 => 'success',
                                    ])
                                    ->icons([
                                        0 => 'heroicon-m-x-circle',
                                        1 => 'heroicon-m-check-circle',
                                    ])
                                    ->default(0)
                                    ->inline(),

                                DatePicker::make('valid_until')
                                    ->label(__('common.valid_until'))
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-calendar'),
                            ]),

                        Section::make('Zuordnung')
                            ->icon('heroicon-m-link')
                            ->schema([
                                Select::make('driver_id')
                                    ->label(__('common.driver'))
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-user'),

                                Select::make('vehicle_id')
                                    ->label(__('common.vehicle'))
                                    ->relationship('vehicle', 'license_plate')
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-truck'),

                                Select::make('company_id')
                                    ->label(__('common.company'))
                                    ->relationship('company', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn () => auth()->user()->role === 'admin')
                                    ->prefixIcon('heroicon-m-building-office'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Monospaced Number for readability
                TextColumn::make('number')
                    ->label(__('common.voucher_number'))
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('issuer')
                    ->label(__('common.issuer'))
                    ->searchable()
                    ->limit(20),

                // Combined Route (From -> To)
                TextColumn::make('from')
                    ->label('Strecke')
                    ->description(fn (Voucher $record) => $record->to ? '→ ' . $record->to : null)
                    ->searchable(['from', 'to'])
                    ->icon('heroicon-m-map-pin'),

                TextColumn::make('passenger_name')
                    ->label(__('common.passenger_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->money('eur')
                    ->weight('bold')
                    ->sortable(),

                // Icon Column for Payment Status
                IconColumn::make('is_paid')
                    ->label(__('common.paid'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label(__('common.valid_until'))
                    ->date('d.m.Y')
                    ->color('gray')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_paid')
                    ->label(__('common.paid_status'))
                    ->options([
                        1 => __('common.paid'),
                        0 => __('common.not_paid'),
                    ]),
                Tables\Filters\SelectFilter::make('issuer')
                    ->label(__('common.issuer'))
                    ->options(fn() => Voucher::distinct()->pluck('issuer', 'issuer')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
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
