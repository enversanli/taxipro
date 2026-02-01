<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ScraperService
{
// The target URL
    protected string $baseUrl = 'https://fleet.klc-ecommerce.de';

    /**
     * Fetch data using the provided cookie.
     *
     * @param string $cookieString The raw cookie string (e.g., "PHPSESSID=xyz; XSRF-TOKEN=abc")
     */
    public function fetchDashboardData(string $cookieString)
    {
        $this->requestByToken();
//        $this->check();
//        dd(1);
        // 1. Make the Request
        $response = Http::withHeaders([
            'Cookie' => $cookieString,
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
        ])->get('https://fleets.bolt.eu/154113/supply/drivers?tab=performance');
        dd($response->body());
        // Check if login failed (usually redirects to /login if cookie is bad)
        if ($response->redirect()) {
            return [
                'success' => false,
                'message' => 'Session expired or invalid cookie. Please login again.',
            ];
        }

        if ($response->failed()) {
            return ['success' => false, 'message' => 'Connection failed: ' . $response->status()];
        }

        // 2. Parse the HTML to read data
        $html = $response->body();
        $crawler = new Crawler($html);

        // EXAMPLE: Extracting specific data points
        // You must inspect the target website's HTML to find the correct CSS selectors (IDs or Classes).

        // Example A: Get the Page Title
        $pageTitle = $crawler->filter('title')->text('No Title Found');

        // Example B: Get a specific stat (Imagine there is a <div class="total-orders">)
        // $totalOrders = $crawler->filter('.total-orders')->count() ? $crawler->filter('.total-orders')->text() : '0';

        // Example C: extracting a table of data
        $data = $crawler->filter('table tr')->each(function (Crawler $node, $i) {
            // Skip header row
            if ($i === 0) return null;

            return [
                'raw_text' => $node->text(),
                // 'id' => $node->filter('td.id-column')->text(), // Specific column example
            ];
        });

        return [
            'success' => true,
            'title' => $pageTitle,
            'scraped_data' => array_filter($data), // Filter out nulls
            // 'raw_html' => $html, // Uncomment if you want to debug raw output
        ];
    }

    public function check()
    {
        $url = 'https://fleetownerportal.live.boltsvc.net/fleetOwnerPortal/getAccessToken';

        $response = Http::withHeaders([

            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Origin' => 'https://fleets.bolt.eu',
            'Referer' => 'https://fleets.bolt.eu/',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',

        ])

            ->post($url . '?language=de-de&version=FO.3.1655&brand=bolt', [
                'refresh_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7InR5cGUiOiJiYXNlIiwiZmxlZXRfb3duZXJfaWQiOjc2NDIxLCJqdGkiOiJmMWIzZTQ4YS1hOTVlLTRlZDctYWJhOS1kMGIxYjdjMDU0OTgifSwiaWF0IjoxNzY5NTU5MjU5LCJleHAiOjE3NzAxNjQwNTl9.gQW6ctnQ5Z8fo_gUjFv8Y9_jEIRwg-z3VJeXCnlcstU'
            ]);
        dd($response->body());
        if ($response->successful()) {
            $token = $response->json();
            return $token;
        }

        return $response->body();
    }

    private function requestByToken()
    {
        $url = 'https://fleetownerportal.live.boltsvc.net/fleetOwnerPortal/getCompanyDetails';
        $token = "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7InR5cGUiOiJjb21wYW55IiwiZmxlZXRfb3duZXJfaWQiOjc2NDIxLCJjb21wYW55Ijp7ImNvbXBhbnlfdHlwZSI6ImZsZWV0X2NvbXBhbnkiLCJjb21wYW55X2lkIjoxNTQxMTMsInBlcm1pc3Npb25zIjpbImZpbmFuY2lhbHM6dmlldyIsImRyaXZlcnM6dmlldyIsInZlaGljbGVzOnZpZXciLCJkcml2ZXJfYXBwbGljYXRpb25zOnZpZXciLCJ2ZWhpY2xlX2FwcGxpY2F0aW9uczp2aWV3IiwiY29tcGFueV9hY2Nlc3M6dmlldyIsImZpbmFuY2lhbHM6d3JpdGUiLCJkcml2ZXJzOndyaXRlIiwidmVoaWNsZXM6d3JpdGUiLCJkcml2ZXJfYXBwbGljYXRpb25zOndyaXRlIiwidmVoaWNsZV9hcHBsaWNhdGlvbnM6d3JpdGUiLCJjb21wYW55X2FjY2Vzczp3cml0ZSJdfX0sImlhdCI6MTc2OTYyMTQ1MCwiZXhwIjoxNzY5NjIyMzUwfQ.puuxs-e59sjllTT8XjK6em5tsc97FJSeAIw64DRFyLE";

        $response = Http::withHeaders([
            // Standard Headers
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Origin' => 'https://fleets.bolt.eu',
            'Referer' => 'https://fleets.bolt.eu/',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
            'Authorization' => $token,

        ])
            ->get($url . '?language=de-de&version=FO.3.1657&company_id=154113&user_id=76421&brand=bolt');

        dd($response->body());
    }
}
