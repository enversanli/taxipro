<?php

namespace App\Services\Bolt;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BoltApiCompanyService
{
    protected string $baseUrl = 'https://node.bolt.eu/fleet-integration-gateway/fleetIntegration/v1';

    public function getCompanies()
    {
        $response = Http::withToken(env('BOLT_ACCESS_TOKEN'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(20)
            ->get($this->endpoint . '/getCompanies');

        dd($response->body());
    }

    public function getOrders()
    {
        // 1. Calculate Timestamps (Start of Month -> Now)
        $startTs = Carbon::now()->startOfDay()->timestamp; // 1st of month at 00:00:00
        $endTs   = Carbon::now()->timestamp;                 // Current moment

        // 2. Send POST Request
        $response = Http::withToken(env('BOLT_ACCESS_TOKEN')) // Use your dynamic token method
        ->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->timeout(30)
            ->post($this->baseUrl . '/getFleetOrders', [
                'company_id' => 154113,
                'company_ids' => [80663, 154113],
                'limit'      => 1000,
                'offset'     => 0,
                'start_ts'   => $startTs,
                'end_ts'     => $endTs,
            ]);
        dd($response->body());
        if ($response->failed()) {
            dd('Bolt API Error: ' . $response->body());
        }

        return $response->json();
    }

    public function getDrivers()
    {
        $startTs = Carbon::now()->startOfDay()->timestamp; // 1st of month at 00:00:00
        $endTs   = Carbon::now()->timestamp;

        $response = Http::withToken(env('BOLT_ACCESS_TOKEN')) // Use your dynamic token method
        ->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->timeout(30)
            ->post($this->baseUrl . '/getDrivers', [
                'company_id' => 154113,
                'company_ids' => [80663, 154113],
                'limit'      => 1000,
                'offset'     => 0,
                'start_ts'   => $startTs,
                'end_ts'     => $endTs,
            ]);

        dd($response->body());
    }
}
