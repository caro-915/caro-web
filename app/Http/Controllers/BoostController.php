<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Services\BoostService;
use Illuminate\Http\Request;

class BoostController extends Controller
{
    protected BoostService $boostService;

    public function __construct(BoostService $boostService)
    {
        $this->boostService = $boostService;
    }

    /**
     * Boost an annonce.
     */
    public function store(Request $request, Annonce $annonce)
    {
        // Check if user owns the annonce
        if ($annonce->user_id !== auth()->id()) {
            return back()->with('error', 'Vous ne pouvez booster que vos propres annonces.');
        }

        $result = $this->boostService->canBoost(auth()->user(), $annonce);
        if (!$result['canBoost']) {
            return back()->with('error', $result['reason']);
        }

        $boost = $this->boostService->boostAnnonce(auth()->user(), $annonce);

        return back()->with('success', 'Votre annonce a été boostée pour 7 jours !');
    }
}
