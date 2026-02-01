<?php

namespace App\Services\Uber;
use App\Models\PlatformConnection;
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
            'client_id' => env('ILKER_UBER_CLIENT'),
            'redirect_uri' => env('ILKER_UBER_REDIRECT_URI'),
            // Note the spaces between scopes here
            'scope' => 'profile partner.accounts profile.mobile_number openid',
            'response_type' => 'code'
        ];
        $url = "https://sandbox-login.uber.com/oauth/v2/authorize?" . http_build_query($params);

        return redirect($url);
    }


    public function connectClient()
    {
        $response = Http::asForm()->post('https://sandbox-login.uber.com/oauth/v2/token', [
            'client_id' => env('ILKER_UBER_CLIENT'),
            'client_secret' => env('ILKER_UBER_SECRET'),
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
    public function completeConnect(\Illuminate\Http\Request $request)
    {

        $code = $request->input('code');

        if (!$code) {
            dd('NO CODE' . $code);
            return response()->json(['error' => 'Authorization code not provided'], 400);
        }

        // Exchange the authorization code for an access token
        $clientId = env('ILKER_UBER_CLIENT');
        $clientSecret = env('ILKER_UBER_SECRET');
        $uberRedirectUri = env('ILKER_UBER_REDIRECT_URI');

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

        PlatformConnection::updateOrCreate([
            'company_id' => auth()->user()->company_id,
            'platform' => 'uber',
        ], [
           'access_token' => $data['access_token'],
        ]);


        return response()->json($data);
    }
}
