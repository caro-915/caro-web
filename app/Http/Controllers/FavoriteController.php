<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // Liste des favoris
    public function index()
    {
        $user = Auth::user();

        $annonces = $user->favoriteAnnonces()
            ->with(['marque', 'modele'])
            ->latest('favorites.created_at')
            ->get();

        return view('favorites.index', compact('annonces'));
    }

    // Ajouter / retirer des favoris (toggle)
    public function toggle(Annonce $annonce)
    {
        $user = Auth::user();

        $existing = $annonce->favorites()
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Annonce retirée de vos favoris.';
        } else {
            $annonce->favorites()->create([
                'user_id' => $user->id,
            ]);
            $message = 'Annonce ajoutée à vos favoris.';
        }

        return back()->with('success', $message);
    }
}
