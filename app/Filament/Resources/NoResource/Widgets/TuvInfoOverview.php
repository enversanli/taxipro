<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TuvInfoOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->where(function ($query) {
                        $query->whereNotNull('tuv_date');
                    })
                    ->orderBy('tuv_date', 'ASC')
                    ->take(3)
            )
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Plate')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tuv_date')
                    ->label('TÜV Date')
                    ->date('d M Y')
                    ->color('success'),

                Tables\Columns\TextColumn::make('upcoming_tuv_date')
                    ->label('Upcoming TÜV Date')
                    ->date('d M Y')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('tuv_status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'EXPIRED',
                        'success' => 'UPCOMING',
                        'danger' => 'SOON',
                        'secondary' => 'UNKNOWN',
                    ])
                    ->extraAttributes([
                        'class' => 'font-bold',
                    ])
            ]);
    }
}
