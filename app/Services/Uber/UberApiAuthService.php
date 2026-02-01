<?php

namespace App\Services\Uber;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class UberApiAuthService
{
    // Uber Production URLs
    protected string $authUrl = 'https://auth.uber.com/oauth/v2/token';
    protected string $sandboxUrl = 'https://sandbox-login.uber.com/oauth/v2/token';
    protected string $baseUrl = 'https://api.uber.com/v1';

    protected ?string $clientId;
    protected ?string $clientSecret;

    public function __construct()
    {
        $this->clientId = env('ILKER_UBER_CLIENT');
        $this->clientSecret = env('ILKER_UBER_SECRET');
    }

    /**
     * Authenticate with Client Secret (OAuth 2.0)
     */
    public function getToken(): string
    {
        // 1. Check Cache
        if (Cache::has('uber_api_token')) {
            return Cache::get('uber_api_token');
        }

        // 2. Request Token
        $response = Http::asForm()
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->post($this->sandboxUrl, [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',

                'scope'         => 'banking.events.financial_products'
            ]);
        dd(1,$response->body());
        if ($response->failed()) {
            throw new \Exception("Uber Auth Failed: " . $response->body());
        }

        $data = $response->json();
        $token = $data['access_token'] ?? null;
        $expiresIn = $data['expires_in'] ?? 3600; // Usually 30 days or 1 hour depending on app type

        if (!$token) {
            throw new \Exception("No access_token from Uber.");
        }

        // 3. Cache Token (Uber tokens last longer, but we play safe)
        Cache::put('uber_api_token', $token, now()->addSeconds($expiresIn - 60));

        return $token;
    }

    /**
     * Example: Get Fleet Trips
     */
    public function getTrips($fromTime, $toTime)
    {
        $token = $this->getToken();

        // Uber Fleet API often requires specific headers (like Organization ID in some versions)
        // or just the Bearer token.
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/fleets/trips", [
                'from_time' => $fromTime, // Unix timestamp or ISO? Check docs
                'to_time'   => $toTime,
                'limit'     => 50
            ]);

        return $response->json();
    }
}
