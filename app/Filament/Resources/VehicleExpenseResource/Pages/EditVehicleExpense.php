<?php

namespace App\Filament\Resources\VehicleExpenseResource\Pages;

use App\Filament\Resources\VehicleExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleExpense extends EditRecord
{
    protected static string $resource = VehicleExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
