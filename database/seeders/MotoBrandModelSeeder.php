<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarBrand;
use App\Models\CarModel;

class MotoBrandModelSeeder extends Seeder
{
    /**
     * Seed moto brands + models into the existing car_brands / car_models tables.
     */
    public function run(): void
    {
        $motoBrands = [
            'Yamaha' => [
                'YZF-R1', 'YZF-R3', 'YZF-R6', 'YZF-R125',
                'MT-03', 'MT-07', 'MT-09', 'MT-10', 'MT-125',
                'TMAX', 'XMAX', 'NMAX', 'Aerox',
                'Ténéré 700', 'Tracer 9', 'FZ',
                'XSR 700', 'XSR 900',
            ],
            'Kawasaki' => [
                'Ninja 400', 'Ninja 650', 'Ninja ZX-6R', 'Ninja ZX-10R', 'Ninja H2',
                'Z400', 'Z650', 'Z900', 'Z H2',
                'Versys 650', 'Versys 1000',
                'Vulcan S', 'ER-6n', 'ER-6f',
                'KLX 150', 'KX 250',
            ],
            'Ducati' => [
                'Monster', 'Monster 821', 'Monster 1200',
                'Panigale V2', 'Panigale V4',
                'Multistrada V4', 'Multistrada 950',
                'Scrambler Icon', 'Scrambler Desert Sled',
                'Diavel', 'Streetfighter V4',
                'Hypermotard 950', 'SuperSport 950',
            ],
            'Harley-Davidson' => [
                'Sportster S', 'Iron 883', 'Forty-Eight',
                'Street Bob', 'Fat Boy', 'Softail Standard',
                'Road King', 'Street Glide', 'Road Glide', 'Electra Glide',
                'Pan America', 'Nightster', 'V-Rod',
            ],
            'KTM' => [
                'Duke 125', 'Duke 200', 'Duke 390', 'Duke 690', 'Duke 790', 'Duke 890', 'Duke 1290',
                'RC 125', 'RC 200', 'RC 390',
                'Adventure 390', 'Adventure 790', 'Adventure 890', 'Adventure 1290',
                'EXC 300', 'SX-F 450',
            ],
            'Aprilia' => [
                'RS 125', 'RS 660', 'RSV4',
                'Tuono 660', 'Tuono V4',
                'Shiver 900', 'Dorsoduro 900',
                'SR GT', 'SXR 160',
            ],
            'Triumph' => [
                'Street Triple', 'Speed Triple',
                'Tiger 660', 'Tiger 900', 'Tiger 1200',
                'Bonneville T100', 'Bonneville T120',
                'Scrambler 900', 'Scrambler 1200',
                'Rocket 3', 'Trident 660', 'Thruxton RS',
            ],
            'BMW Motorrad' => [
                'R 1250 GS', 'R 1250 RT', 'R 1250 RS',
                'F 750 GS', 'F 850 GS', 'F 900 R', 'F 900 XR',
                'S 1000 RR', 'S 1000 R', 'S 1000 XR',
                'G 310 R', 'G 310 GS',
                'R nineT', 'C 400 X', 'CE 04',
            ],
            'Royal Enfield' => [
                'Classic 350', 'Bullet 350', 'Meteor 350',
                'Hunter 350', 'Continental GT 650', 'Interceptor 650',
                'Himalayan', 'Scram 411', 'Super Meteor 650',
            ],
            'Piaggio' => [
                'Liberty 125', 'Medley 125', 'Beverly 300',
                'MP3 300', 'MP3 500',
                'X10 350',
            ],
            'Vespa' => [
                'Primavera 125', 'Sprint 125',
                'GTS 125', 'GTS 300',
                'Elettrica',
            ],
            // Additional popular moto brands in Algeria
            'Honda Moto' => [
                'CBR 600RR', 'CBR 1000RR', 'CBR 250R', 'CBR 500R',
                'CB 125R', 'CB 300R', 'CB 500F', 'CB 650R', 'CB 1000R',
                'CRF 250L', 'CRF 300L', 'CRF 1100L Africa Twin',
                'PCX 125', 'SH 125', 'SH 300', 'Forza 350',
                'Gold Wing', 'NC 750X', 'X-ADV',
            ],
            'Suzuki Moto' => [
                'GSX-R 600', 'GSX-R 750', 'GSX-R 1000', 'GSX-S 750', 'GSX-S 1000',
                'Hayabusa', 'V-Strom 650', 'V-Strom 1050',
                'Burgman 400', 'Address 110',
                'SV 650', 'DR-Z 400',
            ],
            'Benelli' => [
                'TNT 125', 'TNT 300', 'TNT 600',
                'Leoncino 500', 'TRK 502', 'TRK 502 X',
                '752 S',
            ],
            'SYM' => [
                'Symphony', 'Fiddle', 'Maxsym 400',
                'NH-X', 'Cruisym 300',
            ],
            'Kymco' => [
                'Agility 125', 'Like 125', 'People S 125',
                'Downtown 350', 'AK 550', 'X-Town 300',
            ],
        ];

        foreach ($motoBrands as $brandName => $models) {
            // Create or update brand
            $brand = CarBrand::updateOrCreate(
                ['name' => $brandName],
                ['vehicle_type' => 'Moto']
            );

            // Seed models
            foreach ($models as $modelName) {
                CarModel::updateOrCreate(
                    [
                        'car_brand_id' => $brand->id,
                        'name'         => $modelName,
                    ]
                );
            }
        }
    }
}
