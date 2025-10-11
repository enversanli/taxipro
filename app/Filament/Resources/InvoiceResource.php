<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Company;
use App\Models\Invoice;
use Facades\App\Services\InvoiceCalculateService;
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
        return $form->schema([
            Section::make(__('common.invoice_info'))
                ->columns(3)
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
                        ->options(fn () => __('common.months'))
                        ->searchable()
                        ->required(),

                    TextInput::make('total_income')
                        ->label(__('common.total_income'))
                        ->prefix('€')
                        ->numeric()
                        ->default(0)
                        ->placeholder('0.00')
                        ->suffixIcon('heroicon-o-calculator')
                        ->extraInputAttributes(['class' => 'text-right'])
                        ->required(),
                ]),

            Section::make(__('common.calculations'))
                ->collapsed()
                ->collapsible()
                ->schema([
                    ViewField::make('summary')
                        ->label(__('common.summary'))
                        ->view('filament.fields.total-gross', function (Get $get) {
                            return [
                                'items' => [
                                    ['gross', (float) $get('gross') ?? $get('model.gross')],
                                    ['bar', (float) $get('bar') ?? $get('model.bar')],
                                    ['tip', (float) $get('tip') ?? $get('model.tip')],
                                    ['cash', (float) $get('cash') ?? $get('model.cash')],
                                    ['net', (float) $get('net') ?? $get('model.net')],
                                    ['driver_salary', (float) $get('driver_salary') ?? $get('model.driver_salary')],
                                ],
                                'title' => __('common.platform_calculations'),
                            ];
                        })
                        ->reactive()
                        ->columnSpan('full'),
                ]),

            Section::make(__('common.invoice_detail'))
                ->collapsible()
                ->collapsed()
                ->schema([
                    HasManyRepeater::make('details')
                        ->relationship('details')
                        ->reactive()
                        ->afterStateUpdated(fn (?array $state, Set $set) => self::calculatePlatformMetrics($state, $set))
                        ->afterStateHydrated(fn (?array $state, Set $set) => self::calculatePlatformMetrics($state, $set))
                        ->itemLabel(fn (array $state): ?string => strtoupper($state['platform'] ?? '') ?: __('common.detail'))
                        ->grid(2)
                        ->columns(4)
                        ->createItemButtonLabel(__('common.add_detail'))
                        ->defaultItems(1)
                        ->reorderable()
                        ->schema([
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
                                ->columnSpan(2),

                            TextInput::make('gross')
                                ->label(__('common.gross') . ' (€)')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->prefix('€')
                                ->step(0.01),

                            TextInput::make('tip')
                                ->label(__('common.tip') . ' (€)')
                                ->numeric()
                                ->default(0)
                                ->prefix('€')
                                ->step(0.01),

                            TextInput::make('bar')
                                ->label(__('common.bar') . ' (€)')
                                ->numeric()
                                ->default(0)
                                ->prefix('€')
                                ->step(0.01),

                            Hidden::make('cash'),

                            TextInput::make('net')
                                ->label(__('common.net') . ' (€)')
                                ->numeric()
                                ->default(0)
                                ->prefix('€')
                                ->step(0.01),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('driver.first_name')->label(__('common.first_name')),
            TextColumn::make('driver.last_name')->label(__('common.last_name')),
            TextColumn::make('month')->label(__('common.month')),
            TextColumn::make('total_income')->label(__('common.total_income'))->prefix('€'),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),

                Tables\Actions\ActionGroup::make([
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
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(fn ($record) => app(\App\Services\InvoiceExportService::class)->displaySingle($record->id)),

                    Tables\Actions\Action::make('view_pdf')
                        ->label(__('common.view_pdf'))
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->url(fn ($record) => route('invoices.pdf', ['id' => $record->id, 'view' => 'browser']))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('send_whatsapp')
                        ->label(__('common.send_via_whatsapp'))
                        ->icon('heroicon-o-chat-bubble-oval-left-ellipsis')
                        ->color('success')
                        ->url(fn ($record) => 'https://wa.me/?text=' . urlencode(
                                __("common.invoice_message", [
                                    'url' => route('invoices.pdf', ['id' => $record->id, 'view' => 'browser'])
                                ])
                            ))
                        ->openUrlInNewTab(),
                ])
                    ->label(__('common.pdf_actions'))
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

    protected static function calculatePlatformMetrics(?array $state, Set $set): void
    {
        $mainInvoice = InvoiceCalculateService::calculateMain($state);
        $detailInvoice = InvoiceCalculateService::calculateDetail($state);

        $set('gross', $mainInvoice['totalGross']);
        $set('tip', $mainInvoice['tip']);
        $set('bar', $mainInvoice['bar']);
        $set('cash', $mainInvoice['cash']);
        $set('net', $mainInvoice['net']);
        $set('driver_salary', $mainInvoice['driverSalary']);
        $set('details', $detailInvoice);
    }
}
