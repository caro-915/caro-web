<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'    => User::count(),
            'annonces' => Annonce::count(),
            'views'    => (int) (Annonce::sum('views') ?? 0), // si colonne views existe
        ];

        $latestAds = Annonce::with('user')
            ->latest()
            ->take(8)
            ->get();

        $latestUsers = User::latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestAds', 'latestUsers'));
    }
}
