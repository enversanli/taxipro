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
            Card::make(__('common.drivers'), $driversQuery->count())
                ->description(__('common.total_drivers'))
                ->icon('heroicon-o-user')
                ->color('primary'),

            Card::make(__('common.invoices'), $invoicesQuery->count())
                ->icon('heroicon-o-document-text')
                ->description(__('common.total_invoices'))
                ->color('danger'),

            Card::make(__('common.vehicles'), $vehiclesQuery->count())
                ->color('success')
                ->icon('heroicon-o-truck')
                ->description(__('common.total_vehicles')),
        ];

        if (auth()->user()->isAdmin()) {
            $cards[] = Card::make(__('common.companies'), $companiesQuery->count())
                ->icon('heroicon-o-building-office')
                ->color('warning')
                ->description(__('common.total_companies'));
        }

        return $cards;
    }

    public function getColumnSpan(): int | string | array
    {
        return 2;
    }
}
