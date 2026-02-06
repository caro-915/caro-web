@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8">
    <h1 class="text-2xl font-bold mb-6">Historique de recherche</h1>

    @if(session('success'))
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mes alertes actives --}}
    @if($alerts->count() > 0)
        <section class="mb-8">
            <h2 class="text-lg font-semibold mb-3">Mes alertes actives</h2>
            <div class="space-y-3">
                @foreach($alerts as $alert)
                    <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold">{{ $alert->search_label }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Créée le {{ $alert->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('search.alert.delete', $alert->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-xs text-red-600 hover:text-red-800 px-3 py-2 rounded-lg border border-red-200 hover:bg-red-50">
                                Supprimer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Historique des recherches --}}
    <section>
        <h2 class="text-lg font-semibold mb-3">Mes dernières recherches</h2>
        
        @if($searches->isEmpty())
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-sm text-gray-500">Vous n'avez pas encore effectué de recherche.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($searches as $search)
                    <div class="bg-white rounded-xl shadow p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-semibold">{{ $search->search_label }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $search->created_at->diffForHumans() }}
                                </p>
                                @if($search->price_max || $search->annee_min || $search->km_max)
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600">
                                        @if($search->price_max)
                                            <span class="bg-gray-100 px-2 py-1 rounded">
                                                Prix max: {{ number_format($search->price_max, 0, ',', ' ') }} DA
                                            </span>
                                        @endif
                                        @if($search->annee_min)
                                            <span class="bg-gray-100 px-2 py-1 rounded">
                                                Année min: {{ $search->annee_min }}
                                            </span>
                                        @endif
                                        @if($search->km_max)
                                            <span class="bg-gray-100 px-2 py-1 rounded">
                                                KM max: {{ number_format($search->km_max, 0, ',', ' ') }}
                                            </span>
                                        @endif
                                        @if($search->carburant && $search->carburant !== 'any')
                                            <span class="bg-gray-100 px-2 py-1 rounded">
                                                {{ $search->carburant }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4 flex flex-col gap-2">
                                <a href="{{ route('annonces.search', array_filter([
                                        'marque' => $search->marque,
                                        'modele' => $search->modele,
                                        'price_max' => $search->price_max,
                                        'annee_min' => $search->annee_min,
                                        'annee_max' => $search->annee_max,
                                        'km_min' => $search->km_min,
                                        'km_max' => $search->km_max,
                                        'carburant' => $search->carburant,
                                        'wilaya' => $search->wilaya,
                                        'vehicle_type' => $search->vehicle_type,
                                    ])) }}"
                                   class="text-xs text-gray-800 hover:text-gray-900 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 whitespace-nowrap">
                                    Refaire la recherche
                                </a>
                                <form method="POST" action="{{ route('search.alert.create') }}">
                                    @csrf
                                    <input type="hidden" name="search_id" value="{{ $search->id }}">
                                    <button type="submit"
                                            class="w-full text-xs text-pink-600 hover:text-pink-800 px-3 py-2 rounded-lg border border-pink-200 hover:bg-pink-50 whitespace-nowrap">
                                        🔔 Créer une alerte
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
