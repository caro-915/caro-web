<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * List all plans.
     */
    public function index()
    {
        $plans = Plan::latest()->paginate(10);
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Create a new plan.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a new plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_active_ads' => 'required|integer|min:1',
            'boosts_per_month' => 'required|integer|min:0',
            'boost_duration_days' => 'required|integer|min:1',
        ], [
            'name.required' => 'Le nom du plan est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'duration_days.required' => 'La durée est obligatoire.',
            'duration_days.integer' => 'La durée doit être un nombre entier.',
            'max_active_ads.required' => 'Le nombre maximum d\'annonces est obligatoire.',
            'max_active_ads.min' => 'Le nombre maximum d\'annonces doit être au moins 1.',
            'boosts_per_month.required' => 'Le nombre de boosts par mois est obligatoire.',
            'boosts_per_month.integer' => 'Le nombre de boosts doit être un nombre entier.',
            'boost_duration_days.required' => 'La durée d\'un boost est obligatoire.',
            'boost_duration_days.min' => 'La durée d\'un boost doit être au moins 1 jour.',
        ]);

        Plan::create([
            'name' => $validated['name'],
            'price' => (float) $validated['price'],
            'duration_days' => (int) $validated['duration_days'],
            'features' => [
                'max_active_ads' => (int) $validated['max_active_ads'],
                'boosts_per_month' => (int) $validated['boosts_per_month'],
                'boost_duration_days' => (int) $validated['boost_duration_days'],
            ],
            'is_active' => true,
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan créé avec succès. Toutes les fonctionnalités ont été configurées.');
    }

    /**
     * Edit a plan.
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update a plan.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'max_active_ads' => 'required|integer|min:1',
            'boosts_per_month' => 'required|integer|min:0',
            'boost_duration_days' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom du plan est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'duration_days.required' => 'La durée est obligatoire.',
            'duration_days.integer' => 'La durée doit être un nombre entier.',
            'max_active_ads.required' => 'Le nombre maximum d\'annonces est obligatoire.',
            'max_active_ads.min' => 'Le nombre maximum d\'annonces doit être au moins 1.',
            'boosts_per_month.required' => 'Le nombre de boosts par mois est obligatoire.',
            'boosts_per_month.integer' => 'Le nombre de boosts doit être un nombre entier.',
            'boost_duration_days.required' => 'La durée d\'un boost est obligatoire.',
            'boost_duration_days.min' => 'La durée d\'un boost doit être au moins 1 jour.',
        ]);

        $plan->update([
            'name' => $validated['name'],
            'price' => (float) $validated['price'],
            'duration_days' => (int) $validated['duration_days'],
            'is_active' => $request->has('is_active'),
            'features' => [
                'max_active_ads' => (int) $validated['max_active_ads'],
                'boosts_per_month' => (int) $validated['boosts_per_month'],
                'boost_duration_days' => (int) $validated['boost_duration_days'],
            ],
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan mis à jour avec succès. Toutes les fonctionnalités ont été sauvegardées.');
    }

    /**
     * Delete a plan.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();
        return back()->with('success', 'Plan supprimé.');
    }
}
