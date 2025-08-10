<?php

namespace App\Filament\Resources\InvoiceRelationManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DriversRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform')
                    ->options([
                        'uber' => 'Uber',
                        'bolt' => 'Bolt',
                        'bliq' => 'Bliq',
                        'freenow' => 'FreeNow'
                    ])
                    ->required(),

                Forms\Components\TextInput::make('total_income')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('gross')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('tip')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('cash')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('net')
                    ->numeric()
                    ->required(),

                Forms\Components\DatePicker::make('date')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('driver')
            ->columns([
                Tables\Columns\TextColumn::make('platform'),
                Tables\Columns\TextColumn::make('total_income')->money('EUR'),
                Tables\Columns\TextColumn::make('date')->date(),            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
