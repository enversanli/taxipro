<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $company = Company::first();
        $driver = Driver::inRandomOrder()->where('company_id', $company->id)->first();

        return [
            'company_id'    => $company->id, // assuming company is also stored in drivers table
            'driver_id'     => $driver->id,
            'vehicle_id'    => $company->vehicles->first()->id,
            'year'          => $this->faker->numberBetween(2024, 2025),
            'month'         => str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT),
            'total_income'  => $this->faker->randomFloat(2, 500, 5000),
            'gross'         => $this->faker->randomFloat(2, 500, 5000),
            'bar'           => $this->faker->randomFloat(2, 0, 500),
            'tip'           => $this->faker->randomFloat(2, 0, 300),
            'net'           => $this->faker->randomFloat(2, 500, 5000),
            'cash'          => $this->faker->randomFloat(2, 0, 2000),
            'driver_salary' => $this->faker->randomFloat(2, 1000, 4000),
        ];
    }
}
