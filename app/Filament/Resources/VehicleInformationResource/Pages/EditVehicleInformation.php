<?php

namespace App\Filament\Resources\VehicleInformationResource\Pages;

use App\Filament\Resources\VehicleInformationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleInformation extends EditRecord
{
    protected static string $resource = VehicleInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
