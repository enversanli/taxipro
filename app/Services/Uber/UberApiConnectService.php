<?php

namespace App\Services\Uber;
use App\Models\PlatformConnection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
class UberApiConnectService
{
    public function __construct()
    {

    }


    // Verbinden butonuna basınca çağırılıyor
    public function connect()
    {
        $params = [
            'client_id' => env('UBER_CLIENT'),
            'redirect_uri' => env('UBER_REDIRECT_URI'),
            'scope' => 'profile profile.mobile_number partner.accounts',
            'response_type' => 'code'
        ];

        $url = "https://sandbox-login.uber.com/oauth/v2/authorize?" . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return redirect($url);
    }


    public function completeConnect(\Illuminate\Http\Request $request)
    {

        $code = $request->input('code');

        if (!$code) {
            dd('NO CODE' . $code);
            return response()->json(['error' => 'Authorization code not provided'], 400);
        }


        $clientId = env('UBER_CLIENT');
        $clientSecret = env('UBER_SECRET');
        $uberRedirectUri = env('UBER_REDIRECT_URI');

        $url = 'https://sandbox-login.uber.com/oauth/v2/token';

        $response = \Illuminate\Support\Facades\Http::asForm()->post($url, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $uberRedirectUri,
            'code' => $code,
        ]);

        $data = $response->json();

        if ($response->failed()) {
            return response()->json([
                'error' => 'Token request failed',
                'details' => $data,
            ], 400);
        }

        $platform = PlatformConnection::updateOrCreate([
            'company_id' => Auth::user()->company_id ?? 1,
            'platform' => 'uber',
        ], [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        dd($platform);

        return response()->json($data);
    }


    public function connectClient()
    {
        $response = Http::asForm()->post('https://sandbox-login.uber.com/oauth/v2/token', [
            'client_id' => env('UBER_CLIENT'),
            'client_secret' => env('UBER_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'profile',
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();
            dd($data);
            return $data['access_token'];
        }

        // Handle errors (e.g., log them)
        return $response->throw();
    }

    // CEvap geldiğinde /uber/redirect çağırılıyor

}
