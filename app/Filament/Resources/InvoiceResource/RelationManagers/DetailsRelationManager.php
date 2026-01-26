<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\InvoiceDetail;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $title = 'Plattform Details';
    protected static ?string $icon = 'heroicon-o-list-bullet';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([]); // We use custom actions for forms
    }

    public function table(Table $table): Table
    {
        return $table
            // 1. ROBUST QUERY: Ensure we get exactly 1 row per platform for THIS invoice
            ->modifyQueryUsing(function (Builder $query) {
                $invoiceId = $this->getOwnerRecord()->id;

                return $query->whereIn('id', function ($q) use ($invoiceId) {
                    $q->selectRaw('MAX(id)')
                        ->from('invoice_details')
                        ->where('invoice_id', $invoiceId) // Force filter by current invoice
                        ->groupBy('platform');
                });
            })
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // --- ICON ---
                    Tables\Columns\IconColumn::make('platform')
                        ->icon(fn (string $state): string => match ($state) {
                            'uber' => 'heroicon-m-globe-alt',
                            'bolt' => 'heroicon-m-bolt', // Ensure you have this icon or use a generic one
                            'bliq' => 'heroicon-m-cursor-arrow-rays',
                            'freenow' => 'heroicon-m-paper-airplane',
                            default => 'heroicon-m-rectangle-stack',
                        })
                        ->size(Tables\Columns\IconColumn\IconColumnSize::Large)
                        ->color(fn (string $state): string => match ($state) {
                            'uber' => 'info',
                            'bolt' => 'success', // Green
                            'freenow' => 'danger',
                            default => 'gray',
                        })
                        ->alignCenter(),

                    // --- LABEL ---
                    Tables\Columns\TextColumn::make('platform')
                        ->formatStateUsing(fn (string $state) => ucfirst($state))
                        ->weight('bold')
                        ->alignCenter(),

                    // --- TOTAL SUMMARY ---
                    Tables\Columns\TextColumn::make('total_gross')
                        ->state(function (Model $record) {
                            // Sum ALL rows for this platform, not just the representative one
                            return $record->invoice->details()
                                ->where('platform', $record->platform)
                                ->sum('gross_amount');
                        })
                        ->money('EUR')
                        ->size('xs')
                        ->color('gray')
                        ->alignCenter(),
                ])->space(3),
            ])
            // --- HEADER ACTION: THIS ADDS THE MISSING BUTTONS ---
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Plattform hinzufügen')
                    ->icon('heroicon-m-plus')
                    ->slideOver()
                    ->modalWidth('md')
                    ->form([
                        Select::make('platform')
                            ->label('Plattform wählen')
                            ->options([
                                'bolt' => 'Bolt',
                                'uber' => 'Uber',
                                'bliq' => 'Bliq',
                                'freenow' => 'FreeNow'
                            ])
                            ->required()
                            // Prevent adding a platform that already exists
                            ->disableOptionWhen(function ($value) {
                                $invoiceId = $this->getOwnerRecord()->id;
                                return InvoiceDetail::where('invoice_id', $invoiceId)
                                    ->where('platform', $value)
                                    ->exists();
                            }),

                        // Initial dummy data to create the first record
                        TextInput::make('gross_amount')
                            ->label('Start Betrag')
                            ->numeric()
                            ->default(0)
                            ->hidden(), // Hide it, we just need the row created
                    ])
                    ->action(function (array $data): void {
                        // Create the initial record so the button appears
                        $this->getOwnerRecord()->details()->create([
                            'platform' => $data['platform'],
                            'week_number' => 1, // Default to week 1 or null
                            'gross_amount' => 0,
                            'net_payout' => 0,
                        ]);
                    })
            ])
            ->actions([
                // --- THE EDIT/MANAGE BUTTON ---
                Tables\Actions\Action::make('manage')
                    ->label('Details & Wochen')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->outlined()
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->fillForm(function (Model $record) {
                        // Load ALL rows for this platform
                        $details = $record->invoice->details()
                            ->where('platform', $record->platform)
                            ->get();

                        return [
                            'platform_name' => ucfirst($record->platform),
                            'items' => $details->toArray(),
                        ];
                    })
                    ->form([
                        Section::make()->schema([
                            Grid::make(2)->schema([
                                Placeholder::make('platform_name')
                                    ->label('Plattform')
                                    ->content(fn (Get $get) => $get('platform_name'))
                                    ->extraAttributes(['class' => 'text-xl font-bold']),
                                Placeholder::make('total_summary')
                                    ->label('Gesamt Brutto')
                                    ->content(function (Get $get) {
                                        $items = collect($get('items'));
                                        return number_format($items->sum('gross_amount'), 2) . ' €';
                                    })
                                    ->extraAttributes(['class' => 'text-primary-600 font-bold']),
                            ]),
                        ]),
                        Repeater::make('items')
                            ->label('Einträge')
                            ->addActionLabel('Woche hinzufügen')
                            ->itemLabel(fn (array $state) => ($state['week_number'] ?? '-') . '. Woche: ' . ($state['gross_amount'] ?? 0) . '€')
                            ->collapsed()
                            ->cloneable()
                            ->columns(2)
                            ->schema([
                                Select::make('week_number')
                                    ->options([1=>'1. Woche', 2=>'2. Woche', 3=>'3. Woche', 4=>'4. Woche', 5=>'5. Woche'])
                                    ->required(),
                                TextInput::make('gross_amount')->numeric()->prefix('€')->default(0)->live(onBlur: true),
                                TextInput::make('cash_collected_by_driver')->numeric()->prefix('€')->default(0)->live(onBlur: true),
                                TextInput::make('tip')->numeric()->prefix('€')->default(0)->live(onBlur: true),
                                Hidden::make('id'),
                            ])
                    ])
                    ->action(function (array $data, Model $record) {
                        $invoice = $record->invoice;
                        $platform = $record->platform;

                        $existingIds = $invoice->details()->where('platform', $platform)->pluck('id')->toArray();
                        $submittedIds = [];

                        foreach ($data['items'] as $item) {
                            $attributes = [
                                'week_number' => $item['week_number'] ?? null,
                                'gross_amount' => $item['gross_amount'],
                                'cash_collected_by_driver' => $item['cash_collected_by_driver'],
                                'tip' => $item['tip'],
                                'net_payout' => $item['gross_amount'] - ($item['cash_collected_by_driver'] ?? 0),
                            ];

                            if (!empty($item['id'])) {
                                InvoiceDetail::where('id', $item['id'])->update($attributes);
                                $submittedIds[] = $item['id'];
                            } else {
                                $new = $invoice->details()->create(array_merge(['platform' => $platform], $attributes));
                                $submittedIds[] = $new->id;
                            }
                        }

                        // Delete removed items
                        $toDelete = array_diff($existingIds, $submittedIds);
                        if (!empty($toDelete)) {
                            InvoiceDetail::destroy($toDelete);
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Alle löschen')
                    ->modalDescription('Dies löscht alle Einträge für diese Plattform.')
                    ->action(function (Model $record) {
                        // Delete ALL records for this platform, not just the representative row
                        $record->invoice->details()->where('platform', $record->platform)->delete();
                    }),
            ]);
    }
}
