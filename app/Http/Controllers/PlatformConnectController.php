<?php

namespace App\Http\Controllers;

use App\Services\Uber\UberApiConnectService;
use Illuminate\Http\Request;

class PlatformConnectController extends Controller
{
    public function redirect(Request $request)
    {
        $service = new UberApiConnectService();
        $service->completeConnect($request);
    }
}
