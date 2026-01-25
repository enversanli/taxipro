<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $title = 'Fahrten-Details'; // Custom Title
    protected static ?string $icon = 'heroicon-o-list-bullet';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('platform')
                        ->label('Plattform')
                        ->options([
                            'uber' => 'Uber',
                            'bolt' => 'Bolt',
                            'bliq' => 'Bliq',
                            'freenow' => 'Free Now'
                        ])
                        ->searchable()
                        ->required()
                        ->columnSpanFull(), // Full width for better UX

                    TextInput::make('gross')
                        ->label('Brutto')
                        ->prefix('€')
                        ->numeric()
                        ->required(),

                    TextInput::make('tip')
                        ->label('Trinkgeld')
                        ->prefix('€')
                        ->numeric()
                        ->default(0),

                    TextInput::make('cash')
                        ->label('Barzahlung')
                        ->prefix('€')
                        ->numeric()
                        ->default(0),

                    TextInput::make('net')
                        ->label('Netto (Payout)')
                        ->prefix('€')
                        ->numeric()
                        ->required(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_id')
            ->columns([
                // LOGO COLUMN
                TextColumn::make('platform')
                    ->label('Plattform')
                    ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(
                        '<div class="flex items-center gap-3">
                            <img src="/images/platforms/' . strtolower($state) . '.png"
                                 alt="' . $state . '"
                                 style="height: 24px; width: 24px; object-fit: contain;"
                                 onerror="this.style.display=\'none\'" />
                            <span class="font-medium capitalize">' . ucfirst($state) . '</span>
                        </div>'
                    )),

                TextColumn::make('gross')
                    ->label('Umsatz')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('tip')
                    ->label('Tip')
                    ->money('EUR')
                    ->color('success'), // Green color for tips

                TextColumn::make('cash')
                    ->label('Bar')
                    ->money('EUR')
                    ->color('danger'), // Red color for cash (since it's debt to company)

                TextColumn::make('net')
                    ->label('Payout')
                    ->money('EUR')
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // slideOver() makes it a side-panel popup
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->modalWidth('md')
                    ->label('Fahrt hinzufügen'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver()->modalWidth('md'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
