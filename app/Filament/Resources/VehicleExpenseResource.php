<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleExpenseResource\Pages;
use App\Models\VehicleExpense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleExpenseResource extends Resource
{
    protected static ?string $model = VehicleExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Vehicles';

    public static function getNavigationLabel(): string
    {
        return __('common.vehicle_expenses');
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
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label(__('common.expense_type'))
                            ->options([
                                'fuel' => __('common.expense_types.fuel'),
                                'repair' => __('common.expense_types.repair'),
                                'insurance' => __('common.expense_types.insurance'),
                                'other' => __('common.expense_types.other'),
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label(__('common.amount'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->nullable(),

                        Forms\Components\DatePicker::make('date')
                            ->label(__('common.expense_date'))
                            ->default(now())
                            ->required(),

                        Forms\Components\FileUpload::make('receipt_path')
                            ->label(__('common.receipt_optional'))
                            ->directory('receipts')
                            ->downloadable()
                            ->previewable(),

                        Forms\Components\TextInput::make('description')
                            ->label(__('common.short_description'))
                            ->placeholder(__('common.expense_placeholder')),

                        Forms\Components\RichEditor::make('note')
                            ->label(__('common.notes'))
                            ->placeholder(__('common.notes_placeholder'))
                            ->toolbarButtons([
                                'bold', 'italic', 'underline',
                                'bulletList', 'orderedList',
                                'link', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label(__('common.vehicle'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label(__('common.type'))
                    ->colors([
                        'fuel' => 'success',
                        'repair' => 'danger',
                        'insurance' => 'warning',
                        'other' => 'gray',
                    ])
                    ->formatStateUsing(fn($state) => __("common.expense_types.$state"))
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('common.date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('common.description'))
                    ->limit(40)
                    ->tooltip(fn($record) => $record->description),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('common.added_on'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('common.expense_type'))
                    ->options([
                        'fuel' => __('common.expense_types.fuel'),
                        'repair' => __('common.expense_types.repair'),
                        'insurance' => __('common.expense_types.insurance'),
                        'other' => __('common.expense_types.other'),
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('common.from')),
                        Forms\Components\DatePicker::make('until')->label(__('common.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicleExpenses::route('/'),
            'create' => Pages\CreateVehicleExpense::route('/create'),
            'edit' => Pages\EditVehicleExpense::route('/{record}/edit'),
        ];
    }
}
