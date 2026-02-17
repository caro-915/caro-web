@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-pink-600 hover:text-pink-700 font-semibold">← Retour au tableau de bord</a>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">Plans PRO</h1>
                <p class="text-gray-600 mt-1">Créez et gérez les plans d'abonnement</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="py-2 px-4 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                + Ajouter un plan
            </a>
        </div>

        <!-- Plans Grid -->
        @if($plans->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($plans as $plan)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                            <span class="inline-block px-3 py-1 {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-sm font-semibold">
                                {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>

                        <div class="text-3xl font-bold text-pink-600 mb-4">
                            {{ number_format($plan->price, 0, ',', ' ') }} <span class="text-lg text-gray-600">DZD</span>
                        </div>

                        <ul class="space-y-2 mb-6 text-sm text-gray-700">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Durée : {{ $plan->duration_days }} jours
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Annonces : {{ $plan->features['max_active_ads'] }}
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Images : {{ $plan->features['max_images_per_ad'] ?? 4 }}/annonce
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Boosts : {{ $plan->features['boosts_per_month'] }}/mois
                            </li>
                        </ul>

                        <div class="flex gap-2 pt-4 border-t border-gray-200">
                            <a href="{{ route('admin.plans.edit', $plan->id) }}" class="flex-1 text-center py-2 px-3 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition text-sm">
                                Modifier
                            </a>
                            <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Êtes-vous sûr ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-2 px-3 border border-red-300 text-red-600 rounded-lg font-semibold hover:bg-red-50 transition text-sm">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div>
                {{ $plans->links() }}
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <p class="text-gray-600 mb-4">Aucun plan créé. Créez votre premier plan PRO.</p>
                <a href="{{ route('admin.plans.create') }}" class="inline-block py-2 px-6 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                    Créer un plan
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
