<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::updateOrCreate([
            'owner_id' => User::first()->id,
            'name' => 'Tulpar Taxi',
        ],[
            'address' => 'Wedding, Berlin',
            'phone' => '01790000000'
        ]);
    }
}
