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
        // Full list of car brands (for dropdown)
        $marques = [
            'Abarth', 'Acura', 'Aiways', 'Alfa Romeo', 'Alpine', 'Aston Martin', 'Audi',
            'BAIC', 'Bentley', 'BMW', 'Borgward', 'BRP (Can-Am, etc.)', 'Buick', 'BYD',
            'Cadillac', 'Changan', 'Changhe', 'Chevrolet', 'Chrysler', 'Citroën', 'Cupra', 'Chery', 'CFMoto',
            'Dacia', 'Daihatsu', 'Dodge', 'DS', 'Denza',
            'Ferrari', 'Fiat', 'Ford',
            'Genesis', 'GMC', 'Great Wall Motors', 'GAC',
            'Honda', 'Hummer', 'Hyundai', 'Hongqi',
            'Infiniti', 'Isuzu', 'Ineos',
            'Jaguar', 'Jeep', 'JMC',
            'Kia', 'Koenigsegg',
            'Lada', 'Lamborghini', 'Land Rover', 'Lexus', 'Lucid', 'Lotus',
            'Maserati', 'Mazda', 'McLaren', 'Mercedes-Benz', 'Mini', 'Mitsubishi', 'MG Motor', 'Maxus',
            'Nissan', 'Nio',
            'Opel',
            'Peugeot', 'Porsche', 'Polestar',
            'Renault', 'Rivian', 'Rolls-Royce',
            'Saab', 'SEAT', 'Skoda', 'Smart', 'SsangYong', 'Subaru', 'Suzuki',
            'Tata Motors', 'Tesla', 'Toyota',
            'VinFast', 'Vauxhall', 'Volkswagen', 'Volvo',
            'Wuling', 'Wey',
            'Zeekr', 'Zotye'
        ];
        
        // Models for old compatibility
        $modeles = CarModel::orderBy('name')->get();
        
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
            'popularModeles'
        ));
    }
}
