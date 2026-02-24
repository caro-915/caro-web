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
     * Stream the payment proof for admins.
     */
    public function proof(Subscription $subscription)
    {
        abort_unless($subscription->payment_proof_path, 404);

        $path = $subscription->payment_proof_path;

        // 1) Try S3/R2 first (production persistent storage)
        if (!empty(config('filesystems.disks.s3.bucket'))) {
            try {
                if (Storage::disk('s3')->exists($path)) {
                    return Storage::disk('s3')->response($path);
                }
            } catch (\Exception $e) {
                // S3 not reachable, continue to fallbacks
            }
        }

        // 2) Try default disk
        if (Storage::exists($path)) {
            return Storage::response($path);
        }

        // 3) Try public disk (old uploads)
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        // File is permanently lost (e.g. old upload on ephemeral filesystem)
        abort(404, 'Fichier de preuve introuvable. Il a peut-être été perdu lors d\'un redéploiement.');
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
