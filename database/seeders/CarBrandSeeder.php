<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarBrand;

class CarBrandSeeder extends Seeder
{
    /**
     * Seed the car_brands table.
     */
    public function run(): void
    {
        // Brand name => region (juste indicatif)
        $brands = [
            'Peugeot'        => 'FR',
            'Citroën'        => 'FR',
            'Renault'        => 'FR',

            'Audi'           => 'DE',
            'BMW'            => 'DE',
            'Mercedes-Benz'  => 'DE',
            'Volkswagen'     => 'DE',

            'Skoda'          => 'CZ',
            'SEAT'           => 'ES',

            'Hyundai'        => 'KR',
            'Kia'            => 'KR',

            'Toyota'         => 'JP',
            'Nissan'         => 'JP',
        ];

        foreach ($brands as $name => $region) {
            CarBrand::updateOrCreate(
                ['name' => $name],
                ['region' => $region]
            );
        }
    }
}
