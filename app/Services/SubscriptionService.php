<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Get the active subscription for a user.
     */
    public function getActiveSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where('payment_status', 'approved')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Check if user is PRO.
     */
    public function userIsPro(User $user): bool
    {
        return $this->getActiveSubscription($user) !== null;
    }

    /**
     * Get user's subscription features.
     */
    public function getFeatures(User $user): array
    {
        // Free plan defaults
        $defaults = [
            'max_active_ads' => 5,
            'boosts_per_month' => 0,
            'boost_duration_days' => 7,
        ];

        $subscription = $this->getActiveSubscription($user);
        if (!$subscription) {
            return $defaults;
        }

        // Merge plan features with defaults to ensure all keys exist
        $planFeatures = $subscription->plan->features ?? [];
        return array_merge($defaults, $planFeatures);
    }

    /**
     * Create a subscription with payment proof.
     */
    public function createSubscription(User $user, Plan $plan, string $paymentProofPath): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
            'status' => 'active',
            'payment_proof_path' => $paymentProofPath,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Approve a subscription payment.
     */
    public function approveSubscription(Subscription $subscription): void
    {
        $startedAt = now();
        $expiresAt = now()->addDays($subscription->plan->duration_days);
        
        \Log::info('✅ ACTIVATION ABONNEMENT', [
            'user_id' => $subscription->user_id,
            'plan' => $subscription->plan->name,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
            'payment_status' => 'approved',
        ]);
        
        $subscription->update([
            'payment_status' => 'approved',
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Reject a subscription payment.
     */
    public function rejectSubscription(Subscription $subscription, string $reason): void
    {
        $subscription->update([
            'payment_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Expire subscriptions that have passed expiry date.
     */
    public function expireOldSubscriptions(): int
    {
        return Subscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}
