<?php

namespace App\Traits;

use Carbon\Carbon;

trait OrderTrait
{

    /**
     * MAP BOLT DATA TO OUR TABLE
     */
    protected function normalizeBoltOrders(array $boltOrders): array
    {
        $normalized = [];

        foreach ($boltOrders as $order) {
            $price = $order['order_price'] ?? [];

            $normalized[] = [
                'external_id'   => $order['order_reference'] ?? $order['order_id'] ?? 'Unknown',

                // Driver Matching
                'driver_name'   => $order['driver_name'] ?? null,
                'driver_phone'  => $order['driver_phone'] ?? null,
                'license_plate' => $order['vehicle_license_plate'] ?? null,

                // Locations
                'pickup_address' => $order['pickup_address'] ?? null,
                // Bolt often doesn't give a string for dropoff in the summary,
                // so we might leave it null or try to fetch it from stops if needed.
                'dropoff_address' => null,
                'distance'       => $order['ride_distance'] ?? 0,

                // Timestamps (Convert UNIX to Carbon)
                'ordered_at' => isset($order['order_created_timestamp']) ? Carbon::createFromTimestamp($order['order_created_timestamp']) : null,
                'pickup_at'  => isset($order['order_pickup_timestamp']) ? Carbon::createFromTimestamp($order['order_pickup_timestamp']) : null,
                'dropoff_at' => isset($order['order_drop_off_timestamp']) ? Carbon::createFromTimestamp($order['order_drop_off_timestamp']) : null,

                // Financials
                'gross_amount' => $price['ride_price'] ?? 0,
                'net_earnings' => $price['net_earnings'] ?? 0,
                'platform_fee' => $price['commission'] ?? 0,
                'tips'         => $price['tip'] ?? 0,


                'cash_collected' => ($order['payment_method'] === 'cash') ? ($price['ride_price'] ?? 0) : 0,
                'payment_method' => $order['payment_method'] ?? 'app',

                'status'   => $order['order_status'] ?? 'finished',
                'raw_data' => $order, // Save the whole array!
            ];
        }

        return $normalized;
    }
}
