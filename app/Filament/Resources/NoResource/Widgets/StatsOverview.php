<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $cards = [Card::make('Drivers', Driver::count())
            ->description('Your Total Driver')
            ->icon('heroicon-o-user')
            ->color('primary')
            ->chart([30, 20, 31, 3, 25, 4, 40]),
            Card::make('Invoices', Invoice::count())
                ->icon('heroicon-o-document-text')
                ->description('Your Total Invoices')
                ->color('danger')
                ->chart([37, 50, 14, 13, 65, 34, 10]),
            Card::make('Vehicles', Vehicle::count())
                ->color('success')
                ->icon('heroicon-o-truck')
                ->description('Your Total Vehicles')
                ->chart([17, 10, 1, 3, 25, 4, 40]),];

        if (auth()->user()->isAdmin()){
            $cards[] = Card::make('Companies', Company::count())
                ->icon('heroicon-o-building-office')
                ->color('warning')
                ->description('Total Companies')
                ->chart([7, 2, 10, 3, 15, 4, 17]);
        }

        return $cards;
    }

    public function getColumnSpan(): int | string | array
    {
        return 2; // Full width in a 2-column grid
    }
}
