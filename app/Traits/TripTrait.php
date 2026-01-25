<?php

namespace App\Traits;

use App\Models\Trip;
use Illuminate\Support\Facades\Log;

trait TripTrait
{
    protected function saveTripsByPlatform(array $trips, string $platform): array
    {
        $imported = 0;
        $errors = 0;

        foreach ($trips as $data) {
            try {
                // 1. Find Driver
                $driver = $this->findDriver($data['driver_phone'], $data['driver_name']);

                // 2. Update or Create Trip
                Trip::updateOrCreate(
                    [
                        'platform'    => $platform,
                        'external_id' => $data['external_id'],
                    ],
                    [
                        'driver_id'             => $driver?->id,
                        'vehicle_license_plate' => $data['license_plate'],

                        'pickup_address'  => $data['pickup_address'],
                        'dropoff_address' => $data['dropoff_address'],
                        'distance_meters' => $data['distance'],

                        'ordered_at' => $data['ordered_at'],
                        'pickup_at'  => $data['pickup_at'],
                        'dropoff_at' => $data['dropoff_at'],

                        'gross_amount'   => $data['gross_amount'],
                        'net_earnings'   => $data['net_earnings'],
                        'platform_fee'   => $data['platform_fee'],
                        'tips'           => $data['tips'],
                        'cash_collected' => $data['cash_collected'],
                        'payment_method' => $data['payment_method'],

                        'status'   => $data['status'],
                        'raw_data' => $data['raw_data'], // Casts to JSON automatically if model has cast
                    ]
                );

                $imported++;

            } catch (\Exception $e) {
                Log::error("Import Error {$platform} - {$data['external_id']}: " . $e->getMessage());
                $errors++;
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
