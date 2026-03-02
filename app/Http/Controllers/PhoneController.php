<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneController extends Controller
{
    /**
     * Affiche le formulaire d'ajout/modification du téléphone.
     */
    public function edit()
    {
        return view('phone.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Met à jour le numéro de téléphone de l'utilisateur.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[\d\s\+\-\.]+$/',
            ],
        ], [
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.min'      => 'Le numéro doit contenir au moins 8 caractères.',
            'phone.max'      => 'Le numéro ne peut pas dépasser 20 caractères.',
            'phone.regex'    => 'Format invalide. Utilisez uniquement des chiffres, espaces, + ou -.',
        ]);

        // Nettoyer le numéro (garder uniquement chiffres et +)
        $cleanPhone = preg_replace('/[^0-9\+]/', '', $validated['phone']);

        $user = Auth::user();
        $user->phone = $cleanPhone;
        $user->save();

        return redirect()
            ->back()
            ->with('success', 'Votre numéro de téléphone a été enregistré avec succès !');
    }
}
