<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Invoice::factory()->count(3)
            ->has(InvoiceDetail::factory()->count(3), 'details')
            ->create();

    }
}
