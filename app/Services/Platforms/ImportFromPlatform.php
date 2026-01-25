<?php

namespace App\Services\Platforms;

use App\Services\Bolt\BoltFleetService;
use App\Traits\DriverTrait;
use App\Traits\OrderTrait;
use App\Traits\TripTrait;
use App\Traits\VehicleTrait;
use Carbon\Carbon;

class ImportFromPlatform
{
    use DriverTrait;
    use TripTrait;
    use OrderTrait;
    use VehicleTrait;

    /**
     * Main entry point
     */
    public function importOrders(string $platform, $startDate, $endDate): array
    {
        // 1. Fetch
        $rawOrders = match ($platform) {
            'bolt' => $this->fetchFromBolt($startDate, $endDate),
            'uber' => [], // Future
            default => [],
        };

        // 2. Normalize
        $normalizedTrips = match ($platform) {
            'bolt' => $this->normalizeBoltOrders($rawOrders),
            'uber' => [], // Future
            default => [],
        };


        return self::saveTripsByPlatform($normalizedTrips, $platform);
    }

    protected function fetchFromBolt($startDate, $endDate): array
    {
        // ... (Keep your existing fetch logic here) ...
        // Ensure you return the 'orders' array from the API response
        $service = new BoltFleetService();
        // Use timestamps as required by your specific Bolt endpoint
        $startTs = Carbon::parse($startDate)->startOfDay()->timestamp;
        $endTs = Carbon::parse($endDate)->endOfDay()->timestamp;

        $response = $service->getOrders($startTs, $endTs);
        return $response['data']['orders'] ?? [];
    }


    /**
     * Driver Matching Logic
     */


    public function importDrivers(string $platform): array
    {
        match ($platform) {
            'bolt' => $this->fetchDriversFromBolt(),
            default => [],
        };

        return [];
    }

    public function fetchDriversFromBolt(): array
    {
        $service = new BoltFleetService();

        return self::storeDriverByBolt($service->getDrivers()['data']['drivers']);
    }

    public function importVehicles(string $platform): array
    {
        $rawVehicles = match ($platform) {
            'bolt' => $this->fetchVehiclesFromBolt(),
            default => [],
        };

        return $rawVehicles;
    }

    public function fetchVehiclesFromBolt(): array
    {
        $service = new BoltFleetService();

        return self::storeVehicleByBolt($service->getVehicles()['data']['vehicles']);
    }
}
