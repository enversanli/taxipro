<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Driver::create([
            'company_id' => Company::skip(1)->first()->id,
            'first_name' => 'Test Driver',
            'last_name' => fake()->lastName,
            'phone' => fake()->phoneNumber,
            'address' => fake()->address,
            'work_model' => 'taxi',
        ]);

        Driver::factory()->count(3)->create();
    }
}
