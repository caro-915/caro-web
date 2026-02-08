@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Passez au <span class="text-pink-600">PRO</span>
            </h1>
            <p class="text-xl text-gray-600">
                Augmentez vos chances de vendre avec nos fonctionnalités premium
            </p>
        </div>

        <!-- User Status -->
        @if(auth()->user())
            @if($userSubscription)
                <div class="mb-8 bg-green-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-4">
                            <h3 class="font-semibold text-green-900">Vous êtes PRO</h3>
                            <p class="text-green-700 text-sm">Votre abonnement expire le {{ $userSubscription->expires_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <p class="text-blue-900">Vous n'êtes pas encore PRO. Découvrez nos plans ci-dessous.</p>
                </div>
            @endif
        @else
            <div class="mb-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <p class="text-yellow-900">
                    <a href="{{ route('login') }}" class="font-semibold text-yellow-900 underline">Connectez-vous</a> 
                    pour accéder aux plans PRO.
                </p>
            </div>
        @endif

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <!-- Free Plan (Informationnel) -->
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Gratuit</h3>
                <p class="text-gray-600 text-sm mb-6">Pour débuter</p>
                
                <div class="text-3xl font-bold text-gray-900 mb-6">
                    Gratuit
                </div>

                <ul class="space-y-4 mb-6">
                    <li class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Jusqu'à 5 annonces actives
                    </li>
                    <li class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Visibilité basique
                    </li>
                    <li class="flex items-center text-gray-700">
                        <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Pas de boosts
                    </li>
                </ul>

                <button disabled class="w-full py-2 px-4 border border-gray-300 rounded-lg text-gray-700 font-semibold opacity-50 cursor-not-allowed">
                    Actuellement gratuit
                </button>
            </div>

            <!-- PRO Plan -->
            @foreach($plans as $plan)
                <div class="border-2 border-pink-600 rounded-lg p-6 shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                    <div class="inline-block bg-pink-100 text-pink-600 px-3 py-1 rounded-full text-sm font-semibold mb-4">
                        Populaire
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                    <p class="text-gray-600 text-sm mb-6">{{ $plan->duration_days }} jours d'accès illimité</p>
                    
                    <div class="text-4xl font-bold text-pink-600 mb-6">
                        {{ number_format($plan->price, 0, ',', ' ') }} <span class="text-lg text-gray-600">DZD</span>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Jusqu'à {{ $plan->features['max_active_ads'] }} annonces actives
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ $plan->features['boosts_per_month'] }} boosts par mois
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Chaque boost dure {{ $plan->features['boost_duration_days'] }} jours
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Support prioritaire
                        </li>
                    </ul>

                    @if(auth()->user())
                        @if($userSubscription)
                            <button disabled class="w-full py-3 px-4 bg-pink-100 text-pink-600 rounded-lg font-semibold opacity-50 cursor-not-allowed">
                                Déjà actif
                            </button>
                        @else
                            <a href="{{ route('pro.subscribe.form', $plan->id) }}" class="block w-full text-center py-3 px-4 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                                S'abonner maintenant
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center py-3 px-4 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                            Se connecter
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Features Comparison -->
        <div class="bg-gray-50 rounded-lg p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Comparaison complète</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Fonctionnalité</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Gratuit</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">PRO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-4 text-gray-700">Annonces actives</td>
                            <td class="text-center py-4 px-4">5</td>
                            <td class="text-center py-4 px-4 font-semibold text-pink-600">50</td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-4 text-gray-700">Boosts par mois</td>
                            <td class="text-center py-4 px-4">0</td>
                            <td class="text-center py-4 px-4 font-semibold text-pink-600">5</td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-4 text-gray-700">Durée d'un boost</td>
                            <td class="text-center py-4 px-4">-</td>
                            <td class="text-center py-4 px-4 font-semibold text-pink-600">7 jours</td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-4 px-4 text-gray-700">Priorité de recherche</td>
                            <td class="text-center py-4 px-4">Basse</td>
                            <td class="text-center py-4 px-4 font-semibold text-pink-600">Haute</td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 text-gray-700">Support client</td>
                            <td class="text-center py-4 px-4">Email</td>
                            <td class="text-center py-4 px-4 font-semibold text-pink-600">Email + Chat</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FAQ -->
        <div class="bg-white rounded-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Questions fréquentes</h2>
            
            <div class="space-y-6">
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Comment s'effectue le paiement ?</h3>
                    <p class="text-gray-600">Le paiement s'effectue uniquement par transfert bancaire ou Mobile Money. Après avoir téléchargé votre preuve de paiement, nous la vérifierons dans un délai de 24 heures.</p>
                </div>
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Puis-je annuler mon abonnement ?</h3>
                    <p class="text-gray-600">Vous pouvez annuler votre abonnement à tout moment via votre profil. Vous conserverez l'accès PRO jusqu'à la date d'expiration.</p>
                </div>
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Que se passe-t-il si je ne vends pas tout ?</h3>
                    <p class="text-gray-600">Vous pouvez republier vos annonces tant que votre abonnement est actif. Il n'y a pas de limite au nombre de fois que vous pouvez les republier.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Comment fonctionnent les boosts ?</h3>
                    <p class="text-gray-600">Un boost met votre annonce en avant dans les résultats de recherche pendant 7 jours. Vous avez droit à 5 boosts par mois selon votre plan PRO.</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
