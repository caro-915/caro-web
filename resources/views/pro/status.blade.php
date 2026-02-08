@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Mon abonnement <span class="text-pink-600">PRO</span>
            </h1>
        </div>

        <!-- Current Subscription -->
        @if($subscription)
            <div class="bg-gradient-to-r from-pink-50 to-pink-100 border-2 border-pink-600 rounded-lg p-8 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-pink-600 text-sm font-semibold uppercase mb-2">Statut</p>
                        <p class="text-3xl font-bold text-pink-600">Actif</p>
                    </div>
                    <div>
                        <p class="text-pink-600 text-sm font-semibold uppercase mb-2">Expire le</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $subscription->expires_at->format('d/m/Y') }}</p>
                        <p class="text-pink-600 text-sm mt-1">dans {{ $subscription->expires_at->diffInDays(now()) }} jours</p>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-pink-200">
                    <h3 class="font-semibold text-gray-900 mb-4">Vos avantages :</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            50 annonces actives
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            5 boosts par mois
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Priorité dans les résultats
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Support prioritaire
                        </li>
                    </ul>
                </div>
            </div>
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 text-center">
                <p class="text-blue-900 mb-4">Vous n'avez pas d'abonnement PRO actif.</p>
                <a href="{{ route('pro.index') }}" class="inline-block py-2 px-6 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                    Découvrir les plans PRO
                </a>
            </div>
        @endif

        <!-- Pending Subscriptions -->
        @if($pendingSubscriptions->count() > 0)
            <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Demandes en attente</h2>
                
                <div class="space-y-4">
                    @foreach($pendingSubscriptions as $pendingSub)
                        <div class="border border-yellow-200 rounded-lg p-6 bg-yellow-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="font-semibold text-yellow-900">En attente de vérification</span>
                                    </div>
                                    <p class="text-yellow-800 text-sm mb-3">
                                        Votre preuve de paiement a été reçue le {{ $pendingSub->created_at->format('d/m/Y à H:i') }}.
                                        Nous la vérifierons dans les 24 heures.
                                    </p>

                                    @if($pendingSub->payment_status === 'rejected')
                                        <div class="bg-red-50 border border-red-200 rounded p-3 mb-3">
                                            <p class="text-red-900 text-sm">
                                                <span class="font-semibold">Raison du rejet :</span><br>
                                                {{ $pendingSub->rejection_reason }}
                                            </p>
                                        </div>
                                    @endif

                                    <div class="text-sm text-yellow-700">
                                        Plan : <span class="font-semibold">{{ $pendingSub->plan->name }}</span> 
                                        ({{ number_format($pendingSub->plan->price, 0, ',', ' ') }} DZD)
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 bg-yellow-200 text-yellow-900 rounded-full text-sm font-semibold">
                                        @if($pendingSub->payment_status === 'pending')
                                            En attente
                                        @elseif($pendingSub->payment_status === 'rejected')
                                            Rejeté
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if($pendingSub->payment_status === 'rejected')
                                <div class="mt-4 pt-4 border-t border-yellow-200">
                                    <a href="{{ route('pro.subscribe.form', $pendingSub->plan->id) }}" class="text-pink-600 hover:text-pink-700 font-semibold text-sm">
                                        Réessayer avec une nouvelle preuve →
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- My Ads Section -->
        <div class="mt-12 pt-12 border-t border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Mes annonces</h2>
            
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-gray-600 mb-4">
                    Accédez à vos annonces publiées et gérez vos boosts
                </p>
                <a href="{{ route('annonces.my') }}" class="inline-block py-2 px-6 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                    Voir mes annonces
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
