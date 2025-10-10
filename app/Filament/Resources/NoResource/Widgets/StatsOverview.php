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
    public $month;
    public $driver_id;

    protected static string $view = 'filament.widgets.stats-overview';
    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters)
    {
        $this->month = $filters['month'] ?? null;
        $this->driver_id = $filters['driver_id'] ?? null;

        $this->resetCached(); // recalc cards
    }

    protected function getCards(): array
    {
        $driversQuery = Driver::query();
        $invoicesQuery = Invoice::query();
        $vehiclesQuery = Vehicle::query();
        $companiesQuery = Company::query();

        if ($this->month) {
            $invoicesQuery->where('month', $this->month);
        }

        if ($this->driver_id) {
            $invoicesQuery->where('driver_id', $this->driver_id);
        }

        $cards = [
            Card::make('Drivers', $driversQuery->count())
                ->description('Your Total Driver')
                ->icon('heroicon-o-user')
                ->color('primary'),

            Card::make('Invoices', $invoicesQuery->count())
                ->icon('heroicon-o-document-text')
                ->description('Your Total Invoices')
                ->color('danger'),

            Card::make('Vehicles', $vehiclesQuery->count())
                ->color('success')
                ->icon('heroicon-o-truck')
                ->description('Your Total Vehicles'),
        ];

        if (auth()->user()->isAdmin()) {
            $cards[] = Card::make('Companies', $companiesQuery->count())
                ->icon('heroicon-o-building-office')
                ->color('warning')
                ->description('Total Companies');
        }

        return $cards;
    }

    public function getColumnSpan(): int | string | array
    {
        return 2;
    }
}
