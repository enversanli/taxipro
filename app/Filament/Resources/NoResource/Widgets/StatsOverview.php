<?php

namespace App\Filament\Resources\NoResource\Widgets;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat; // V3 uses 'Stat', not 'Card'
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    public $month;
    public $driver_id;

    // Keep listeners for filter interaction
    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    // Modern dashboards usually sit at the top, concise.
    protected int | string | array $columnSpan = 'full';

    public function updateFilters($filters)
    {
        $this->month = $filters['month'] ?? null;
        $this->driver_id = $filters['driver_id'] ?? null;

        $this->resetCached();
    }

    protected function getStats(): array
    {
        // 1. Prepare Queries
        $driversQuery = Driver::query();
        $vehiclesQuery = Vehicle::query();
        $companiesQuery = Company::query();

        // Invoice Query needs to be cloneable for Chart vs Total
        $invoicesQuery = Invoice::query();

        if ($this->month) {
            $invoicesQuery->where('month', $this->month);
        }

        if ($this->driver_id) {
            $invoicesQuery->where('driver_id', $this->driver_id);
        }

        // 2. Calculate Modern Metrics

        // A. REVENUE (Instead of just counting invoices, sum the money)
        $totalRevenue = $invoicesQuery->sum('taxameter_total');

        // Get the last 7 data points for the sparkline chart
        $revenueChart = $invoicesQuery
            ->latest()
            ->take(7)
            ->pluck('taxameter_total')
            ->toArray();

        // B. DRIVERS
        $driverCount = $driversQuery->count();
        $newDriversThisMonth = Driver::where('created_at', '>=', now()->startOfMonth())->count();

        // C. VEHICLES
        $vehicleCount = $vehiclesQuery->count();
        $taxiCount = $vehiclesQuery->where('usage_type', 'taxi')->count();

        // 3. Build Stats
        $stats = [
            // REVENUE STAT (The most important one)
            Stat::make(__('Umsatz'), Number::currency($totalRevenue, 'EUR'))
                ->description('Gesamtumsatz (Gefiltert)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($revenueChart) // Sparkline
                ->color('success'), // Green for money

            // DRIVERS STAT
            Stat::make(__('common.drivers'), $driverCount)
                ->description($newDriversThisMonth > 0 ? "+{$newDriversThisMonth} diesen Monat" : 'Aktive Fahrer')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            // VEHICLES STAT
            Stat::make(__('common.vehicles'), $vehicleCount)
                ->description("Davon {$taxiCount} Taxis")
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'), // Orange/Yellow for fleet
        ];

        // ADMIN ONLY STAT
        if (auth()->user()->role === 'admin') { // Assuming 'role' check based on previous context
            $stats[] = Stat::make(__('common.companies'), $companiesQuery->count())
                ->description(__('Registrierte Firmen'))
                ->icon('heroicon-m-building-office')
                ->color('gray');
        }

        return $stats;
    }
}
