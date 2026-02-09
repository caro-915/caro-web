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
        try {
            // Check if user owns the annonce
            if ($annonce->user_id !== auth()->id()) {
                return back()->with('error', 'Vous ne pouvez booster que vos propres annonces.');
            }

            \Log::info('🚀 Tentative de boost', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'annonce_id' => $annonce->id,
                'annonce_title' => $annonce->titre,
                'annonce_is_active' => $annonce->is_active,
            ]);

            $result = $this->boostService->canBoost(auth()->user(), $annonce);
            
            if (!$result['canBoost']) {
                \Log::warning('❌ Boost refusé', [
                    'reason' => $result['reason'],
                    'user_id' => auth()->id(),
                ]);
                return back()->with('error', $result['reason']);
            }

            $boost = $this->boostService->boostAnnonce(auth()->user(), $annonce);

            if (!$boost) {
                \Log::error('❌ Échec création boost', [
                    'user_id' => auth()->id(),
                    'annonce_id' => $annonce->id,
                ]);
                return back()->with('error', 'Une erreur est survenue lors du boost.');
            }

            \Log::info('✅ Boost créé avec succès', [
                'boost_id' => $boost->id,
                'expires_at' => $boost->expires_at,
            ]);

            return back()->with('success', 'Votre annonce a été boostée pour 7 jours !');
            
        } catch (\Exception $e) {
            \Log::error('❌ ERREUR BOOST - Exception capturée', [
                'user_id' => auth()->id(),
                'annonce_id' => $annonce->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'Une erreur serveur est survenue. Veuillez réessayer ou contacter le support.');
        }
    }
}
