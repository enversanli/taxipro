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
                    ->whereNotNull('tuv_date')
                    ->orderBy('tuv_date', 'ASC')
                    ->limit(3)
            )
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label(__('common.license_plate'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('tuv_date')
                    ->label(__('common.tuv_date'))
                    ->date('d M Y')
                    ->color('success'),

                Tables\Columns\TextColumn::make('upcoming_tuv_date')
                    ->label(__('common.upcoming_tuv_date'))
                    ->date('d M Y')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('tuv_status')
                    ->label(__('common.tuv_status'))
                    ->colors([
                        'warning'   => __('common.tuv_statuses.expired'),
                        'success'   => __('common.tuv_statuses.upcoming'),
                        'danger'    => __('common.tuv_statuses.soon'),
                        'secondary' => __('common.tuv_statuses.unknown'),
                    ])
                    ->extraAttributes([
                        'class' => 'font-bold',
                    ]),
            ]);
    }

    public function getColumnSpan(): int|string|array
    {
        return 1;
    }
}
