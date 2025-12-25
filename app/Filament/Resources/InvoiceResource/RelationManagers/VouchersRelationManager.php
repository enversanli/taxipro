<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VouchersRelationManager extends RelationManager
{
    protected static string $relationship = 'vouchers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('number')
                                ->label(__('common.voucher_number'))
                                ->unique(ignoreRecord: true)
                                ->required(),
                            Forms\Components\TextInput::make('issuer')
                                ->label(__('common.issuer'))
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->label(__('common.amount'))
                                ->numeric()
                                ->prefix('€')
                                ->required(),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('passenger_name')
                                ->label(__('common.passenger_name')),
                            Forms\Components\DatePicker::make('valid_until')
                                ->label(__('common.valid_until')),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('from')
                                ->label(__('common.from')),
                            Forms\Components\TextInput::make('to')
                                ->label(__('common.to')),
                        ]),

                        Forms\Components\Toggle::make('is_paid')
                            ->label(__('common.is_paid'))
                            ->default(false)
                            ->inline(false),

                        Forms\Components\Textarea::make('note')
                            ->label(__('common.note'))
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('common.number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('issuer')
                    ->label(__('common.issuer'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('passenger_name')
                    ->label(__('common.passenger'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('common.amount'))
                    ->money('eur')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label(__('common.status'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('common.date'))
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $invoice = $this->getOwnerRecord();
                        $data['driver_id'] = $invoice->driver_id;
                        $data['vehicle_id'] = $invoice->vehicle_id;
                        $data['company_id'] = $invoice->company_id;
                        return $data;
                    })
                    ->after(fn () => $this->updateInvoiceTotals()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn () => $this->updateInvoiceTotals()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => $this->updateInvoiceTotals()),
            ]);
    }

    protected function updateInvoiceTotals(): void
    {
        $invoice = $this->getOwnerRecord();
        $totalVouchers = $invoice->vouchers()->sum('amount');
        dd($totalVouchers);
        $invoice->update([
            'coupon_payments' => $totalVouchers
        ]);
    }
}
