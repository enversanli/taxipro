<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes'; // More relevant icon
    protected static ?string $navigationGroup = 'Finanzen'; // Grouping suggestion
    protected static ?int $navigationSort = 2;

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
            ->columns(3) // 3-Column Layout
            ->schema([

                // --- LEFT: MAIN EXPENSE DATA (2/3 width) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Ausgabendetails')
                            ->icon('heroicon-m-receipt-percent')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('type')
                                        ->label(__('common.expense_type'))
                                        ->options([
                                            'fuel' => __('common.expense_types.fuel'),
                                            'repair' => __('common.expense_types.repair'),
                                            'insurance' => __('common.expense_types.insurance'),
                                            'cash_withdrawals' => __('common.expense_types.cash_withdrawals'),
                                            'other' => __('common.expense_types.other'),
                                        ])
                                        ->prefixIcon('heroicon-m-tag')
                                        ->searchable()
                                        ->required()
                                        ->live(),

                                    TextInput::make('amount')
                                        ->label(__('common.amount'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->step(0.01)
                                        ->required()
                                        ->extraInputAttributes(['class' => 'text-lg font-semibold']),
                                ]),

                                TextInput::make('description')
                                    ->label(__('common.short_description'))
                                    ->placeholder('Z.B. Reifenwechsel oder Tanken Berlin')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                FileUpload::make('receipt_path')
                                    ->label(__('common.receipt_optional'))
                                    ->directory('receipts')
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull()
                                    ->openable()
                                    ->downloadable(),
                            ]),

                        Section::make(__('common.notes'))
                            ->collapsed()
                            ->schema([
                                RichEditor::make('note')
                                    ->hiddenLabel()
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo']),
                            ]),
                    ]),

                // --- RIGHT: CONTEXT & RELATIONS (1/3 width) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Zuordnung')
                            ->description('Fahrzeug & Fahrer')
                            ->icon('heroicon-m-link')
                            ->schema([
                                DatePicker::make('date')
                                    ->label(__('common.expense_date'))
                                    ->default(now())
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-calendar'),

                                Select::make('vehicle_id')
                                    ->label(__('common.vehicle'))
                                    ->relationship('vehicle', 'license_plate')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->license_plate} ({$record->brand})")
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-truck'),

                                Select::make('driver_id')
                                    ->label(__('common.driver'))
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-user'),

                                Select::make('invoice_id')
                                    ->label(__('common.related_invoice'))
                                    ->relationship('invoice', 'id')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "Abrechnung #{$record->id} ({$record->month}/{$record->year})")
                                    ->searchable()
                                    ->placeholder('Optional verknüpfen')
                                    ->prefixIcon('heroicon-m-document-text'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Receipt Thumbnail (Click to zoom)
                ImageColumn::make('receipt_path')
                    ->label('')
                    ->circular()
                    ->stacked(),

                TextColumn::make('date')
                    ->label(__('common.date'))
                    ->date('d.m.Y')
                    ->sortable()
                    ->color('gray'),

                // Stacked Vehicle & Driver Info
                TextColumn::make('vehicle.license_plate')
                    ->label(__('Fahrzeug / Fahrer'))
                    ->description(fn (Expense $record) => $record->driver ? $record->driver->first_name . ' ' . $record->driver->last_name : '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('common.type'))
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'fuel' => 'heroicon-m-fire',
                        'repair' => 'heroicon-m-wrench',
                        'cash_withdrawals' => 'heroicon-m-banknotes',
                        'insurance' => 'heroicon-m-shield-check',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'fuel' => 'warning', // Orange
                        'repair' => 'danger', // Red
                        'cash_withdrawals' => 'info', // Blue
                        'insurance' => 'success', // Green
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => __("common.expense_types.$state")),

                TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->money('eur')
                    ->sortable()
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('eur')
                            ->label('Total'),
                    ]),

                TextColumn::make('description')
                    ->label(__('common.description'))
                    ->limit(20)
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fuel' => __('common.expense_types.fuel'),
                        'repair' => __('common.expense_types.repair'),
                        'cash_withdrawals' => __('common.expense_types.cash_withdrawals'),
                        'other' => __('common.expense_types.other'),
                    ]),
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->relationship('vehicle', 'license_plate')
                    ->searchable()
                    ->preload()
                    ->label(__('common.vehicle')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(), // SlideOver for quick edits
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
