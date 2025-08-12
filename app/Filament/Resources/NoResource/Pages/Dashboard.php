<?php

namespace App\Filament\Resources\NoResource\Pages;

use App\Filament\Resources\NoResource\Widgets\InsuranceInfoOverview;
use App\Filament\Resources\NoResource\Widgets\StatsOverview;
use App\Filament\Resources\NoResource\Widgets\TuvInfoOverview;
use Filament\Pages\Page;

class Dashboard extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.resources.no-resource.pages.dashboard';
    protected static ?string $navigationLabel = 'Dashboard';

    protected function getFooterWidgets(): array
    {
        return [
            StatsOverview::class,
            TuvInfoOverview::class,
            InsuranceInfoOverview::class
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 2;
    }
}

