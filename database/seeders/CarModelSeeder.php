<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarBrand;
use App\Models\CarModel;

class CarModelSeeder extends Seeder
{
    /**
     * Seed the car_models table.
     */
    public function run(): void
    {
        /**
         * Chaque entrée : 'Nom de la marque' => [liste des modèles]
         * Tu pourras compléter / ajuster la liste quand tu veux.
         */
        $data = [
            // FR – Constructeurs généralistes
            'Peugeot' => [
                '208',
                '308',
                '508',
            ],
            'Citroën' => [
                'C3',
                'C4',
                'C4 X',
                'C5 X',
            ],
            'Renault' => [
                'Clio',
                'Twingo',
                'Captur',
                'Austral',
                'Mégane E-Tech',
                'Scénic E-Tech',
                'Talisman',
            ],

            // DE – Premium
            'Audi' => [
                'A1',
                'A3',
                'A4',
                'A5',
                'A6',
                'A7',
                'A8',
                'Q2',
                'Q3',
                'Q4 e-tron',
                'Q5',
                'Q7',
                'Q8',
                'e-tron GT',
                'Q4 e-tron',
                'Q6 e-tron',
            ],
            'BMW' => [
                'Série 1',
                'Série 2',
                'Série 3',
                'Série 4',
                'Série 5',
                'Série 6',
                'Série 7',
                'Série 8',
                'X1',
                'X2',
                'X3',
                'X4',
                'X5',
                'X6',
                'X7',
            ],
            'Mercedes-Benz' => [
                'Classe A',
                'Classe C',
                'Classe E',
                'Classe S',
                'GLA',
                'GLB',
                'GLC',
                'GLE',
                'GLS',
            ],

            // Groupe Volkswagen
            'Volkswagen' => [
                'up!',
                'Polo',
                'Golf',
                'Taigo',
                'T-Cross',
                'T-Roc',
                'Tiguan',
                'Touareg',
                'ID.3',
                'ID.4',
                'ID.5',
                'ID.7',
                'ID. Buzz',
            ],
            'Skoda' => [
                'Fabia',
                'Scala',
                'Octavia',
                'Kamiq',
                'Karoq',
                'Kodiaq',
                'Superb',
                'Enyaq',
            ],
            'SEAT' => [
                'Ibiza',
                'Leon',
                'Arona',
                'Ateca',
                'Tarraco',
            ],

            // Coréens
            'Hyundai' => [
                'i10',
                'i20',
                'i30',
                'Bayon',
                'Kona',
                'Tucson',
                'Santa Fe',
                'IONIQ 5',
                'IONIQ 6',
                'KONA Electric',
                'NEXO',
            ],
            'Kia' => [
                'Picanto',
                'Rio',
                'Ceed',
                'Stonic',
                'XCeed',
                'Niro',
                'Sportage',
                'Sorento',
                'EV6',
            ],

            // Japonais
            'Toyota' => [
                'Aygo X',
                'Yaris',
                'Corolla',
                'Yaris Cross',
                'C-HR',
                'RAV4',
                'Land Cruiser',
                'Hilux',
            ],
            'Nissan' => [
                'Micra',
                'Leaf',
                'Juke',
                'Qashqai',
                'X-Trail',
                'Navara',
            ],
        ];

        foreach ($data as $brandName => $models) {
            $brand = CarBrand::where('name', $brandName)->first();

            if (! $brand) {
                // Si jamais la marque n'existe pas (seed non lancé ou modifié),
                // on la saute pour éviter une erreur.
                continue;
            }

            foreach ($models as $modelName) {
                CarModel::updateOrCreate(
                    [
                        'car_brand_id' => $brand->id,
                        'name'         => $modelName,
                    ],
                    [] // pas d'autres colonnes pour le moment
                );
            }
        }
    }
}
