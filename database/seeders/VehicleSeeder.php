<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vehicles')->insert([
            [
                'company_id' => Company::first()->id,
                'license_plate' => 'B-TA 1001',
                'model' => 'Mercedes-Benz E-Class',
                'usage_type' => 'taxi',
                'color' => '#FFD700', // Gold
                'brand' => 'BMW',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => Company::first()->id,
                'license_plate' => 'B-RE 2024',
                'model' => 'Volkswagen Passat',
                'usage_type' => 'rent',
                'brand' => 'BMW',
                'color' => '#0000FF', // Blue
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => Company::first()->id,
                'license_plate' => 'B-PC 3333',
                'model' => 'BMW 5 Series',
                'brand' => 'BMW',
                'usage_type' => 'taxi',
                'color' => '#FFFFFF', // White
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => Company::first()->id,
                'license_plate' => 'B-RN 8080',
                'model' => 'Skoda Superb',
                'brand' => 'Skoda',
                'usage_type' => 'rent',
                'color' => '#808080', // Gray
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
