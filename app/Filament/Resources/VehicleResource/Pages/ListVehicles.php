<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\NoResource\Widgets\InsuranceInfoOverview;
use App\Filament\Resources\NoResource\Widgets\TuvInfoOverview;
use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array{
        return [
            TuvInfoOverview::class,
            InsuranceInfoOverview::class
        ];
    }
}
