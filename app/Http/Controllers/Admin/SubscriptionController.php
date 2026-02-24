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

        // Try each disk in order: S3/R2 (persistent) → default → public (legacy)
        $disksToTry = [];

        if (!empty(config('filesystems.disks.s3.bucket'))) {
            $disksToTry[] = 's3';
        }

        $defaultDisk = config('filesystems.default');
        if (!in_array($defaultDisk, $disksToTry)) {
            $disksToTry[] = $defaultDisk;
        }

        if (!in_array('public', $disksToTry)) {
            $disksToTry[] = 'public';
        }

        foreach ($disksToTry as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if ($disk->exists($path) && $disk->size($path) > 0) {
                    $content = $disk->get($path);
                    $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
                    $filename = basename($path);

                    return response($content, 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                        'Content-Length' => strlen($content),
                        'Cache-Control' => 'no-cache',
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Proof lookup failed on disk [{$diskName}]", [
                    'subscription_id' => $subscription->id,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        // File not found on any disk — return a clear error page
        return response()->view('errors.proof-missing', [
            'subscription' => $subscription,
        ], 404);
    }

    /**
     * Check if proof exists (AJAX).
     */
    public function proofCheck(Subscription $subscription)
    {
        if (!$subscription->payment_proof_path) {
            return response()->json(['exists' => false]);
        }

        $path = $subscription->payment_proof_path;
        $disks = ['s3', config('filesystems.default'), 'public'];

        foreach (array_unique($disks) as $diskName) {
            try {
                if (empty($diskName)) continue;
                if ($diskName === 's3' && empty(config('filesystems.disks.s3.bucket'))) continue;
                if (Storage::disk($diskName)->exists($path) && Storage::disk($diskName)->size($path) > 0) {
                    return response()->json(['exists' => true, 'disk' => $diskName]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json(['exists' => false]);
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
