<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $users = User::withCount('annonces')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function toggleAdmin(User $user)
    {
        // sécurité: éviter de se retirer soi-même (optionnel)
        if (auth()->id() === $user->id) {
            return back()->with('success', 'Action refusée sur votre propre compte.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return back()->with('success', 'Rôle admin mis à jour.');
    }

    public function toggleBan(User $user)
    {
        // sécurité: éviter de se bannir soi-même
        if (auth()->id() === $user->id) {
            return back()->with('success', 'Action refusée sur votre propre compte.');
        }

        $user->is_banned = !$user->is_banned;
        $user->save();

        return back()->with('success', 'Statut utilisateur mis à jour.');
    }
}
