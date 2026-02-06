<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use App\Models\SearchAlert;
use App\Models\Annonce;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    public function index()
    {
        $searches = SearchHistory::where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        $alerts = SearchAlert::where('user_id', auth()->id())
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('search-history.index', compact('searches', 'alerts'));
    }

    public function alertResults()
    {
        // Récupérer toutes les alertes actives de l'utilisateur
        $alerts = SearchAlert::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        if ($alerts->isEmpty()) {
            return view('search-history.alert-results', ['annonces' => collect([]), 'alerts' => collect([])]);
        }

        // Construire une requête qui récupère les annonces matchant AU MOINS UNE alerte
        $query = Annonce::where('is_active', true);

        $query->where(function ($mainQuery) use ($alerts) {
            foreach ($alerts as $alert) {
                $mainQuery->orWhere(function ($alertQuery) use ($alert) {
                    // Pour cette alerte, TOUS les critères doivent matcher (AND)
                    
                    if ($alert->marque) {
                        $alertQuery->where('marque', 'like', '%' . $alert->marque . '%');
                    }
                    
                    if ($alert->modele) {
                        $alertQuery->where('modele', 'like', '%' . $alert->modele . '%');
                    }
                    
                    if ($alert->price_max) {
                        $alertQuery->where('prix', '<=', $alert->price_max);
                    }
                    
                    if ($alert->annee_min) {
                        $alertQuery->where('annee', '>=', $alert->annee_min);
                    }
                    
                    if ($alert->annee_max) {
                        $alertQuery->where('annee', '<=', $alert->annee_max);
                    }
                    
                    if ($alert->km_min) {
                        $alertQuery->where('kilometrage', '>=', $alert->km_min);
                    }
                    
                    if ($alert->km_max) {
                        $alertQuery->where('kilometrage', '<=', $alert->km_max);
                    }
                    
                    if ($alert->carburant) {
                        $alertQuery->where('carburant', $alert->carburant);
                    }
                    
                    if ($alert->wilaya) {
                        $alertQuery->where('ville', 'like', '%' . $alert->wilaya . '%');
                    }
                    
                    if ($alert->vehicle_type) {
                        $alertQuery->where('vehicle_type', $alert->vehicle_type);
                    }
                });
            }
        });

        $annonces = $query->with('user')
            ->latest()
            ->paginate(12);

        return view('search-history.alert-results', compact('annonces', 'alerts'));
    }

    public function createAlert(Request $request)
    {
        $searchId = $request->input('search_id');
        $search = SearchHistory::where('id', $searchId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        SearchAlert::create([
            'user_id' => auth()->id(),
            'marque' => $search->marque,
            'modele' => $search->modele,
            'price_max' => $search->price_max,
            'annee_min' => $search->annee_min,
            'annee_max' => $search->annee_max,
            'km_min' => $search->km_min,
            'km_max' => $search->km_max,
            'carburant' => $search->carburant,
            'wilaya' => $search->wilaya,
            'vehicle_type' => $search->vehicle_type,
        ]);

        return back()->with('success', 'Alerte de recherche créée avec succès !');
    }

    public function deleteAlert($id)
    {
        SearchAlert::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Alerte supprimée avec succès !');
    }
}

