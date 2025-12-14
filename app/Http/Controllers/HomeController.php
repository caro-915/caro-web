<?php

namespace App\Http\Controllers;

use App\Models\Annonce;

class HomeController extends Controller
{
    public function index()
    {
        $annonces = Annonce::latest()->take(10)->get();
        return view('home', compact('annonces'));
    }
}
