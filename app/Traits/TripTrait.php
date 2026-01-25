<?php

namespace App\Traits;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait TripTrait
{
    protected function saveTripsByPlatform(array $trips, string $platform): array
    {
        $imported = 0;
        $errors = 0;
        $invoiceIds = [];
        foreach ($trips as $trip) {
            try {
                // 1. Find Driver
                $driver = $this->findDriver($trip['driver_phone'], $trip['driver_name']);
                $vehicle = Vehicle::where('license_plate', $trip['license_plate'])->first();
                $invoice = $this->createInvoice($driver->id, $vehicle->id);

                if (!in_array($invoice->id, $invoiceIds)) {
                    $invoiceIds[] = $invoice->id;
                }

                // 2. Update or Create Trip
                Trip::updateOrCreate(
                    [
                        'platform' => $platform,
                        'external_id' => $trip['external_id'],
                        'invoice_id' => $invoice->id,
                        'driver_id' => $driver?->id,
                        'vehicle_id' => $vehicle?->id,
                    ],
                    [

                        'pickup_address' => $trip['pickup_address'],
                        'dropoff_address' => $trip['dropoff_address'],
                        'distance_meters' => $trip['distance'],

                        'ordered_at' => $trip['ordered_at'],
                        'pickup_at' => $trip['pickup_at'],
                        'dropoff_at' => $trip['dropoff_at'],

                        'gross_amount' => $trip['gross_amount'],
                        'net_earnings' => $trip['net_earnings'],
                        'platform_fee' => $trip['platform_fee'],
                        'tips' => $trip['tips'],
                        'cash_collected' => $trip['cash_collected'],
                        'payment_method' => $trip['payment_method'],

                        'status' => $trip['status'],
                        'raw_data' => json_encode($trip['raw_data']),
                    ]
                );

                $imported++;

            } catch (\Exception $e) {
                Log::error("Import Error {$platform} - {$trip['external_id']}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->createInvoiceDetail($invoiceIds, $platform);
        return ['imported' => $imported, 'errors' => $errors];
    }

    private function createInvoice(int $driverId, int $vehicleId): Invoice
    {
        return Invoice::updateOrCreate([
            'company_id' => auth()->user()->company_id,
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
        ], [
        ]);
    }

    private function createInvoiceDetail(array $invoiceIds, string $platform): Collection
    {
        $createdDetails = collect();

        foreach ($invoiceIds as $invoiceId) {
            // 1. Fetch Invoice with filtered Trips
            $invoice = Invoice::with(['trips' => function ($q) use ($platform) {
                $q->whereIn('status', ['finished', 'client_did_not_show'])
                    ->where('platform', $platform);
            }])->find($invoiceId);

            if (!$invoice) {
                continue; // Skip if invoice not found
            }

            $grossAmount = $invoice->trips->sum('gross_amount');
            $tips = $invoice->trips->sum('tips');
            $platformCommission = $invoice->trips->sum('platform_fee');

            $netPayout = $invoice->trips->sum('net_earnings');
            $collectedCash = $invoice->trips->sum('cash_collected');

            $detail = InvoiceDetail::updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'platform'   => $platform,
                ],
                [
                    'gross_amount'             => $grossAmount,
                    'platform_commission'      => $platformCommission,
                    'tip'                      => $tips,
                    'net_payout'               => $netPayout,
                    'cash_collected_by_driver' => $collectedCash,
                ]
            );

            $createdDetails->push($detail);
        }

        return $createdDetails;
    }}
