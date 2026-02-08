<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPro
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !$this->subscriptionService->userIsPro(auth()->user())) {
            return abort(403, 'Vous devez avoir un abonnement PRO pour accéder à cette fonctionnalité.');
        }

        return $next($request);
    }
}
