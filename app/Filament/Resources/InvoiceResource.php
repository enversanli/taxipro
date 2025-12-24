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
                // --- SOL TARAF: GİRİŞ ALANLARI ---
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
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                HasManyRepeater::make('details')
                                    ->relationship('details')
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        if (!isset($state['platform'])) {
                                            return __('common.detail');
                                        }

                                        $label = ucfirst($state['platform']);

                                        // Eğer platform uber ise ve hafta seçilmişse yanına ekle
                                        if ($state['platform'] === 'uber' && !empty($state['week_number'])) {
                                            $label .= " - {$state['week_number']}. " . __('common.week');
                                        }

                                        return $label;
                                    })                                    ->live()
                                    ->afterStateUpdated(fn (?array $state, Set $set, Get $get) => self::calculateMainMetrics($state, $set, $get))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('platform')
                                                ->label(__('common.platform'))
                                                ->options(['uber' => 'Uber', 'bolt' => 'Bolt', 'bliq' => 'Bliq', 'freenow' => 'Free Now'])
                                                ->required()->live()
                                                ->afterStateUpdated(function (Set $set, Get $get) {
                                                    if ($get('platform') !== 'uber') {
                                                        $set('week_number', null);
                                                    }
                                                    self::calculateDetailItemMetrics($set, $get);
                                                }),

                                            Select::make('week_number')
                                                ->label(__('common.week'))
                                                ->options([
                                                    1 => '1. ' . __('common.week'),
                                                    2 => '2. ' . __('common.week'),
                                                    3 => '3. ' . __('common.week'),
                                                    4 => '4. ' . __('common.week'),
                                                    5 => '5. ' . __('common.week'),
                                                ])
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

                // --- SAĞ TARAF: ÖZET VE HAKEDİŞ ---
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make(__('common.summary'))
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                TextInput::make('taxameter_total')
                                    ->label(__('common.taximetre_total'))
                                    ->numeric()->prefix('€')->required()->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('sumup_payments')
                                    ->label('SumUp')
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                                TextInput::make('expected_cash')
                                    ->label(__('common.expected_cash'))
                                    ->readOnly()->prefix('€')
                                    ->extraInputAttributes(['class' => 'text-right text-xl font-bold text-danger-600']),
                            ]),

                        Section::make(__('Sürücü Hesaplamaları'))
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                TextInput::make('net_salary')
                                    ->label(__('common.driver_salary'))
                                    ->readOnly()->prefix('€')
                                    ->extraInputAttributes(['class' => 'font-bold text-success-600']),

                                TextInput::make('deductions_sb')
                                    ->label(__('common.deductions_sb'))
                                    ->numeric()->prefix('€')->default(0)->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateMainMetrics($get('details'), $set, $get)),

                            ]),
                    ]),
            ]);
    }

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
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')->options(fn () => __('common.months')),
                Tables\Filters\SelectFilter::make('driver_id')->relationship('driver', 'first_name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('download_pdf')
                        ->label(__('common.download_pdf'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn ($record) => app(\App\Services\InvoiceExportService::class)->displaySingle($record->id)),
                ])
            ]);
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

    protected static function calculateMainMetrics(?array $state, Set $set, Get $get): void
    {
        $taxameter = (float)($get('taxameter_total') ?? 0);
        $sumup = (float)($get('sumup_payments') ?? 0);
        $totalAppGross = 0;
        $totalAppBar = 0;

        if ($state) {
            foreach ($state as $item) {
                $totalAppGross += (float)($item['gross_amount'] ?? 0);
                $totalAppBar += (float)($item['cash_collected_by_driver'] ?? 0);
            }
        }

        // Kasa Kontrolü: Genel Kasa - (Uygulama Kartlı Ödemeleri) - SumUp
        $appCardPayments = $totalAppGross - $totalAppBar;
        $expectedCash = $taxameter - $appCardPayments - $sumup;

        $sb = (float)($get('deductions_sb') ?? 0);
        $percentage = (int)($get('salary_percentage') ?? 50);

        // Hakediş Hesabı: (Tüm Brüt Kazançlar * Yüzde) - Kesintiler
        $totalGrossForSalary = $taxameter + $totalAppGross;
        $salary = ($totalGrossForSalary * ($percentage / 100)) - $sb;

        $set('expected_cash', round($expectedCash, 2));
        $set('net_salary', round($salary, 2));
    }

    protected static function calculateDetailItemMetrics(Set $set, Get $get): void
    {
        $platform = $get('platform');
        $gross = (float)($get('gross_amount') ?? 0);

        $rates = config('platform.commissions', [
            'uber' => 0.25, 'bolt' => 0.20, 'freenow' => 0.15, 'bliq' => 0.15
        ]);

        $rate = $rates[$platform] ?? 0.20;
        $set('net_payout', round($gross * (1 - $rate), 2));
    }
}
