<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('platform')
                    ->options([
                        'uber' => 'Uber',
                        'bolt' => 'Bolt',
                        'bliq' => 'Bliq',
                        'freenow' => 'Free Now'
                    ])
                    ->required(),
                TextInput::make('gross')->numeric()->required(),
                TextInput::make('tip')->numeric()->required(),
                TextInput::make('cash')->numeric()->required(),
                TextInput::make('net')->numeric()->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_id')
            ->columns([
                TextColumn::make('platform'),
                TextColumn::make('gross')->money('EUR'),
                TextColumn::make('tip')->money('EUR'),
                TextColumn::make('cash')->money('EUR'),
                TextColumn::make('net')->money('EUR'),
                TextColumn::make('created_at')->dateTime(),
            ])
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
