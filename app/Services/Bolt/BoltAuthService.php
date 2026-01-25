<?php

namespace App\Services\Bolt;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BoltAuthService
{
    protected string $baseUrl;
    protected ?string $clientId;
    protected ?string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.bolt.base_url') ?? env('BOLT_BASE_URL');

        $this->clientId = env('BOLT_CLIENT_ID');
        $this->clientSecret = env('BOLT_CLIENT_SECRET');

        if (empty($this->baseUrl)) {
            $this->baseUrl = 'https://api.bolt.eu/fleet-integration/v1';
        }
    }

    public function getToken(): string
    {
        if (Cache::has('bolt_api_token')) {
            return Cache::get('bolt_api_token');
        }

        $response = Http::asForm()
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->post('https://oidc.bolt.eu/token', [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',
                'scope'         => 'fleet-integration:api'
            ]);

        if ($response->failed()) {
            $body = $response->body();
            if (str_contains($body, '<html') || str_contains($body, '<body')) {
                $title = preg_match('/<title>(.*?)<\/title>/', $body, $matches) ? $matches[1] : 'Unknown HTML Error';
                throw new \Exception("Bolt Auth blocked (HTML response). Title: '$title'. Status: " . $response->status());
            }
            throw new \Exception("Bolt Auth Failed [{$response->status()}]: " . $body);
        }

        $data = $response->json();

        if (is_null($data)) {
            throw new \Exception("Bolt response was not valid JSON. Raw body: " . substr($response->body(), 0, 200));
        }

        $token = $data['access_token'] ?? null;

        if (!$token) {
            throw new \Exception("No access_token found in response. Data: " . json_encode($data));
        }

        Cache::put('bolt_api_token', $token, now()->addMinutes(5));

        return $token;
    }

    public function request(string $method, string $endpoint, array $params = [])
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->timeout(15)
            ->$method("{$this->baseUrl}/{$endpoint}", $params);

        if ($response->header('Content-Type') && str_contains($response->header('Content-Type'), 'text/html')) {
            $title = preg_match('/<title>(.*?)<\/title>/', $response->body(), $matches) ? $matches[1] : 'Unknown';
            throw new \Exception("API Endpoint returned HTML (Status {$response->status()}). You likely have the WRONG BOLT_BASE_URL in .env. Page Title: $title");
        }

        return $response;
    }
}
