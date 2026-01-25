<?php

namespace App\Services\Bolt;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class BoltFleetService
{
    protected string $baseUrl = 'https://node.bolt.eu/fleet-integration-gateway/fleetIntegration/v1';
    protected string $accessToken = '';

    public function __construct()
    {
        $this->getAccessToken();
    }

    public function getCompanies()
    {
        $response = Http::withToken($this->accessToken)
            ->get($this->baseUrl . '/getCompanies')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(20);

        dd($response->body());
    }

    public function getOrders($startTs, $endTs)
    {
        return $this->requestToApi('getFleetOrders', [
            'company_id' => 154113,
            'company_ids' => [80663, 154113],
            'limit' => 1000,
            'offset' => 0,
            'start_ts' => $startTs,
            'end_ts' => $endTs,
        ]);

    }

    public function getDrivers()
    {
        $startTs = Carbon::now()->startOfDay()->timestamp;
        $endTs = Carbon::now()->timestamp;

        return $this->requestToApi('getDrivers', [
            'company_id' => 154113,
            'company_ids' => [80663, 154113],
            'limit' => 1000,
            'offset' => 0,
            'start_ts' => $startTs,
            'end_ts' => $endTs,
        ]);
    }

    public function getVehicles()
    {
        $startTs = Carbon::now()->startOfDay()->timestamp;
        $endTs = Carbon::now()->timestamp;

        return $this->requestToApi('getVehicles', [
            'company_id' => 154113,
            'company_ids' => [80663, 154113],
            'limit' => 100,
            'offset' => 0,
            'start_ts' => $startTs,
            'end_ts' => $endTs,
        ]);
    }

    private function getAccessToken(): void
    {
        $authService = new BoltAuthService();

        $this->accessToken = $authService->getToken();
    }

    private function requestToApi($endPoint, $data)
    {
        $response = Http::withToken($this->accessToken)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->baseUrl . '/' . $endPoint, $data);

        if ($response->failed()) {
            throw new \Exception('Bolt API Error: ' . $response->json()['message']);
        }

        if ($response->json()['message'] != 'OK') {
            throw new \Exception($response->json()['message']);
        }

        return $response->json();
    }
}
