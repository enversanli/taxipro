<?php

namespace App\Services;

class InvoiceCalculateService
{
    protected array $commission;

    public function __construct()
    {
        $this->commission = config('platform');
    }

    public function calculateMain($state): array
    {
        $totalGross = collect($state)->sum(fn($item) => (float)($item['gross'] ?? 0));
        $tip = collect($state)->sum(fn($item) => (float)($item['tip'] ?? 0));
        $bar = collect($state)->sum(fn($item) => (float)($item['bar'] ?? 0));

        $cash = collect($state)->sum(function ($item) {
            $gross = (float)($item['gross'] ?? 0);
            $bar = (float)($item['bar'] ?? 0);
            return $gross - $bar;
        });

        $net = collect($state)->sum(function ($item) {
            $gross = (float)($item['gross'] ?? 0);
            $commissionRate = (float)($this->commission[$item['platform']]['commission'] ?? 0);
            return $gross * (1 - $commissionRate);
        });

        $driverSalary = $net - 100;

        return compact('totalGross', 'net', 'bar', 'tip', 'cash', 'driverSalary');
    }

    public function calculateDetail($state): array
    {
        return collect($state)
            ->groupBy('platform')
            ->map(function ($items, $platform) {
                $gross = $items->sum(fn($item) => (float)($item['gross'] ?? 0));
                $tip = $items->sum(fn($item) => (float)($item['tip'] ?? 0));
                $bar = $items->sum(fn($item) => (float)($item['bar'] ?? 0));

                $commissionRate = (float)($this->commission[$platform]['commission'] ?? 0);
                $cash = ($gross ?? 0) - ($tip ?? 0) - ($bar ?? 0);
                $net = $gross * (1 - $commissionRate);

                return [
                    'gross' => $gross ?? 0,
                    'tip' => $tip ?? 0,
                    'bar' => $bar ?? 0,
                    'cash' => $cash ?? 0,
                    'net' => 0,
                    'platform' => $platform
                ];
            })
            ->toArray();
    }
}
