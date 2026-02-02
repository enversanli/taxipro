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

Route::view('privacy-policy', 'privacy-policy')->name('privacy-policy');


Route::get('scrape', function (){
//    $serv = new \App\Services\ScraperService();
//    $cookie = 'CookieConsent={stamp:%27/IuO/nQRU/7uSSUWt9Kb0sJQa3BzJm5tm2CDJTT7DZ4AET7KRzB/XA==%27%2Cnecessary:true%2Cpreferences:false%2Cstatistics:false%2Cmarketing:false%2Cmethod:%27explicit%27%2Cver:1%2Cutc:1769301826093%2Cregion:%27de%27}; mp_8fab875f485abf8675b3f4d85004406c_mixpanel=%7B%22distinct_id%22%3A%20%22%24device%3A19bf2f691c6146-06da233dcdd78c-1b525631-1fa400-19bf2f691c6146%22%2C%22%24device_id%22%3A%20%2219bf2f691c6146-06da233dcdd78c-1b525631-1fa400-19bf2f691c6146%22%2C%22__mps%22%3A%20%7B%22%24os%22%3A%20%22Mac%20OS%20X%22%2C%22%24browser%22%3A%20%22Chrome%22%2C%22%24browser_version%22%3A%20144%2C%22%24initial_referrer%22%3A%20%22https%3A%2F%2Fwww.google.com%2F%22%2C%22%24initial_referring_domain%22%3A%20%22www.google.com%22%2C%22utm_source%20%5Blast%20touch%5D%22%3A%20%22google%22%2C%22utm_medium%20%5Blast%20touch%5D%22%3A%20%22cpc%22%2C%22utm_campaign%20%5Blast%20touch%5D%22%3A%20%2221397679239%22%2C%22utm_term%20%5Blast%20touch%5D%22%3A%20%22bolt%20unternehmen%22%2C%22utm_content%20%5Blast%20touch%5D%22%3A%20%22703696165108%22%2C%22utm_adgroup%20%5Blast%20touch%5D%22%3A%20%22164406341512%22%7D%2C%22__mpso%22%3A%20%7B%22initial_utm_source%22%3A%20%22google%22%2C%22initial_utm_medium%22%3A%20%22cpc%22%2C%22initial_utm_campaign%22%3A%20%2221397679239%22%2C%22initial_utm_content%22%3A%20%22703696165108%22%2C%22initial_utm_term%22%3A%20%22bolt%20unternehmen%22%2C%22utm_source%22%3A%20%22google%22%2C%22utm_medium%22%3A%20%22cpc%22%2C%22utm_campaign%22%3A%20%2221397679239%22%2C%22utm_term%22%3A%20%22bolt%20unternehmen%22%2C%22utm_content%22%3A%20%22703696165108%22%2C%22utm_adgroup%22%3A%20%22164406341512%22%7D%2C%22__mpus%22%3A%20%7B%7D%2C%22__mpa%22%3A%20%7B%7D%2C%22__mpu%22%3A%20%7B%7D%2C%22__mpr%22%3A%20%5B%5D%2C%22__mpap%22%3A%20%5B%5D%2C%22%24search_engine%22%3A%20%22google%22%2C%22utm_source%22%3A%20%22google%22%2C%22utm_medium%22%3A%20%22cpc%22%2C%22utm_campaign%22%3A%20%2221397679239%22%2C%22utm_content%22%3A%20%22703696165108%22%2C%22utm_term%22%3A%20%22bolt%20unternehmen%22%2C%22%24initial_referrer%22%3A%20%22https%3A%2F%2Fwww.google.com%2F%22%2C%22%24initial_referring_domain%22%3A%20%22www.google.com%22%2C%22utm_source%20%5Blast%20touch%5D%22%3A%20%22google%22%2C%22utm_medium%20%5Blast%20touch%5D%22%3A%20%22cpc%22%2C%22utm_campaign%20%5Blast%20touch%5D%22%3A%20%2221397679239%22%2C%22utm_term%20%5Blast%20touch%5D%22%3A%20%22bolt%20unternehmen%22%2C%22utm_content%20%5Blast%20touch%5D%22%3A%20%22703696165108%22%2C%22utm_adgroup%20%5Blast%20touch%5D%22%3A%20%22164406341512%22%2C%22utm_adgroup%22%3A%20%22164406341512%22%7D; mp_1a3f21ca8018375e914288d06af0cb79_mixpanel=%7B%22distinct_id%22%3A%22%24device%3A68f0b7da-b973-468c-9f42-d444896e508b%22%2C%22%24device_id%22%3A%2268f0b7da-b973-468c-9f42-d444896e508b%22%2C%22%24search_engine%22%3A%22google%22%2C%22%24initial_referrer%22%3A%22https%3A%2F%2Fgemini.google.com%2F%22%2C%22%24initial_referring_domain%22%3A%22gemini.google.com%22%2C%22__mps%22%3A%7B%7D%2C%22__mpso%22%3A%7B%22%24initial_referrer%22%3A%22https%3A%2F%2Fgemini.google.com%2F%22%2C%22%24initial_referring_domain%22%3A%22gemini.google.com%22%7D%2C%22__mpus%22%3A%7B%7D%2C%22__mpa%22%3A%7B%7D%2C%22__mpu%22%3A%7B%7D%2C%22__mpr%22%3A%5B%5D%2C%22__mpap%22%3A%5B%5D%7D; _cfuvid=5U3PU2PvNPgxzvMEno6hsJIE1tOZ_2.EGuORr1UMfV4-1769557908280-0.0.1.1-604800000';
//    $serv->fetchDashboardData($cookie);
})->name('uber.tes');
