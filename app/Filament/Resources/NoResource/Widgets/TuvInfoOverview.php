<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;

class TuvInfoOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 2; // Placed before Insurance (usually more critical)

    protected static ?string $heading = 'TÜV Termine';
    protected static ?string $description = 'Fahrzeuge mit anstehender Hauptuntersuchung';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->whereNotNull('tuv_date')
                    // Sort by urgency (assuming 'upcoming' is calculated or stored)
                    // If you only have 'tuv_date' in DB, sort by that.
                    ->orderBy('tuv_date', 'ASC')
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                // 1. License Plate
                TextColumn::make('license_plate')
                    ->label(__('common.license_plate'))
                    ->weight(FontWeight::Bold)
                    ->fontFamily('mono')
                    ->icon('heroicon-m-truck')
                    ->searchable(),

                // 2. Last TÜV (Neutral)
                TextColumn::make('tuv_date')
                    ->label('Letzte HU') // "Last Inspection"
                    ->date('d.m.Y')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('gray')
                    ->size(TextColumn\TextColumnSize::Small),

                // 3. Next Due Date (Bold & Urgent)
                TextColumn::make('upcoming_tuv_date')
                    ->label('Nächste HU') // "Next Inspection"
                    ->date('d.m.Y')
                    ->weight('bold')
                    ->color(fn (Vehicle $record) => match (true) {
                        Carbon::parse($record->upcoming_tuv_date)->isPast() => 'danger',
                        Carbon::parse($record->upcoming_tuv_date)->lessThan(now()->addMonth()) => 'warning',
                        default => 'success',
                    })
                    ->icon(fn (Vehicle $record) => match (true) {
                        Carbon::parse($record->upcoming_tuv_date)->isPast() => 'heroicon-m-exclamation-triangle',
                        Carbon::parse($record->upcoming_tuv_date)->lessThan(now()->addMonth()) => 'heroicon-m-clock',
                        default => 'heroicon-m-check-badge',
                    }),

                // 4. Status Badge
                TextColumn::make('tuv_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'danger' => 'expired',
                        'warning' => 'soon',
                        'success' => 'valid',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Erneuern')
                    ->icon('heroicon-m-arrow-path')
                    ->modalHeading('TÜV Datum aktualisieren')
                    ->form([
                        // ERROR WAS HERE: You used Tables\...\Grid instead of Forms\...\Grid
                        \Filament\Forms\Components\Grid::make(1)->schema([

                            \Filament\Forms\Components\DatePicker::make('tuv_date')
                                ->label('Neues TÜV Datum')
                                ->helperText('Datum der neuen Hauptuntersuchung')
                                ->required(),

                        ])
                    ])
                    ->mutateRecordDataUsing(function (array $data): array {
                        if (isset($data['tuv_date'])) {
                            $data['tuv_date'] = \Carbon\Carbon::parse($data['tuv_date'])
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
