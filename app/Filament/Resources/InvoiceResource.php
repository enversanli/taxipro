<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Company;
use App\Models\Invoice;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

    public static function getPluralModelLabel(): string { return __('common.invoices'); }
    public static function getNavigationGroup(): ?string { return __('common.invoices'); }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                // --- SOL TARAF: VERİ GİRİŞ ALANLARI ---
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
                                    ->searchable()->required()
                                    : Hidden::make('company_id')->default(auth()->user()->company_id),

                                Select::make('driver_id')
                                    ->label(__('common.driver'))
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                    ->searchable()->preload()->required(),

                                Group::make()->columns(2)->schema([
                                    Select::make('month')
                                        ->label(__('common.month'))
                                        ->options(fn () => __('common.months'))
                                        ->required(),
                                    TextInput::make('year')
                                        ->label(__('common.year'))
                                        ->default(now()->format('Y'))
                                        ->disabled()->dehydrated()->required(),
                                ])->columnSpan(1),
                            ]),

                        Section::make(__('common.invoice_detail'))
                            ->description('Platform bazlı kazançlar (Uber, Bolt vb.)')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                HasManyRepeater::make('details')
                                    ->relationship('details')
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        if (!isset($state['platform'])) return __('common.detail');
                                        $label = ucfirst($state['platform']);
                                        if ($state['platform'] === 'uber' && !empty($state['week_number'])) {
                                            $label .= " - {$state['week_number']}. " . __('common.week');
                                        }
                                        return $label;
                                    })
                                    ->live()
                                    ->afterStateUpdated(fn (?array $state, Set $set, Get $get) => self::calculateMainMetrics($state, $set, $get))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('platform')
                                                ->label(__('common.platform'))
                                                ->options(['uber' => 'Uber', 'bolt' => 'Bolt', 'bliq' => 'Bliq', 'freenow' => 'Free Now'])
                                                ->required()->live()
                                                ->afterStateUpdated(function (Set $set, Get $get) {
                                                    if ($get('platform') !== 'uber') $set('week_number', null);
                                                    self::calculateDetailItemMetrics($set, $get);
                                                }),

                                            Select::make('week_number')
                                                ->label(__('common.week'))
                                                ->options([1 => '1. Hafta', 2 => '2. Hafta', 3 => '3. Hafta', 4 => '4. Hafta', 5 => '5. Hafta'])
                                                ->visible(fn (Get $get) => $get('platform') === 'uber')
                                                ->required(fn (Get $get) => $get('platform') === 'uber'),

                                            TextInput::make('gross_amount')
                                                ->label(__('common.gross'))
                                                ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('cash_collected_by_driver')
                                                ->label(__('common.bar'))
                                                ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('tip')
                                                ->label(__('common.tip'))
                                                ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('net_payout')
                                                ->label(__('common.net'))
                                                ->readOnly()->prefix('€')->extraInputAttributes(['class' => 'bg-gray-50']),
                                        ]),
                                    ]),
                            ]),
                    ]),

                // --- SAĞ TARAF: ÖZET VE KASA KONTROLÜ ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Kasa Kontrolü (Barbestand)'))
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextInput::make('taxameter_total')
                                    ->label(__('common.taximetre_total')) // Genel Kasa (Taksimetre)
                                    ->numeric()->prefix('€')->required()->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('sumup_payments')
                                    ->label('SumUp (Kartlı Ödemeler)')
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('coupon_payments')
                                    ->label('Kupon/Cari Sürüşler') // Rechnungsfahrten
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('expected_cash')
                                    ->label(__('common.expected_cash')) // Teslim Edilmesi Gereken Nakit
                                    ->readOnly()->prefix('€')
                                    ->extraInputAttributes(['class' => 'text-right text-xl font-bold text-danger-600 bg-danger-50']),
                            ]),

                        Section::make(__('Sürücü Maaş Hesabı'))
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                TextInput::make('salary_percentage')
                                    ->label('Maaş Oranı (%)')
                                    ->default(50)->numeric()->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('deductions_sb')
                                    ->label(__('common.deductions_sb')) // Hasar Kesintisi (SB)
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('cash_withdrawals')
                                    ->label('Alınan Avanslar') // Barentnahmen
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('net_salary')
                                    ->label(__('common.driver_salary'))
                                    ->readOnly()->prefix('€')
                                    ->extraInputAttributes(['class' => 'text-right text-xl font-bold text-success-600 bg-success-50']),
                            ]),
                    ]),
            ]);
    }

    // --- TABLO GÖRÜNÜMÜ ---
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('driver.first_name')
                ->label(__('common.driver'))
                ->formatStateUsing(fn ($record) => "{$record->driver->first_name} {$record->driver->last_name}")
                ->sortable()->searchable(),

            TextColumn::make('month')->label(__('common.month'))->badge(),
            TextColumn::make('taxameter_total')->label(__('common.taximetre_total'))->money('eur'),
            TextColumn::make('expected_cash')->label(__('common.expected_cash'))->money('eur')->color('danger')->weight('bold'),
            TextColumn::make('net_salary')->label(__('common.driver_salary'))->money('eur')->color('success'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('driver_id')->relationship('driver', 'first_name'),
            ]);
    }

    // --- HESAPLAMA MANTIKLARI ---

    protected static function calculateMainMetrics(?array $state, Set $set, Get $get): void
    {
        $taxameter = (float)($get('taxameter_total') ?? 0);
        $sumup = (float)($get('sumup_payments') ?? 0);
        $coupons = (float)($get('coupon_payments') ?? 0);
        $withdrawals = (float)($get('cash_withdrawals') ?? 0);

        $totalAppGross = 0;
        $totalAppBar = 0;

        if ($state) {
            foreach ($state as $item) {
                $totalAppGross += (float)($item['gross_amount'] ?? 0);
                $totalAppBar += (float)($item['cash_collected_by_driver'] ?? 0);
            }
        }

        // --- 1. KASA HESABI (Barbestand / Ermitteltes Bargeld) ---
        // Uygulama üzerinden kartla ödenen miktar = Toplam Brüt - Şoförün Nakit Aldığı
        $appDigitalPayments = $totalAppGross - $totalAppBar;

        // Formül: Taksimetre - Uygulama Kartlıları - SumUp - Kuponlar - Avanslar
        $expectedCash = $taxameter - $appDigitalPayments - $sumup - $coupons - $withdrawals;

        // --- 2. MAAŞ HESABI (Lohnabrechnung) ---
        $sb = (float)($get('deductions_sb') ?? 0);
        $percentage = (int)($get('salary_percentage') ?? 50);

        // Toplam ciro üzerinden yüzde hesaplanır
        $totalGrossForSalary = $taxameter + $totalAppGross;
        $salary = ($totalGrossForSalary * ($percentage / 100)) - $sb;

        $set('expected_cash', round($expectedCash, 2));
        $set('net_salary', round($salary, 2));
    }

    protected static function calculateDetailItemMetrics(Set $set, Get $get): void
    {
        $platform = $get('platform');
        $gross = (float)($get('gross_amount') ?? 0);

        // Komisyon oranları (Berlin Standardı)
        $rates = ['uber' => 0.25, 'bolt' => 0.20, 'freenow' => 0.15, 'bliq' => 0.15];
        $rate = $rates[$platform] ?? 0.20;

        $set('net_payout', round($gross * (1 - $rate), 2));
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
