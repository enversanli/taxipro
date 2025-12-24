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
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(__('common.invoice_info'))
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([
                                // Admin değilse şirket seçimini gizle
                                auth()->user()->role === 'admin'
                                    ? Select::make('company_id')
                                    ->label(__('common.company'))
                                    ->options(Company::all()->pluck('name', 'id'))
                                    ->required()
                                    : Hidden::make('company_id')->default(auth()->user()->company_id),

                                Select::make('driver_id')
                                    ->label(__('common.driver'))
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                    ->required()
                                    ->live()
                                    // Şoför seçildiğinde Driver modelindeki varsayılan yüzdeyi getir
                                    ->afterStateUpdated(fn ($state, Set $set) =>
                                    $set('salary_percentage', \App\Models\Driver::find($state)?->default_percentage ?? 50)
                                    ),

                                Grid::make(2)->schema([
                                    Select::make('month')
                                        ->label(__('common.month'))
                                        ->options(fn () => __('common.months'))
                                        ->required(),

                                    TextInput::make('year')
                                        ->label(__('common.year'))
                                        ->default(now()->format('Y'))
                                        ->required(),
                                ]),
                            ]),

                        Section::make(__('common.invoice_detail'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                HasManyRepeater::make('details')
                                    ->relationship('details')
                                    ->collapsible()
                                    ->collapsed()
                                    ->cloneable() // Kullanıcılar haftalık Uber verilerini kopyalamayı sever
                                    ->itemLabel(fn ($state) => self::getRepeaterLabel($state))
                                    ->live() // Her satır hareketinde ana metrikleri güncelle
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('platform')
                                                ->label(__('common.platform'))
                                                ->options(['uber' => 'Uber', 'bolt' => 'Bolt', 'bliq' => 'Bliq', 'freenow' => 'Free Now'])
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, Get $get) {
                                                    if ($get('platform') !== 'uber') $set('week_number', null);
                                                    self::calculateDetailItemMetrics($set, $get);
                                                }),

                                            Select::make('week_number')
                                                ->label(__('common.week'))
                                                ->options([1=>'1.Woche', 2=>'2.Woche', 3=>'3.Woche', 4=>'4.Woche', 5=>'5.Woche'])
                                                ->visible(fn (Get $get) => $get('platform') === 'uber')
                                                ->required(fn (Get $get) => $get('platform') === 'uber'),

                                            TextInput::make('gross_amount')
                                                ->label(__('common.gross'))
                                                ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('cash_collected_by_driver')
                                                ->label(__('common.bar'))
                                                ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                                ->maxValue(fn (Get $get) => (float) $get('gross_amount')) // Validasyon: Nakit > Brüt olamaz
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('tip')
                                                ->label(__('common.tip'))
                                                ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                                ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateDetailItemMetrics($set, $get)),

                                            TextInput::make('net_payout')
                                                ->label(__('common.net'))
                                                ->readOnly()->prefix('€')->extraInputAttributes(['class' => 'bg-gray-100 font-medium']),
                                        ]),
                                    ]),
                            ]),
                    ]),

                // --- SAĞ TARAF: ÖZET VE SONUÇLAR ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('Kasa & Mutabakat'))
                            ->description('Barbestand hesabı')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextInput::make('taxameter_total')
                                    ->label(__('common.taximetre_total'))
                                    ->numeric()->prefix('€')->required()->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                TextInput::make('sumup_payments')
                                    ->label('SumUp / Kart')
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                TextInput::make('coupon_payments')
                                    ->label('Kupon / Cari')
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                TextInput::make('expected_cash')
                                    ->label(__('common.expected_cash'))
                                    ->readOnly()
                                    ->prefix('€')
                                    ->extraInputAttributes(['class' => 'text-right text-2xl font-bold text-danger-600 bg-danger-50']),
                            ]),

                        Section::make(__('Hakediş & Kesintiler'))
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                TextInput::make('salary_percentage')
                                    ->label('Maaş Oranı %')
                                    ->numeric()->default(50)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                TextInput::make('deductions_sb')
                                    ->label('SB (Sigorta Muafiyet)')
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                TextInput::make('cash_withdrawals')
                                    ->label('Ay İçi Avanslar')
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($set, $get)),

                                TextInput::make('net_salary')
                                    ->label(__('common.driver_salary'))
                                    ->readOnly()
                                    ->prefix('€')
                                    ->extraInputAttributes(['class' => 'text-right text-2xl font-bold text-success-600 bg-success-50']),
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
    protected static function getRepeaterLabel($state): string
    {
        if (!isset($state['platform'])) return __('common.detail');
        $label = ucfirst($state['platform']);
        if ($state['platform'] === 'uber' && !empty($state['week_number'])) {
            $label .= " ({$state['week_number']}. Woche)";
        }
        return $label;
    }

    protected static function calculateMainMetrics(Set $set, Get $get): void
    {
        $taxameter = (float) $get('taxameter_total');
        $sumup = (float) $get('sumup_payments');
        $coupons = (float) $get('coupon_payments');
        $withdrawals = (float) $get('cash_withdrawals');

        $details = $get('details') ?? [];
        $totalAppGross = 0;
        $totalAppBar = 0;

        foreach ($details as $item) {
            $totalAppGross += (float) ($item['gross_amount'] ?? 0);
            $totalAppBar += (float) ($item['cash_collected_by_driver'] ?? 0);
        }

        // Kasa Hesabı: Taksimetre - (App Kartlılar) - Sumup - Kupon - Avans
        $appDigitalPayments = $totalAppGross - $totalAppBar;
        $expectedCash = $taxameter - $appDigitalPayments - $sumup - $coupons - $withdrawals;

        // Maaş Hesabı
        $percentage = (int) $get('salary_percentage');
        $sb = (float) $get('deductions_sb');
        $totalGross = $taxameter + $totalAppGross;
        $salary = ($totalGross * ($percentage / 100)) - $sb;

        $set('expected_cash', round($expectedCash, 2));
        $set('net_salary', round($salary, 2));
    }

    protected static function calculateDetailItemMetrics(Set $set, Get $get): void
    {
        $platform = $get('platform');
        $gross = (float) $get('gross_amount');

        $rates = ['uber' => 0.25, 'bolt' => 0.20, 'bliq' => 0.15, 'freenow' => 0.15];
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
