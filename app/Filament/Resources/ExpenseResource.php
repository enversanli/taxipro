<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense; // Model ismini migration ile eşitledik
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function getNavigationLabel(): string
    {
        return __('common.expenses');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('common.expense_details'))
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label(__('common.vehicle'))
                            ->relationship('vehicle', 'license_plate')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->brand} {$record->model} - {$record->license_plate}")
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('driver_id')
                            ->label(__('common.driver'))
                            ->relationship('driver', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->label(__('common.expense_type'))
                            ->options([
                                'fuel' => __('common.expense_types.fuel'),
                                'cash_withdrawals' => __('common.expense_types.cash_withdrawals'),
                                'repair' => __('common.expense_types.repair'),
                                'insurance' => __('common.expense_types.insurance'),
                                'other' => __('common.expense_types.other'),
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('amount')
                            ->label(__('common.amount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),

                        Forms\Components\DatePicker::make('date')
                            ->label(__('common.expense_date'))
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('invoice_id')
                            ->label(__('common.related_invoice'))
                            ->relationship('invoice', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => "Invoice #{$record->id} - {$record->month}/{$record->year}")
                            ->placeholder(__('common.optional'))
                            ->searchable(),

                        Forms\Components\TextInput::make('description')
                            ->label(__('common.short_description'))
                            ->placeholder(__('common.expense_placeholder'))
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('receipt_path')
                            ->label(__('common.receipt_optional'))
                            ->directory('receipts')
                            ->visibility('public')
                            ->image() // Eğer sadece resimse
                            ->downloadable(),

                        Forms\Components\RichEditor::make('note')
                            ->label(__('common.notes'))
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('common.date'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label(__('common.vehicle'))
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver.first_name')
                    ->label(__('common.driver'))
                    ->formatStateUsing(fn($record) => $record->driver ? "{$record->driver->first_name} {$record->driver->last_name}" : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('common.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fuel' => 'success',
                        'cash_withdrawals' => 'warning',
                        'repair' => 'danger',
                        'insurance' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => __("common.expense_types.$state")),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->money('eur')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('common.description'))
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fuel' => __('common.expense_types.fuel'),
                        'cash_withdrawals' => __('common.expense_types.cash_withdrawals'),
                        'repair' => __('common.expense_types.repair'),
                        'other' => __('common.expense_types.other'),
                    ]),
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->relationship('vehicle', 'license_plate')
                    ->label(__('common.vehicle')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
