<?php

namespace App\Console\Commands;

use App\Services\Bolt\BoltApiCompanyService;
use App\Services\Bolt\BoltAuthService;
use Illuminate\Console\Command;

class TestBoltApi extends Command
{
    protected $signature = 'test:bolt';
    protected $description = 'Test Bolt Login & Data Fetch';

    public function handle(BoltAuthService $bolt)
    {
        $this->info('Testing Bolt API with Client Credentials...');
        $service = new BoltApiCompanyService();

        $service->getDrivers();

        exit();
        try {
            // We trigger the auth flow by making a dummy request
            // Since we don't know the exact endpoint yet, we just try to get a token first.

            // This line forces the getToken() method to run
            $response = $bolt->request('get', 'company');

            if ($response->status() === 404) {
                $this->info('✅ Auth Successful! (Token received, but endpoint /company not found, which is normal).');
                $this->info('Your connection is working.');
            } elseif ($response->successful()) {
                $this->info('✅ Full Success!');
                $this->info($response->body());
            } else {
                $this->error('❌ Error: ' . $response->status());
                $this->error($response->body());
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
