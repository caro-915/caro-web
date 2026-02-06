<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SearchHistory;
use App\Models\SearchAlert;
use Illuminate\Http\Request;

class SearchHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

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

