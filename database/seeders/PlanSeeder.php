<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::firstOrCreate(
            ['name' => 'Pro'],
            [
                'price' => 3000.00,
                'duration_days' => 30,
                'features' => [
                    'max_active_ads' => 50,
                    'boosts_per_month' => 5,
                    'boost_duration_days' => 7,
                ],
                'is_active' => true,
            ]
        );
    }
}
