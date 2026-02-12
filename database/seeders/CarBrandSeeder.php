<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarBrand;

class CarBrandSeeder extends Seeder
{
    /**
     * Seed the car_brands table with comprehensive global brands.
     */
    public function run(): void
    {
        // Brand name => region
        $brands = [
            // Marques Françaises
            'Peugeot'        => 'FR',
            'Citroën'        => 'FR',
            'Renault'        => 'FR',
            'DS'             => 'FR',
            'Alpine'         => 'FR',
            'Bugatti'        => 'FR',

            // Marques Allemandes
            'Audi'           => 'DE',
            'BMW'            => 'DE',
            'Mercedes-Benz'  => 'DE',
            'Volkswagen'     => 'DE',
            'Porsche'        => 'DE',
            'Opel'           => 'DE',
            'Smart'          => 'DE',
            'Maybach'        => 'DE',

            // Marques Italiennes
            'Fiat'           => 'IT',
            'Ferrari'        => 'IT',
            'Lamborghini'    => 'IT',
            'Maserati'       => 'IT',
            'Alfa Romeo'     => 'IT',
            'Lancia'         => 'IT',
            'Abarth'         => 'IT',
            'Pagani'         => 'IT',

            // Marques Espagnoles
            'SEAT'           => 'ES',
            'Cupra'          => 'ES',

            // Marques Britanniques
            'Land Rover'     => 'GB',
            'Jaguar'         => 'GB',
            'Aston Martin'   => 'GB',
            'Bentley'        => 'GB',
            'Rolls-Royce'    => 'GB',
            'McLaren'        => 'GB',
            'Lotus'          => 'GB',
            'Mini'           => 'GB',
            'MG'             => 'GB',

            // Marques Japonaises
            'Toyota'         => 'JP',
            'Nissan'         => 'JP',
            'Honda'          => 'JP',
            'Mazda'          => 'JP',
            'Suzuki'         => 'JP',
            'Mitsubishi'     => 'JP',
            'Subaru'         => 'JP',
            'Lexus'          => 'JP',
            'Infiniti'       => 'JP',
            'Acura'          => 'JP',
            'Isuzu'          => 'JP',
            'Daihatsu'       => 'JP',

            // Marques de Motos
            'Yamaha'         => 'JP',
            'Kawasaki'       => 'JP',
            'Ducati'         => 'IT',
            'Harley-Davidson' => 'US',
            'KTM'            => 'AT',
            'Aprilia'        => 'IT',
            'Triumph'        => 'GB',
            'BMW Motorrad'   => 'DE',
            'Royal Enfield'  => 'IN',
            'Piaggio'        => 'IT',
            'Vespa'          => 'IT',

            // Marques Coréennes
            'Hyundai'        => 'KR',
            'Kia'            => 'KR',
            'Genesis'        => 'KR',
            'SsangYong'      => 'KR',

            // Marques Chinoises
            'BYD'            => 'CN',
            'Geely'          => 'CN',
            'Great Wall'     => 'CN',
            'Chery'          => 'CN',
            'MG Motor'       => 'CN',
            'NIO'            => 'CN',
            'Xpeng'          => 'CN',
            'Li Auto'        => 'CN',
            'Changan'        => 'CN',
            'BYD Auto'       => 'CN',
            'Hongqi'         => 'CN',
            'Maxus'          => 'CN',
            'Zeekr'          => 'CN',
            'Voyah'          => 'CN',

            // Marques Américaines
            'Ford'           => 'US',
            'Chevrolet'      => 'US',
            'GMC'            => 'US',
            'Cadillac'       => 'US',
            'Jeep'           => 'US',
            'Dodge'          => 'US',
            'Chrysler'       => 'US',
            'Tesla'          => 'US',
            'RAM'            => 'US',
            'Buick'          => 'US',
            'Lincoln'        => 'US',
            'Rivian'         => 'US',
            'Lucid'          => 'US',

            // Marques Tchèques
            'Skoda'          => 'CZ',

            // Marques Roumaines
            'Dacia'          => 'RO',

            // Marques Suédoises
            'Volvo'          => 'SE',
            'Saab'           => 'SE',
            'Polestar'       => 'SE',
            'Koenigsegg'     => 'SE',

            // Marques Indiennes
            'Tata'           => 'IN',
            'Mahindra'       => 'IN',

            // Marques Russes
            'Lada'           => 'RU',

            // Marques Autres
            'Tesla Motors'   => 'US',
        ];

        foreach ($brands as $name => $region) {
            CarBrand::updateOrCreate(
                ['name' => $name],
                ['region' => $region]
            );
        }
    }
}
