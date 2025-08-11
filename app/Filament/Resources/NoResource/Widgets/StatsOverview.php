<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Drivers', Driver::count())
            ->description('Your Total Driver'),
            Card::make('Invoices', Invoice::count())
            ->description('Your Total Invoices'),
            Card::make('Vehicles', Vehicle::count())
            ->description('Your Total Vehicles'),
        ];
    }
}
