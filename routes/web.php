<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoices/{id}/pdf', function ($id) {
    return app(\App\Services\InvoiceExportService::class)->displaySingle($id);
})->name('invoices.pdf');

Route::get('/set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'tr', 'de'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('set-locale');


Route::get('uber/redirect', [\App\Http\Controllers\PlatformConnectController::class, 'redirect'])->name('uber.redirect');

//Route::get('uber/privacy-policy', function (){
//    dd(123);
//});
//
//Route::get('uber', function (){
//    $clientId = env('UBER_CLIENT');
//    $redirectUri = 'https://101bcde16e20.ngrok-free.app/uber/redirect'; // This must match the one registered in Uber dashboard
//    $scopes = urlencode('profile partner.accounts partner.payments partner.trips');
//    $responseType = 'code';
//
//    $url = "https://auth.uber.com/oauth/v2/authorize?" . http_build_query([
//            'client_id' => $clientId,
//            'redirect_uri' => $redirectUri,
//            'scope' => 'profile partner.accounts partner.payments partner.trips',
//            'response_type' => 'code',
//        ]);
//
//    return redirect()->away($url);
//});
//
//
//Route::get('uber/me', function (\Illuminate\Http\Request $request){
//        // Replace with the actual access token you got from OAuth
//        $accessToken = env('UBER_ACCESS_TOKEN');
//
//        // Example coordinates
//        $latitude = 37.7759792;
//        $longitude = -122.41823;
//
//        // Make GET request to Uber Sandbox API
//        $response = \Illuminate\Support\Facades\Http::withHeaders([
//            'Authorization' => 'Bearer ' . $accessToken,
//        ])->get('https://test-api.uber.com/v1.2/products', [
//            'latitude' => $latitude,
//            'longitude' => $longitude,
//        ]);
//
//        // Check for errors
//        if ($response->failed()) {
//            return response()->json([
//                'error' => 'Request failed',
//                'details' => $response->json(),
//            ], $response->status());
//        }
//
//        return $response->json();
//});
