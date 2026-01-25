<?php

namespace App\Traits;

use App\Models\Driver;
use Filament\Notifications\Notification;

trait DriverTrait
{
    public function storeDriverByBolt(array $driversData): array
    {
        $count = 0;
        foreach ($driversData as $d) {
            Driver::updateOrCreate(
                [
                    'email' => $d['email'],
                    'company_id' => auth()->user()->company_id
                ],
                [
                    'first_name' => $d['first_name'] ?? 'Unknown',
                    'last_name'  => $d['last_name'] ?? '',
                    'phone'      => $d['phone'] ?? null,
                    'email' => $d['email'] ?? null,
                    'bolt_uuid' => $d['driver_uuid'],
                    'bolt_partner_uuid' => $d['partner_uuid'],
                ]
            );
            $count++;
        }

        Notification::make()
            ->success()
            ->body($count . ' Drivers have been imported.')
            ->title('Drivers have been imported.');
        return [];
    }

    protected function findDriver($phone, $name)
    {
        if (!$phone && !$name) return null;

        if ($phone) {
            $driver = Driver::where('phone', 'LIKE', "%$phone%")->first();
            if ($driver) return $driver;
        }

        // Fallback to name
        if ($name) {
            return Driver::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$name%"])->first();
        }

        return null;
    }
}
