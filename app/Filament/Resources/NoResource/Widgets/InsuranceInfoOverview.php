<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class InsuranceInfoOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->where(function ($query) {
                        $query->whereNotNull('insurance_date');
                    })
                    ->orderBy('insurance_date', 'ASC')
                    ->take(3)
            )
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label(__('common.license_plate'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('insurance_date')
                    ->label(__('common.insurance_date'))
                    ->date('d M Y')
                    ->color('success'),

                Tables\Columns\TextColumn::make('upcoming_insurance_date')
                    ->label(__('common.upcoming_insurance_date'))
                    ->date('d M Y')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('insurance_status')
                    ->label(__('common.insurance_status'))
                    ->colors([
                        'warning' => __('common.insurance_statuses.expired'),
                        'success' => __('common.insurance_statuses.upcoming'),
                        'danger'  => __('common.insurance_statuses.soon'),
                        'secondary' => __('common.insurance_statuses.unknown'),
                    ])
                    ->extraAttributes([
                        'class' => 'font-bold',
                    ])

            ]);
    }

    public function getColumnSpan(): int | string | array
    {
        return 1;
    }
}
