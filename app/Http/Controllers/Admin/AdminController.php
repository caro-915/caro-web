<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'    => User::count(),
            'proUsers' => User::whereHas('subscriptions', function($q) { 
                $q->where('payment_status', 'approved')
                  ->where('status', 'active')
                  ->where('expires_at', '>', now()); 
            })->count(),
            'annonces' => Annonce::count(),
            'active'   => Annonce::where('is_active', true)->count(),
            'pending'  => Annonce::where('is_active', false)->count(),
            'views'    => (int) (Annonce::sum('views') ?? 0),
        ];

        $latestAds = Annonce::with('user')
            ->latest()
            ->take(12)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestAds'));
    }
}
