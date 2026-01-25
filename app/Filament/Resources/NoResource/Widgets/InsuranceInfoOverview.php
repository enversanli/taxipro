<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;

class InsuranceInfoOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Versicherungsstatus';
    protected static ?string $description = 'Übersicht der Laufzeiten und Fristen';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->whereNotNull('insurance_date')
                    ->orderBy('insurance_date', 'ASC')
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('license_plate')
                    ->label(__('common.license_plate'))
                    ->weight(FontWeight::Bold)
                    ->fontFamily('mono')
                    ->icon('heroicon-m-truck')
                    ->searchable(),

                TextColumn::make('insurance_date')
                    ->label('Versichert seit')
                    ->date('d.m.Y')
                    ->icon('heroicon-m-calendar')
                    ->color('gray')
                    ->size(TextColumn\TextColumnSize::Small),

                TextColumn::make('upcoming_insurance_date')
                    ->label('Ablaufdatum')
                    ->date('d.m.Y')
                    ->weight('bold')
                    ->color(fn (Vehicle $record) => match (true) {
                        Carbon::parse($record->upcoming_insurance_date)->isPast() => 'danger',
                        Carbon::parse($record->upcoming_insurance_date)->lessThan(now()->addMonth()) => 'warning',
                        default => 'success',
                    })
                    ->icon(fn (Vehicle $record) => match (true) {
                        Carbon::parse($record->upcoming_insurance_date)->isPast() => 'heroicon-m-exclamation-circle',
                        Carbon::parse($record->upcoming_insurance_date)->lessThan(now()->addMonth()) => 'heroicon-m-clock',
                        default => 'heroicon-m-shield-check',
                    }),

                TextColumn::make('insurance_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'danger' => 'expired',
                        'warning' => 'soon',
                        'success' => 'active',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Verlängern')
                    ->icon('heroicon-m-arrow-path')
                    ->modalHeading('Versicherung erneuern')
                    ->form([
                        // FIXED: Use Forms\Components\Grid, not Tables\...\Grid
                        \Filament\Forms\Components\Grid::make(1)->schema([
                            \Filament\Forms\Components\DatePicker::make('insurance_date')
                                ->label('Neues Startdatum')
                                ->required(),
                        ])
                    ])
                    ->mutateRecordDataUsing(function (array $data): array {
                        if (isset($data['insurance_date'])) {
                            $data['insurance_date'] = Carbon::parse($data['insurance_date'])
                                ->addYear()
                                ->toDateTimeString();
                        }
                        return $data;
                    })
                    ->button()
                    ->size('xs')
                    ->color('gray'),
            ]);
    }
}
