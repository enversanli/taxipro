<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\DetailsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\ExpensesRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers\VouchersRelationManager;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Invoice;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';
    protected static ?string $activeNavigationIcon = 'heroicon-s-document-currency-euro';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('common.invoice');
    }

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
            ->columns(12)
            ->schema([
                Group::make()
                    ->columnSpan(['lg' => 8])
                    ->schema([
                        Section::make()
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('company_id')
                                        ->label(__('common.company'))
                                        ->options(Company::all()->pluck('name', 'id'))
                                        ->required()
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-building-office')
                                        ->visible(fn() => auth()->user()->role === 'admin')
                                        ->default(auth()->user()->company_id),

                                    Hidden::make('company_id')
                                        ->default(auth()->user()->company_id)
                                        ->visible(fn() => auth()->user()->role !== 'admin'),

                                    Select::make('driver_id')
                                        ->label(__('common.driver'))
                                        ->relationship('driver', 'first_name')
                                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-user')
                                        ->afterStateUpdated(fn($state, Set $set) => $set('salary_percentage', Driver::find($state)?->default_percentage ?? 50)),
                                ]),

                                Grid::make(2)->schema([
                                    Select::make('month')
                                        ->label(__('common.month'))
                                        ->options(__('common.months'))
                                        ->required()
                                        ->native(false),
                                    TextInput::make('year')
                                        ->label(__('common.year'))
                                        ->default(now()->format('Y'))
                                        ->numeric()
                                        ->required(),
                                ]),
                            ]),

                        // --- 2. REVENUE (Money In) ---
                        Section::make(__('Einnahmen & Umsatz'))
                            ->icon('heroicon-o-banknotes')
                            ->description('Erfassung der Taximeter- und Kartenzahlungen.')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('taxameter_total')
                                        ->label(__('common.taximetre_total'))
                                        ->numeric()
                                        ->prefix('€')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->extraInputAttributes(['class' => 'text-lg'])
                                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                    TextInput::make('sumup_payments')
                                        ->label('SumUp / Kartenzahlung')
                                        ->numeric()
                                        ->prefix('€')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateMainMetrics($set, $get)),
                                ]),
                            ]),

                        // --- 3. DEDUCTIONS (Money Out) ---
                        Section::make(__('Abzüge & Vorschüsse'))
                            ->icon('heroicon-o-minus-circle')
                            ->collapsed()
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('deductions_sb')
                                        ->label('Selbstbeteiligung / Schaden')
                                        ->numeric()
                                        ->prefix('€')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                    TextInput::make('cash_withdrawals')
                                        ->label('Barentnahme (Avans)')
                                        ->numeric()
                                        ->prefix('€')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                    // Placeholder for Coupon info since we moved details to relations
                                    Placeholder::make('info')
                                        ->label('Hinweis')
                                        ->content('Kupon-Details werden unten im Tab "Gutscheine" verwaltet.'),
                                ]),
                            ]),
                    ]),

                // =================================================================================================
                // RIGHT COLUMN (Results / Dashboard) - Spans 4 of 12
                // =================================================================================================
                Group::make()
                    ->columnSpan(['lg' => 4])
                    ->schema([
                        Section::make('Abrechnung')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                // SALARY PERCENTAGE
                                TextInput::make('salary_percentage')
                                    ->label('Provisionssatz (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(50)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                // --- RESULT 1: NET SALARY ---
                                Section::make()
                                    ->extraAttributes(['class' => 'bg-green-50 border border-green-200 rounded-lg'])
                                    ->schema([
                                        TextInput::make('net_salary')
                                            ->label(__('common.driver_salary'))
                                            ->readOnly()
                                            ->prefix('€')
                                            ->extraInputAttributes([
                                                'class' => 'text-right text-3xl font-black text-green-700 bg-transparent border-none shadow-none',
                                                'style' => '--tw-ring-color: transparent;', // Removes focus ring
                                            ]),
                                        Placeholder::make('salary_help')
                                            ->label('')
                                            ->content(new HtmlString('<span class="text-xs text-green-600">Auszuzahlender Betrag an Fahrer</span>')),
                                    ]),

                                // --- RESULT 2: EXPECTED CASH ---
                                Section::make()
                                    ->extraAttributes(['class' => 'bg-red-50 border border-red-200 rounded-lg mt-4'])
                                    ->schema([
                                        TextInput::make('expected_cash')
                                            ->label(__('common.expected_cash'))
                                            ->readOnly()
                                            ->prefix('€')
                                            ->extraInputAttributes([
                                                'class' => 'text-right text-3xl font-black text-red-700 bg-transparent border-none shadow-none',
                                                'style' => '--tw-ring-color: transparent;',
                                            ]),
                                        Placeholder::make('cash_help')
                                            ->label('')
                                            ->content(new HtmlString('<span class="text-xs text-red-600">Bargeld, das abgegeben werden muss</span>')),
                                    ]),
                            ]),

                        Section::make('Info')
                            ->schema([
                                Placeholder::make('note')
                                    ->hiddenLabel()
                                    ->content('Bitte speichern Sie die Rechnung, um Plattform-Details (Bolt/Uber) und Gutscheine hinzuzufügen.'),
                            ]),
                    ]),
            ]);
    }

    /**
     * Live Calculation Logic.
     * Note: Since 'details' are now in a RelationManager, we cannot calculate
     * the App totals live here easily without saving first.
     * This method calculates the base values available in the form.
     */
    protected static function calculateMainMetrics(Set $set, Get $get): void
    {
        $taxameter = (float)$get('taxameter_total');
        $sumup = (float)$get('sumup_payments');
        $withdrawals = (float)$get('cash_withdrawals');
        $sb = (float)$get('deductions_sb');
        $percentage = (int)$get('salary_percentage');

        // Note: To get the App Totals (Uber/Bolt) live, we would need to query the DB
        // using the record ID, but on 'Create' page the record ID is null.
        // For now, we calculate based on Inputs + a placeholder for App data if stored in hidden fields.

        // Basic Salary Calculation (Turnover * %) - SB
        // Ideally, Turnover = Taxameter + App Gross.
        // If you want App Gross included, you might need a hidden field 'total_app_gross' updated by the relation manager later.

        $totalGross = $taxameter; // + $appGross (from DB)

        $salary = ($totalGross * ($percentage / 100)) - $sb;

        // Cash Calculation
        // Expected Cash = Taxameter - (App Digital Payments) - SumUp - Withdrawals
        $expectedCash = $taxameter - $sumup - $withdrawals;

        $set('expected_cash', number_format($expectedCash, 2, '.', ''));
        $set('net_salary', number_format($salary, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('driver.first_name')
                    ->label(__('common.driver'))
                    ->formatStateUsing(fn($record) => "{$record->driver->first_name} {$record->driver->last_name}")
                    ->description(fn($record) => $record->driver->phone)
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('month')
                    ->label(__('common.month'))
                    ->formatStateUsing(fn($state, $record) => $state . ' / ' . $record->year)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('taxameter_total')
                    ->label('Umsatz')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('expected_cash')
                    ->label(__('common.expected_cash'))
                    ->money('EUR')
                    ->color('danger')
                    ->weight('bold')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('net_salary')
                    ->label(__('common.driver_salary'))
                    ->money('EUR')
                    ->color('success')
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('driver_id')
                    ->relationship('driver', 'first_name')
                    ->label('Fahrer'),
                Tables\Filters\SelectFilter::make('month')
                    ->options(__('common.months'))
                    ->label('Monat'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\Action::make('pdf')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->url(fn(Invoice $record) => route('invoices.pdf', $record))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DetailsRelationManager::class,   // Bolt/Uber
            VouchersRelationManager::class,  // Kuponlar
            ExpensesRelationManager::class,  // Ausgaben
        ];
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
}
