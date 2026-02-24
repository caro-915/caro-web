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
        // Build brand → models maps from DB (split by vehicle_type)
        $carBrandsMap = [];
        $motoBrandsMap = [];

        CarBrand::with(['models' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->each(function (CarBrand $brand) use (&$carBrandsMap, &$motoBrandsMap) {
                $models = $brand->models->pluck('name')->filter()->unique()->sort()->values()->all();
                if ($brand->vehicle_type === 'Moto') {
                    $motoBrandsMap[$brand->name] = $models;
                } else {
                    $carBrandsMap[$brand->name] = $models;
                }
            });

        // Enrich car brands with hardcoded popular models for Algeria
        $staticCarModels = [
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
            'Mercedes-Benz' => ['Classe A', 'Classe B', 'Classe C', 'Classe E', 'Classe S', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'CLA', 'CLS', 'Vito', 'Sprinter'],
            'BMW' => ['Série 1', 'Série 2', 'Série 3', 'Série 4', 'Série 5', 'Série 7', 'X1', 'X3', 'X5', 'X6', 'X7', 'Z4'],
            'Audi' => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'TT'],
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
        ];

        foreach ($staticCarModels as $brand => $models) {
            $existing = $carBrandsMap[$brand] ?? [];
            $carBrandsMap[$brand] = array_values(array_unique(array_merge($existing, $models)));
            sort($carBrandsMap[$brand], SORT_NATURAL | SORT_FLAG_CASE);
        }

        // Combined map for backward compat (used in JS)
        $brandModelsMap = array_merge($carBrandsMap, $motoBrandsMap);
        
        // Sorted brand lists
        $marques = array_keys($carBrandsMap);
        sort($marques, SORT_NATURAL | SORT_FLAG_CASE);
        
        $marquesMotos = array_keys($motoBrandsMap);
        sort($marquesMotos, SORT_NATURAL | SORT_FLAG_CASE);
        
        // Models for old compatibility
        $modeles = CarModel::orderBy('name')->get();
        
        // Query annonces
        $baseQuery = Annonce::query()
            ->where('is_active', true)
            ->latest();

        $filteredQuery = (clone $baseQuery)->filter($request->only([
            'marque', 'modele', 'price_max', 'annee_min', 'annee_max',
            'km_min', 'km_max', 'carburant', 'wilaya', 'vehicle_type',
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
        
        // brandModelMap kept for backward compat
        $brandModelMap = [];
        
        return view('home', compact(
            'marques',
            'marquesMotos',
            'modeles',
            'latestAds',
            'topAnnonces',
            'popularMarques',
            'popularModeles',
            'brandModelMap',
            'brandModelsMap',
            'carBrandsMap',
            'motoBrandsMap'
        ));
    }
}
