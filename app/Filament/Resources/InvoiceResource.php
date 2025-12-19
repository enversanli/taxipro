<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Company;
use App\Models\Invoice;
use Facades\App\Services\InvoiceCalculateService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Set;
use Filament\Forms\Get;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';
    protected static ?string $navigationLabel = 'Invoices';
    protected static ?string $pluralModelLabel = 'Invoices';

    public static function getPluralModelLabel(): string
    {
        return __('common.invoices');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.invoices');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3) // Create a 3-column grid for the layout
            ->schema([
                // --- LEFT SIDE: INPUTS (Takes up 2 of 3 columns) ---
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('common.invoice_info'))
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([
                                auth()->user()->role === 'admin'
                                    ? Select::make('company_id')
                                    ->label(__('common.company'))
                                    ->options(Company::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    : Hidden::make('company_id')
                                    ->default(auth()->user()->company_id),

                                Select::make('driver_id')
                                    ->label(__('common.driver'))
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Group::make()->columns(2)->schema([
                                    Select::make('month')
                                        ->label(__('common.month'))
                                        ->options(fn () => __('common.months'))
                                        ->searchable()
                                        ->required(),

                                    TextInput::make('year')
                                        ->label(__('common.year'))
                                        ->default(now()->format('Y'))
                                        ->disabled()
                                        ->dehydrated()
                                        ->required(),
                                ])->columnSpan(1),
                            ]),

                        Section::make(__('common.invoice_detail'))
                            ->description('Add earnings for each platform below.')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                HasManyRepeater::make('details')
                                    ->relationship('details')
                                    // Make it behave like an accordion/popup to save space
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => isset($state['platform']) ? ucfirst($state['platform']) : __('common.detail'))
                                    ->createItemButtonLabel(__('common.add_detail'))
                                    ->grid(1) // Stack them vertically for a cleaner look
                                    ->reactive()
                                    // Update MAIN totals when repeater changes (add/remove)
                                    ->afterStateUpdated(fn (?array $state, Set $set) => self::calculateMainMetrics($state, $set))
                                    ->afterStateHydrated(fn (?array $state, Set $set) => self::calculateMainMetrics($state, $set))
                                    ->schema([
                                        Group::make()->columns(2)->schema([
                                            Select::make('platform')
                                                ->label(__('common.platform'))
                                                ->options([
                                                    'uber' => 'Uber',
                                                    'bolt' => 'Bolt',
                                                    'bliq' => 'Bliq',
                                                    'freenow' => 'Free Now',
                                                ])
                                                ->required()
                                                ->searchable()
                                                ->live(debounce: '500ms')
                                                ->afterStateUpdated(fn (Set $set, Get $get, ?string $state) => self::calculateDetailItemMetrics($set, $get, $state)),

                                            TextInput::make('gross')
                                                ->label(__('common.gross'))
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->prefix('€')
                                                ->step(0.01)
                                                ->live(debounce: '500ms')
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('tip')
                                                ->label(__('common.tip'))
                                                ->numeric()
                                                ->default(0)
                                                ->prefix('€')
                                                ->step(0.01)
                                                ->live(debounce: '500ms')
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('bar')
                                                ->label(__('common.bar'))
                                                ->numeric()
                                                ->default(0)
                                                ->prefix('€')
                                                ->step(0.01)
                                                ->live(debounce: '500ms')
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),
                                        ]),

                                        // Highlight the calculated results within the repeater
                                        Section::make('Result')
                                            ->compact()
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('net')
                                                    ->label(__('common.net'))
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->prefix('€')
                                                    ->extraInputAttributes(['class' => 'font-bold bg-gray-50']),

                                                Hidden::make('cash'),
                                            ]),
                                    ]),
                            ]),
                    ]),

                // --- RIGHT SIDE: SUMMARY (Takes up 1 of 3 columns) ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('common.summary'))
                            ->icon('heroicon-o-calculator')
                            ->description('Real-time calculations')
                            ->schema([
                                TextInput::make('total_income')
                                    ->label(__('common.total_income'))
                                    ->prefix('€')
                                    ->numeric()
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'text-right text-xl font-bold text-primary-600'])
                                    ->columnSpanFull(),

                                TextInput::make('net')
                                    ->label(__('common.net'))
                                    ->prefix('€')
                                    ->numeric()
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'text-right text-xl font-bold text-primary-600'])
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('gross')
                                            ->label(__('common.gross'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->readOnly(),

                                        TextInput::make('bar')
                                            ->label(__('common.bar'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->readOnly(),

                                        TextInput::make('tip')
                                            ->label(__('common.tip'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->readOnly(),

                                        TextInput::make('cash')
                                            ->label(__('common.cash'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->readOnly(),

                                        TextInput::make('driver_salary')
                                            ->label(__('common.driver_salary'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->readOnly()
                                            ->extraInputAttributes(['class' => 'font-bold']),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('driver.first_name')
                ->label(__('common.driver'))
                ->formatStateUsing(fn ($record) => $record->driver->first_name . ' ' . $record->driver->last_name)
                ->sortable()
                ->searchable(['first_name', 'last_name']),

            TextColumn::make('month')
                ->label(__('common.month'))
                ->badge(),

            TextColumn::make('total_income')
                ->label(__('common.total_income'))
                ->money('eur')
                ->weight('bold')
                ->sortable(),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label(__('common.month'))
                    ->options(fn () => __('common.months')),
                Tables\Filters\SelectFilter::make('driver_id')
                    ->label(__('common.driver'))
                    ->relationship('driver', 'first_name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\Action::make('export')
                        ->label(__('common.export'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $service = app(InvoiceExportService::class);
                            return $service->exportSingle($record->id);
                        }),

                    Tables\Actions\Action::make('download_pdf')
                        ->label(__('common.download_pdf'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('warning')
                        ->action(fn ($record) => app(\App\Services\InvoiceExportService::class)->displaySingle($record->id)),

                    Tables\Actions\Action::make('send_whatsapp')
                        ->label('WhatsApp')
                        ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                        ->color('success')
                        ->url(fn ($record) => 'https://wa.me/?text=' . urlencode(
                                __("common.invoice_message", [
                                    'url' => route('invoices.pdf', ['id' => $record->id, 'view' => 'browser'])
                                ])
                            ))
                        ->openUrlInNewTab(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriverInvoices::route('/'),
            'create' => Pages\CreateDriverInvoice::route('/create'),
            'edit' => Pages\EditDriverInvoice::route('/{record}/edit'),
            'import' => Pages\ImportInvoice::route('/import-invoice'),
        ];
    }

    // --- LOGIC FIX: SEPARATED CALCULATIONS TO PREVENT DATA LOSS ---

    /**
     * Calculates ONLY the aggregated totals for the main invoice.
     * Does NOT touch the details array.
     *
     * COMMON SCREEN CALCULATION
     */
    protected static function calculateMainMetrics(?array $state, Set $set): void
    {
        $mainInvoice = InvoiceCalculateService::calculateMain($state);

        $set('total_income', $mainInvoice['totalGross']);
        $set('gross', $mainInvoice['totalGross']);
        $set('tip', $mainInvoice['tip']);
        $set('bar', $mainInvoice['bar']);

        // RECALCULATE Cash to include Tip explicitly: Gross + Tip - Bar
        // This ensures the summary matches the line items where $cash = $gross + $tip - $bar
        // $calculatedCash = $mainInvoice['totalGross'] + $mainInvoice['tip'] - $mainInvoice['bar'];

        $set('net', $mainInvoice['net']);
        //$set('driver_salary', $mainInvoice['driverSalary']);
        $set('driver_salary', 0);
        $set('cash', 0);
    }

    /**
     * Calculates metrics ONLY for the specific item being edited.
     */
    protected static function calculateDetailItemMetrics(Set $set, Get $get, ?string $platform = null): void
    {
        $platform = $platform ?? $get('platform');
        $gross = (float)($get('gross') ?? 0);
        $tip = (float)($get('tip') ?? 0);
        $bar = (float)($get('bar') ?? 0);

        // Fetch config inside function to avoid dependency injection issues in static context
        $commissionRates = config('platform');
        $commissionRate = (float)($commissionRates[$platform]['commission'] ?? 0);

        $cash = $gross + $tip - $bar;
        $net = $gross * (1 - $commissionRate);

        $set('cash', 5);
        $set('net', round($net, 2));
    }
}
