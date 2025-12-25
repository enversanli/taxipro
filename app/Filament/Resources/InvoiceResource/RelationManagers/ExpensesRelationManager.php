<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('type')
                        ->label(__('common.expense_type'))
                        ->options([
                            'fuel' => __('common.expense_types.fuel'),
                            'cash_withdrawals' => __('common.expense_types.cash_withdrawals'),
                            'repair' => __('common.expense_types.repair'),
                            'insurance' => __('common.expense_types.insurance'),
                            'other' => __('common.expense_types.other'),
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->label(__('common.amount'))
                        ->numeric()
                        ->prefix('€')
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label(__('common.date'))
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('description')
                        ->label(__('common.description'))
                        ->placeholder('Örn: Mazot fişi veya avans ödemesi'),

                    Forms\Components\FileUpload::make('receipt_path')
                        ->label(__('common.receipt'))
                        ->directory('receipts')
                    ,

                    Forms\Components\RichEditor::make('note')
                    ->label(__('common.note'))
                    ->placeholder('Açıklayın ...'),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('common.date'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('common.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fuel' => 'success',
                        'cash_withdrawals' => 'warning',
                        'repair' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => __("common.expense_types.$state")),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->money('eur')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('common.description'))
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fuel' => __('common.expense_types.fuel'),
                        'cash_withdrawals' => __('common.expense_types.cash_withdrawals'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Yeni Harcama Ekle')
                    // Otomatik atamayı garantiye almak için driver_id'yi de faturadan çekebiliriz:
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['driver_id'] = $this->getOwnerRecord()->driver_id;
                        $data['vehicle_id'] = $this->getOwnerRecord()->vehicle_id;
                        return $data;
                    }),
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
}
