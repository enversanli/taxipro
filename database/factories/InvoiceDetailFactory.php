<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceDetail>
 */
class InvoiceDetailFactory extends Factory
{
    protected $model = InvoiceDetail::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'platform'   => $this->faker->randomElement(['uber', 'bolt', 'bliq', 'freenow']),
            'gross'      => $this->faker->randomFloat(2, 100, 2000),
            'tip'        => $this->faker->randomFloat(2, 0, 200),
            'bar'        => $this->faker->randomFloat(2, 0, 100),
            'cash'       => $this->faker->randomFloat(2, 0, 500),
            'net'        => $this->faker->randomFloat(2, 100, 2000),
            'commission' => $this->faker->randomFloat(2, 0, 500),
        ];
    }
}
