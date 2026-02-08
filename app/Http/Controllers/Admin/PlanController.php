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
        ]);

        Plan::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'features' => [
                'max_active_ads' => $validated['max_active_ads'],
                'boosts_per_month' => $validated['boosts_per_month'],
                'boost_duration_days' => $validated['boost_duration_days'],
            ],
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan créé avec succès.');
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
        ]);

        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'is_active' => $validated['is_active'] ?? false,
            'features' => [
                'max_active_ads' => $validated['max_active_ads'],
                'boosts_per_month' => $validated['boosts_per_month'],
                'boost_duration_days' => $validated['boost_duration_days'],
            ],
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan mis à jour avec succès.');
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
