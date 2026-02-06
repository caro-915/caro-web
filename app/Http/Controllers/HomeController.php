<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\CarBrand;

class HomeController extends Controller
{
    public function index()
    {
        $annonces = Annonce::latest()->take(10)->get();
        
        // Full list of car brands
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
        
        return view('home', compact('annonces', 'marques'));
    }
}
