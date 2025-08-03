<?php

namespace App\Observers;

use App\Models\Vehicle;

class VehicleObserver
{
    public function creating(Vehicle $vehicle)
    {
        if (!$vehicle->company_id) {
            $vehicle->company_id = auth()->user()->company_id;
        }
    }
}
