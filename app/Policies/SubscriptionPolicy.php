<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    /**
     * Determine if user can create a new subscription.
     */
    public function create(User $user): bool
    {
        // User must be logged in
        return auth()->check();
    }
}
