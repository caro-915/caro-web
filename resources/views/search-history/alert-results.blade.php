@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        {{-- Titre --}}
        <h1 class="text-3xl font-bold mb-6">Résultats de mes alertes</h1>

        {{-- Alertes actives --}}
        @if($alerts->count() > 0)
        <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h2 class="text-lg font-semibold mb-3">Mes alertes actives ({{ $alerts->count() }})</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($alerts as $alert)
                    <div class="bg-blue-100 text-blue-900 px-3 py-1 rounded-full text-sm font-medium">
                        {{ $alert->getSearchLabelAttribute() }}
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="mb-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-yellow-900">Vous n'avez pas d'alertes actives. <a href="{{ route('search.history') }}" class="font-semibold underline">Créez une alerte</a></p>
        </div>
        @endif

        {{-- Résultats --}}
        @if($annonces->count() > 0)
            <div class="space-y-4">
                @foreach($annonces as $annonce)
                    <a href="{{ route('annonces.show', ['annonce' => $annonce->id, 'slug' => $annonce->slug ?? Str::slug($annonce->titre)]) }}" class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden flex">
                        {{-- Image à gauche --}}
                        <div class="relative bg-gray-200 w-64 h-48 flex-shrink-0">
                            @if($annonce->image_path)
                                <img src="{{ asset('storage/' . $annonce->image_path) }}" alt="{{ $annonce->titre }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <span>Pas d'image</span>
                                </div>
                            @endif
                        </div>

                        {{-- Contenu à droite --}}
                        <div class="flex-1 p-4 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-lg">{{ $annonce->titre }}</h3>
                                    <span class="text-pink-600 font-bold text-xl ml-4 flex-shrink-0">
                                        {{ number_format($annonce->prix, 0, '.', ' ') }} DA
                                    </span>
                                </div>
                                
                                @if($annonce->is_active)
                                    <span class="inline-block bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded mb-2">
                                        🔥 Annonce populaire
                                    </span>
                                @endif

                                <p class="text-sm text-gray-700 mb-2">
                                    <strong>{{ $annonce->marque }}</strong> • {{ $annonce->modele }}
                                </p>

                                <p class="text-sm text-gray-600">
                                    {{ $annonce->annee ?? '-' }} • {{ $annonce->kilometrage ? number_format($annonce->kilometrage) . ' km' : '-' }} • 
                                    {{ $annonce->carburant ?? '-' }} • {{ $annonce->boite_vitesse ?? 'Manuelle' }} {{ $annonce->boite_vitesse ? $annonce->boite_vitesse . ' vue(s)' : '' }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between mt-3 text-sm text-gray-500">
                                <span>📍 {{ $annonce->ville ?? 'Alger' }}</span>
                                <button class="text-gray-400 hover:text-pink-600">
                                    ♡ Ajouter aux favoris
                                </button>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $annonces->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg p-8 text-center">
                <p class="text-gray-600 mb-4">Aucune annonce ne correspond à vos alertes pour le moment.</p>
                <a href="{{ route('home') }}" class="text-pink-600 hover:text-pink-700 font-semibold">Retourner à l'accueil</a>
            </div>
        @endif
    </div>
</div>
@endsection
