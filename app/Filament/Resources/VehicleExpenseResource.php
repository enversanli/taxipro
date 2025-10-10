<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleExpenseResource\Pages;
use App\Filament\Resources\VehicleExpenseResource\RelationManagers;
use App\Models\VehicleExpense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleExpenseResource extends Resource
{
    protected static ?string $model = VehicleExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Expenses';
    protected static ?string $pluralModelLabel = 'Expenses';
    protected static ?string $navigationGroup = 'Vehicles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Details')
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->relationship('vehicle', 'license_plate')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->brand} {$record->model} - {$record->license_plate}")
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Expense Type')
                            ->options([
                                'fuel' => 'Fuel',
                                'repair' => 'Repair',
                                'insurance' => 'Insurance',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (€)')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->nullable(), // now optional

                        Forms\Components\DatePicker::make('date')
                            ->label('Expense Date')
                            ->default(now())
                            ->required(),

                        Forms\Components\FileUpload::make('receipt_path')
                            ->label('Receipt (optional)')
                            ->directory('receipts')
                            ->downloadable()
                            ->previewable(),

                        Forms\Components\TextInput::make('description')
                            ->label('Short Description')
                            ->placeholder('e.g., Oil change, tire replacement...'),

                        Forms\Components\RichEditor::make('note')
                            ->label('Notes')
                            ->placeholder('Add detailed notes or comments...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
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
                    ->label('Vehicle')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'fuel' => 'success',
                        'repair' => 'danger',
                        'insurance' => 'warning',
                        'other' => 'gray',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (€)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added On')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Expense Type')
                    ->options([
                        'fuel' => 'Fuel',
                        'repair' => 'Repair',
                        'insurance' => 'Insurance',
                        'other' => 'Other',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
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
        return [
            //
        ];
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
