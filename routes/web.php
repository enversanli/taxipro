<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('test', function (){
    $headers = [
        'User-Agent' => 'de.mobile.android.app/9.0 (1000) (gzip)',
        'X-Mobile-Api-Version' => '10',
        'Accept' => 'application/json, text/plain, */*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection' => 'keep-alive',
        'Host' => 'm.mobile.de',
    ];


    return \Illuminate\Support\Facades\Http::withHeaders($headers)
        ->withOptions(['verify' => false, 'timeout' => 100])
       ->get('https://m.mobile.de/svc/a/285041801')
       ->dd();
});
