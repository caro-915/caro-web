<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Mapping marque -> modèles populaires (Algérie & Afrique)
        $brandModelsMap = [
            // Marques populaires en Algérie
            'Renault' => ['Clio', 'Megane', 'Symbol', 'Logan', 'Sandero', 'Duster', 'Captur', 'Kadjar', 'Talisman', 'Twingo', 'Kangoo'],
            'Peugeot' => ['208', '308', '2008', '3008', '301', '508', '407', '206', '307', '5008', 'Partner', 'Expert'],
            'Citroën' => ['C3', 'C4', 'C5', 'Berlingo', 'Jumpy', 'C-Elysée', 'C3 Aircross', 'C4 Cactus', 'Jumper'],
            'Dacia' => ['Sandero', 'Logan', 'Duster', 'Lodgy', 'Dokker', 'Spring'],
            'Volkswagen' => ['Golf', 'Polo', 'Passat', 'Tiguan', 'Jetta', 'Touareg', 'Caddy', 'T-Cross', 'T-Roc', 'Arteon'],
            'Hyundai' => ['i10', 'i20', 'Accent', 'Elantra', 'Tucson', 'Santa Fe', 'Kona', 'i30', 'Creta'],
            'Kia' => ['Picanto', 'Rio', 'Cerato', 'Sportage', 'Sorento', 'Optima', 'Stonic', 'Seltos', 'Carens'],
            'Toyota' => ['Corolla', 'Yaris', 'Hilux', 'Land Cruiser', 'RAV4', 'Camry', 'Prado', 'Fortuner', 'Avensis', 'Auris'],
            'Nissan' => ['Micra', 'Qashqai', 'X-Trail', 'Juke', 'Navara', 'Note', 'Patrol', 'Sentra', 'Altima'],
            'Ford' => ['Fiesta', 'Focus', 'Mondeo', 'Kuga', 'Ranger', 'EcoSport', 'Puma', 'Mustang', 'Transit'],
            'Opel' => ['Corsa', 'Astra', 'Insignia', 'Mokka', 'Grandland', 'Crossland', 'Combo', 'Vivaro'],
            'Seat' => ['Ibiza', 'Leon', 'Arona', 'Ateca', 'Tarraco', 'Toledo'],
            'Skoda' => ['Fabia', 'Octavia', 'Superb', 'Kodiaq', 'Karoq', 'Scala', 'Kamiq'],
            'Fiat' => ['500', 'Panda', 'Punto', 'Tipo', 'Doblo', '500X', 'Fiorino', 'Ducato'],
            
            // Marques africaines & asiatiques populaires
            'Honda' => ['Civic', 'Accord', 'CR-V', 'Jazz', 'HR-V', 'City', 'Fit'],
            'Mazda' => ['2', '3', '6', 'CX-3', 'CX-5', 'CX-30', 'MX-5'],
            'Mitsubishi' => ['Pajero', 'L200', 'ASX', 'Outlander', 'Lancer', 'Eclipse Cross'],
            'Suzuki' => ['Swift', 'Vitara', 'Baleno', 'Jimny', 'S-Cross', 'Celerio', 'Alto', 'Dzire'],
            'Isuzu' => ['D-Max', 'MU-X', 'Trooper'],
            'Mahindra' => ['Scorpio', 'XUV500', 'Thar', 'Bolero', 'KUV100'],
            'Tata Motors' => ['Indica', 'Indigo', 'Safari', 'Nexon', 'Harrier'],
            'Chery' => ['Tiggo', 'Arrizo', 'QQ'],
            'Geely' => ['Emgrand', 'Coolray', 'Atlas', 'GC6'],
            'JAC' => ['S3', 'S5', 'T6', 'J4'],
            
            // Premium
            'Mercedes-Benz' => ['Classe A', 'Classe B', 'Classe C', 'Classe E', 'Classe S', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'CLA', 'CLS', 'Vito', 'Sprinter'],
            'BMW' => ['Série 1', 'Série 2', 'Série 3', 'Série 4', 'Série 5', 'Série 7', 'X1', 'X3', 'X5', 'X6', 'X7', 'Z4'],
            'Audi' => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'TT'],
            
            // Autres marques internationales
            'Chevrolet' => ['Spark', 'Aveo', 'Cruze', 'Malibu', 'Captiva', 'Trax', 'Tahoe', 'Silverado'],
            'Jeep' => ['Renegade', 'Compass', 'Cherokee', 'Grand Cherokee', 'Wrangler'],
            'Land Rover' => ['Discovery', 'Range Rover', 'Range Rover Sport', 'Defender', 'Evoque'],
            'Lexus' => ['IS', 'ES', 'GS', 'LS', 'NX', 'RX', 'LX', 'UX'],
            'Porsche' => ['911', 'Cayenne', 'Macan', 'Panamera', 'Taycan', 'Boxster', 'Cayman'],
            'Tesla' => ['Model 3', 'Model S', 'Model X', 'Model Y'],
            'Volvo' => ['S60', 'S90', 'V60', 'V90', 'XC40', 'XC60', 'XC90'],
            'Mini' => ['Cooper', 'Clubman', 'Countryman', 'Paceman'],
            'Alfa Romeo' => ['Giulietta', 'Giulia', 'Stelvio', 'Tonale'],
            'DS' => ['DS3', 'DS4', 'DS7'],
            
            // Motos populaires
            'Yamaha' => ['YZF-R1', 'YZF-R6', 'MT-07', 'MT-09', 'TMAX', 'XMax', 'Aerox', 'FZ', 'Tenere'],
            'Honda Moto' => ['CBR', 'CB', 'CRF', 'PCX', 'SH', 'Africa Twin', 'Gold Wing'],
            'Kawasaki' => ['Ninja', 'Z', 'Versys', 'ZX', 'ER-6'],
            'Suzuki Moto' => ['GSX-R', 'Hayabusa', 'V-Strom', 'Burgman', 'Address'],
            'Ducati' => ['Monster', 'Panigale', 'Multistrada', 'Scrambler', 'Diavel'],
            'KTM' => ['Duke', 'RC', 'Adventure', 'Enduro'],
            'BMW Motorrad' => ['R1250', 'S1000RR', 'F750', 'F850', 'GS'],
            'Harley-Davidson' => ['Sportster', 'Softail', 'Touring', 'Street', 'V-Rod'],
        ];

        // Extraction des marques uniques
        $marques = array_keys($brandModelsMap);
        
        // Models for old compatibility
        $modeles = CarModel::orderBy('name')->get();

        // Enrichir avec les données de la BD
        $brandModelMap = CarBrand::with(['models' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->map(function (CarBrand $brand) use (&$brandModelsMap) {
                $models = $brand->models
                    ->pluck('name')
                    ->filter()
                    ->unique()
                    ->values();

                // Fusionner avec notre mapping statique
                if (isset($brandModelsMap[$brand->name])) {
                    $models = $models->merge($brandModelsMap[$brand->name])->unique()->values();
                } elseif ($models->isNotEmpty()) {
                    $brandModelsMap[$brand->name] = $models->all();
                }

                return $models->isNotEmpty()
                    ? [
                        'brand' => $brand->name,
                        'models' => $models->all(),
                    ]
                    : null;
            })
            ->filter()
            ->values()
            ->all();

        // Ajouter les marques de la BD qui n'existent pas déjà
        $marques = array_values(array_unique(array_merge(
            $marques,
            array_map(static fn (array $entry) => $entry['brand'], $brandModelMap)
        )));

        sort($marques, SORT_NATURAL | SORT_FLAG_CASE);
        
        // Query annonces
        $baseQuery = Annonce::query()
            ->where('is_active', true)
            ->latest();

        $filteredQuery = (clone $baseQuery)->filter($request->only([
            'marque',
            'modele',
            'price_max',
            'annee_min',
            'annee_max',
            'km_min',
            'km_max',
            'carburant',
            'wilaya',
            'vehicle_type',
        ]));

        $latestAds = (clone $filteredQuery)->take(6)->get();

        $topAnnonces = Annonce::with(['marque', 'modele'])
            ->where('is_active', true)
            ->orderBy('views', 'desc')
            ->take(3)
            ->get();

        $popularMarques = Annonce::select(
                DB::raw('marque as name'),
                DB::raw('COUNT(*) as annonces_count')
            )
            ->where('is_active', true)
            ->whereNotNull('marque')
            ->groupBy('marque')
            ->orderByDesc('annonces_count')
            ->take(8)
            ->get();

        $popularModeles = Annonce::select(
                DB::raw('modele as name'),
                DB::raw('COUNT(*) as annonces_count')
            )
            ->where('is_active', true)
            ->whereNotNull('modele')
            ->groupBy('modele')
            ->orderByDesc('annonces_count')
            ->take(8)
            ->get();
        
        return view('home', compact(
            'marques',
            'modeles',
            'latestAds',
            'topAnnonces',
            'popularMarques',
            'popularModeles',
            'brandModelMap',
            'brandModelsMap'
        ));
    }
}
