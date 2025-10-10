<?php

namespace App\Filament\Resources\VehicleInformationResource\Pages;

use App\Filament\Resources\VehicleInformationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleInformation extends ListRecords
{
    protected static string $resource = VehicleInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
