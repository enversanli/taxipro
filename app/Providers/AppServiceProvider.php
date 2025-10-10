<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Vehicle;
use App\Observers\InvoiceObserver;
use App\Observers\VehicleObserver;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Invoice::observe(InvoiceObserver::class);
        Vehicle::observe(VehicleObserver::class);

        FilamentAsset::register([
            Css::make('example-local-stylesheet', asset('css/filament.css')),
        ]);

        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);
    }
}
