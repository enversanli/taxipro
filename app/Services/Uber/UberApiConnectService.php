<?php

namespace App\Services\Uber;

class UberApiConnectService
{
    public function __construct()
    {

    }

    public function connect()
    {
        $redirectUri = env('UBER_REDIRECT_URI');
        $url = "https://auth.uber.com/oauth/v2/authorize?client_id=wKEBBLwhzVL78dMu6MVA_2mW11b-UqEh&redirect_uri=$redirectUri&scope=profile&response_type=code";
        $url = "https://sandbox-login.uber.com/oauth/v2/authorize?client_id=KFWnHBQd3gBy6X5T7Nz7TbBLkAf7jldA&redirect_uri=https://166dccbe8845.ngrok-free.app/uber/redirect&scope=profile&response_type=code";
        return redirect($url);
    }

    public function completeConnect(\Illuminate\Http\Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json(['error' => 'Authorization code not provided'], 400);
        }

        // Exchange the authorization code for an access token
        $clientId = env('UBER_CLIENT');
        $clientSecret = env('UBER_SECRET');
        $uberRedirectUri = env('UBER_REDIRECT_URI');

        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://sandbox-login.uber.com/oauth/v2/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $uberRedirectUri,
            'code' => $code,
        ]);

        $data = $response->json();
        dd($data);
        if ($response->failed()) {
            return response()->json([
                'error' => 'Token request failed',
                'details' => $data,
            ], 400);
        }

        dd($data);
        return response()->json($data);
    }
}
