<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::skip(1)->first()->id,
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'phone' => fake()->phoneNumber,
            'address' => fake()->address,
            'work_model' => 'taxi',
        ];
    }
}
