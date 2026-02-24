<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class ProController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show PRO marketing page.
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)->get();
        $userSubscription = $this->subscriptionService->getActiveSubscription(auth()->user());

        return view('pro.index', compact('plans', 'userSubscription'));
    }

    /**
     * Show subscription creation form.
     */
    public function create(Plan $plan)
    {
        $this->authorize('create', Subscription::class);

        return view('pro.subscribe', compact('plan'));
    }

    /**
     * Store subscription with payment proof.
     */
    public function store(Request $request, Plan $plan)
    {
        $this->authorize('create', Subscription::class);

        $validated = $request->validate([
            'payment_proof' => 'required|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'payment_proof.required' => 'Veuillez télécharger une preuve de paiement.',
            'payment_proof.mimes' => 'Le fichier doit être un JPG, PNG ou PDF.',
            'payment_proof.max' => 'Le fichier ne doit pas dépasser 5 MB.',
        ]);

        // Store the payment proof
        $path = $request->file('payment_proof')->store('payment_proofs');

        // Create subscription with pending status
        $subscription = $this->subscriptionService->createSubscription(
            auth()->user(),
            $plan,
            $path
        );

        return redirect()->route('pro.status')
            ->with('success', 'Votre demande d\'abonnement a été reçue. Elle sera vérifiée dans les 24 heures.');
    }

    /**
     * Show subscription status.
     */
    public function status()
    {
        $subscription = $this->subscriptionService->getActiveSubscription(auth()->user());
        $pendingSubscriptions = auth()->user()
            ->subscriptions()
            ->where('payment_status', 'pending')
            ->latest()
            ->get();

        return view('pro.status', compact('subscription', 'pendingSubscriptions'));
    }
}
