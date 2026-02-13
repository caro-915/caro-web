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
        // Plan PRO : 10 annonces, 8 images/annonce, boost, 1 mois
        Plan::firstOrCreate(
            ['name' => 'Pro'],
            [
                'price' => 3000.00,
                'duration_days' => 30,
                'features' => [
                    'max_active_ads' => 10,
                    'max_images_per_ad' => 8,
                    'boosts_per_month' => 5,
                    'boost_duration_days' => 7,
                ],
                'is_active' => true,
            ]
        );

        // Plan Premium : 5 annonces, 4 images/annonce, boost, 1 semaine  
        Plan::firstOrCreate(
            ['name' => 'Premium'],
            [
                'price' => 1500.00,
                'duration_days' => 7,
                'features' => [
                    'max_active_ads' => 5,
                    'max_images_per_ad' => 4,
                    'boosts_per_month' => 3,
                    'boost_duration_days' => 7,
                ],
                'is_active' => true,
            ]
        );
    }
}
