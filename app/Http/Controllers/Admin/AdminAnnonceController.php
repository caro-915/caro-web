<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use Illuminate\Http\Request;

class AdminAnnonceController extends Controller
{
    /**
     * Liste des annonces (admin).
     */
    public function index(Request $request)
{
    $q = trim((string) $request->input('q', ''));
    $status = $request->input('status', 'all'); // all | active | inactive

    $annonces = Annonce::query()
        ->with('user')
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('titre', 'like', "%{$q}%")
                   ->orWhere('marque', 'like', "%{$q}%")
                   ->orWhere('modele', 'like', "%{$q}%")
                   ->orWhereHas('user', function ($u) use ($q) {
                       $u->where('name', 'like', "%{$q}%")
                         ->orWhere('email', 'like', "%{$q}%");
                   });
            });
        })
        ->when($status === 'active', fn ($query) => $query->where('is_active', true))
        ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('admin.annonces.index', compact('annonces', 'q', 'status'));
}

    /**
     * Activer / désactiver une annonce.
     */
    public function toggle(Annonce $annonce)
    {
        $annonce->is_active = ! (bool) $annonce->is_active;
        $annonce->save();

        return back()->with(
            'success',
            $annonce->is_active ? 'Annonce activée.' : 'Annonce désactivée.'
        );
    }

    /**
     * Supprimer une annonce.
     */
    public function destroy(Annonce $annonce)
    {
        $annonce->delete();

        return back()->with('success', 'Annonce supprimée.');
    }
}
