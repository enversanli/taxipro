<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Forms\Get;


class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';
    protected static ?string $navigationLabel = 'Invoices';
    protected static ?string $pluralModelLabel = 'Invoices';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('driver_id')
                    ->label(__('common.driver'))
                    ->relationship(
                        name: 'driver',
                        titleAttribute: 'first_name',
                        modifyQueryUsing: fn ($query, $search) => $query->where(function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        })
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->required(),

                Select::make('vehicle_id')
                    ->label(__('common.vehicle'))
                    ->relationship('vehicle', 'license_plate')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->brand} {$record->model} - {$record->license_plate}")
                    ->searchable()
                    ->required(),

                TextInput::make('year')
                    ->label(__('common.year'))
                    ->default(now()->format('Y'))
                    ->disabled()
                    ->required(),

                Select::make('month')
                    ->label(__('common.month'))
                    ->options(fn () => __('common.months')) // use closure for better formatting and potential dynamic behavior
                    ->searchable()
                    ->required(),

                TextInput::make('total_income')
                    ->label(__('common.total_income') . ' (Taximetre)')
                    ->numeric()
                    ->prefix('€') // Shows the euro sign to the left
                    ->placeholder('0.00') // A helpful hint for users
                    ->suffixIcon('heroicon-o-calculator') // Optional: adds a small calculator icon
                    ->default(0)
                    ->required()
                    ->extraInputAttributes(['class' => 'text-right']) // Aligns text to right, common for numbers
                    ->columnSpan([
                        'default' => 1,
                        'md' => 1,
                    ]),

                ViewField::make('test')
                    ->label('Total Gross')
                    ->view('filament.fields.total-gross', function (Get $get) {
                        return [
                            'value' => (float) $get('total_gross') ?? 0,
                            'tip' => (float) $get('tip') ?? 0,
                            'cash' => (float) $get('cash') ?? 0,
                            'net' => (float) $get('net') ?? 0,
                        ];
                    })
                    ->reactive()
                    ->columnSpan('full'),  // <-- make full width


                Section::make()
                    ->schema([
                        HasManyRepeater::make('details') // lowercase relation name is typical
                        ->afterStateUpdated(function (?array $state, Set $set) {
                            $totalGross = collect($state)->sum(fn ($item) => (float) ($item['gross'] ?? 0));
                            $tip = collect($state)->sum(fn ($item) => (float) ($item['tip'] ?? 0));
                            $cash = collect($state)->sum(fn ($item) => (float) ($item['cash'] ?? 0));
                            $net = collect($state)->sum(fn ($item) => (float) ($item['net'] ?? 0));

                            $set('total_gross', $totalGross);
                            $set('tip', $tip);
                            $set('cash', $cash);
                            $set('net', $net);
                        })
                            ->reactive()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => strtoupper($state['platform'] ?? '') ?: 'Detail')
                        ->grid(2)
                            ->relationship('details')
                            ->label('Invoice Details')
                            ->schema([
                                Select::make('platform')
                                    ->label('Platform')
                                    ->options([
                                        'uber' => 'Uber',
                                        'bolt' => 'Bolt',
                                        'bliq' => 'Bliq',
                                        'freenow' => 'Free Now',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(2),

                                TextInput::make('gross')
                                    ->label('Gross')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->columnSpan(1),

                                TextInput::make('tip')
                                    ->label('Tip')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->columnSpan(1),

                                TextInput::make('cash')
                                    ->label('Cash')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->columnSpan(1),

                                TextInput::make('net')
                                    ->label('Net')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->columns(2)
                            ->createItemButtonLabel('Add Detail')
                            ->defaultItems(1)
                            ->reorderable()
                            ->columnSpan(2), // takes half grid

                    ])
                    ->columns(1)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('driver.first_name')->label(__('common.first_name')),
                TextColumn::make('driver.last_name')->label(__('common.last_name')),
                TextColumn::make('year')->label(__('common.year')),
                TextColumn::make('month')->label(__('common.month')),
                TextColumn::make('total_income')->label(__('common.total_income')),
                TextColumn::make('created_at')->dateTime()->label('Created'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriverInvoices::route('/'),
            'create' => Pages\CreateDriverInvoice::route('/create'),
            'edit' => Pages\EditDriverInvoice::route('/{record}/edit'),
        ];
    }
}
