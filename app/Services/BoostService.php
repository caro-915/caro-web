<?php

namespace App\Services;

use App\Models\User;
use App\Models\Annonce;
use App\Models\Boost;
use Carbon\Carbon;

class BoostService
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Count active boosts this month for a user.
     */
    public function countBoostsThisMonth(User $user): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return Boost::where('user_id', $user->id)
            ->whereBetween('started_at', [$startOfMonth, $endOfMonth])
            ->count();
    }

    /**
     * Check if user can boost an annonce.
     */
    public function canBoost(User $user, Annonce $annonce): array
    {
        $canBoost = true;
        $reason = '';

        // Check if user is PRO
        if (!$this->subscriptionService->userIsPro($user)) {
            $canBoost = false;
            $reason = 'Vous devez avoir un abonnement PRO pour booster une annonce.';
            \Log::warning('❌ BOOST NON AUTORISÉ : Utilisateur non PRO', [
                'user_id' => $user->id,
                'annonce_id' => $annonce->id,
            ]);
            return compact('canBoost', 'reason');
        }

        // Check if annonce belongs to user
        if ($annonce->user_id !== $user->id) {
            $canBoost = false;
            $reason = "Vous ne pouvez booster que vos propres annonces.";
            \Log::warning('❌ BOOST NON AUTORISÉ : Annonce pas du propriétaire', [
                'user_id' => $user->id,
                'annonce_id' => $annonce->id,
                'actual_owner' => $annonce->user_id,
            ]);
            return compact('canBoost', 'reason');
        }

        // Check if annonce is active
        if (!$annonce->is_active) {
            $canBoost = false;
            $reason = "Vous ne pouvez booster qu'une annonce active.";
            \Log::warning('❌ BOOST NON AUTORISÉ : Annonce inactive', [
                'user_id' => $user->id,
                'annonce_id' => $annonce->id,
            ]);
            return compact('canBoost', 'reason');
        }

        // Check if already boosted
        if ($annonce->isBoosted()) {
            $canBoost = false;
            $reason = "Cette annonce est déjà boostée.";
            \Log::warning('❌ BOOST NON AUTORISÉ : Déjà boostée', [
                'user_id' => $user->id,
                'annonce_id' => $annonce->id,
            ]);
            return compact('canBoost', 'reason');
        }

        // Check monthly boost limit
        $features = $this->subscriptionService->getFeatures($user);
        $boostsThisMonth = $this->countBoostsThisMonth($user);

        if ($boostsThisMonth >= $features['boosts_per_month']) {
            $canBoost = false;
            $reason = "Vous avez atteint votre limite de {$features['boosts_per_month']} boosts ce mois-ci.";
            \Log::warning('❌ BOOST NON AUTORISÉ : Quota mensuel dépassé', [
                'user_id' => $user->id,
                'boosts_this_month' => $boostsThisMonth,
                'quota' => $features['boosts_per_month'],
            ]);
            return compact('canBoost', 'reason');
        }

        \Log::info('✅ BOOST AUTORISÉ', [
            'user_id' => $user->id,
            'annonce_id' => $annonce->id,
            'boosts_this_month' => $boostsThisMonth,
            'quota' => $features['boosts_per_month'],
        ]);

        return compact('canBoost', 'reason');
    }

    /**
     * Boost an annonce.
     */
    public function boostAnnonce(User $user, Annonce $annonce): ?Boost
    {
        $result = $this->canBoost($user, $annonce);
        if (!$result['canBoost']) {
            return null;
        }

        $features = $this->subscriptionService->getFeatures($user);

        return Boost::create([
            'annonce_id' => $annonce->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'expires_at' => now()->addDays($features['boost_duration_days']),
            'status' => 'active',
        ]);
    }

    /**
     * Expire old boosts.
     */
    public function expireOldBoosts(): int
    {
        return Boost::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}
