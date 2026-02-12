<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * List all pending subscriptions.
     */
    public function index()
    {
        $pendingSubscriptions = Subscription::where('payment_status', 'pending')
            ->with(['user', 'plan'])

    /**
     * Stream the payment proof for admins.
     */
    public function proof(Subscription $subscription)
    {
        abort_unless($subscription->payment_proof_path, 404);

        $disk = 'public';

        if (!Storage::disk($disk)->exists($subscription->payment_proof_path)) {
            abort(404);
        }

        return Storage::disk($disk)->response($subscription->payment_proof_path);
    }
            ->latest()
            ->paginate(15);

        $approvedSubscriptions = Subscription::where('payment_status', 'approved')
            ->with(['user', 'plan'])
            ->latest()
            ->paginate(15);

        return view('admin.subscriptions.index', compact('pendingSubscriptions', 'approvedSubscriptions'));
    }

    /**
     * Show a subscription detail page.
     */
    public function show(Subscription $subscription)
    {
        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Approve a subscription payment.
     */
    public function approve(Request $request, Subscription $subscription)
    {
        $this->subscriptionService->approveSubscription($subscription);

        return back()->with('success', 'Abonnement approuvé.');
    }

    /**
     * Reject a subscription payment.
     */
    public function reject(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Veuillez fournir une raison.',
        ]);

        $this->subscriptionService->rejectSubscription($subscription, $validated['reason']);

        return back()->with('success', 'Abonnement rejeté.');
    }
}
