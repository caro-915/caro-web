<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Annonce;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function show(User $user)
    {
        // Annonces du vendeur (tu peux filtrer status=active si tu ajoutes ce champ plus tard)
        $annonces = Annonce::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        // Stats simples
        $totalAds = Annonce::where('user_id', $user->id)->count();

        return view('sellers.show', [
            'seller'   => $user,
            'annonces' => $annonces,
            'totalAds' => $totalAds,
        ]);
    }
}
