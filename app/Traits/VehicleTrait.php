<?php

namespace App\Traits;

use App\Models\Vehicle;

trait VehicleTrait
{
    public function storeVehicleByBolt(array $vehiclesData): array
    {
        foreach ($vehiclesData as $vehicleData) {
            Vehicle::updateOrCreate([
                'license_plate' => $vehicleData['reg_number'],
                'company_id' => auth()->user()->company_id,
            ], [
                'brand' => $this->findBrand($vehicleData['model']),
                'year' => $vehicleData['year'],
                'model' => $vehicleData['model'],
                'bolt_uuid' => $vehicleData['uuid'],
            ]);
        }

        return [];
    }


    private function findBrand(string $model): string
    {
        $brands = [
            'Mercedes-Benz', 'Volkswagen', 'BMW',
            'Audi', 'Opel', 'Skoda', 'Ford', 'Toyota',
            'Renault', 'Hyundai', 'Other'
        ];

        foreach ($brands as $brand) {
            if (stripos($model, $brand) !== false) {
                return $brand;
            }
        }
        return 'Other';
    }
}
