@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8">
    {{-- Title + total results --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-2 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">
                Voitures d'occasion
                @if($annonces->total())
                    <span class="text-pink-600 font-extrabold">
                        – {{ number_format($annonces->total(), 0, ',', ' ') }} annonces
                    </span>
                @endif
            </h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1">
                Affinez votre recherche avec les filtres à gauche.
            </p>
        </div>

        {{-- Sort selector (connecté au contrôleur) --}}
        <div class="mt-2 md:mt-0">
            <label class="text-xs text-gray-500 block mb-1">Trier par :</label>

            <form method="GET" action="{{ route('annonces.search') }}">
                {{-- On garde tous les filtres existants sauf sort & page --}}
                @foreach(request()->except(['sort','page']) as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach

                @php
                    $currentSort = request('sort', 'latest');
                @endphp

                <select name="sort"
                        class="border rounded-lg px-3 py-2 text-xs md:text-sm"
                        onchange="this.form.submit()">
                    <option value="latest"     {{ $currentSort === 'latest' ? 'selected' : '' }}>Les plus récentes</option>
                    <option value="price_asc"  {{ $currentSort === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                    <option value="price_desc" {{ $currentSort === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                    <option value="km_asc"     {{ $currentSort === 'km_asc' ? 'selected' : '' }}>Km croissant</option>
                    <option value="km_desc"    {{ $currentSort === 'km_desc' ? 'selected' : '' }}>Km décroissant</option>
                    <option value="year_desc"  {{ $currentSort === 'year_desc' ? 'selected' : '' }}>Année décroissante</option>
                    <option value="year_asc"   {{ $currentSort === 'year_asc' ? 'selected' : '' }}>Année croissante</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Main layout: filters / results / side panel --}}
    <div class="grid grid-cols-1 md:grid-cols-[260px,minmax(0,1fr)] lg:grid-cols-[260px,minmax(0,1fr),260px] gap-4 md:gap-6">
        {{-- LEFT COLUMN – Filters --}}
        <aside class="bg-white rounded-2xl shadow p-4 space-y-4">
            <h2 class="text-sm font-semibold mb-1">Filtres</h2>

            {{-- Filters form --}}
            <form method="GET" action="{{ route('annonces.search') }}" class="space-y-4">
                {{-- Keep existing query params (useful when changing one filter) --}}
                @foreach(request()->except(['page']) as $name => $value)
                    @if(!in_array($name, [
                        'marque','modele','price_max',
                        'annee_min','annee_max',
                        'km_min','km_max',
                        'carburant','wilaya',
                        'vehicle_type','boite_vitesse',
                        'q','sort'
                    ]))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach

                {{-- Garder le tri actuel quand on applique les filtres --}}
                <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

                {{-- Vehicle type --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Type de véhicule</label>
                    <div class="flex flex-wrap gap-2 text-xs">
                        @php
                            $types = ['car' => 'Voiture', 'van' => 'Utilitaire', 'moto' => 'Moto'];
                            $currentType = request('vehicle_type');
                        @endphp
                        @foreach($types as $value => $label)
                            <button type="submit"
                                    name="vehicle_type"
                                    value="{{ $value }}"
                                    class="px-3 py-1 rounded-full border 
                                        {{ $currentType === $value ? 'bg-pink-600 text-white border-pink-600' : 'bg-white text-gray-700 border-gray-200' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Brand --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Marque</label>
                    <input type="text"
                           name="marque"
                           value="{{ request('marque') }}"
                           placeholder="ex : Renault"
                           class="w-full border rounded-lg px-2 py-2 text-xs">
                </div>

                {{-- Model --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Modèle</label>
                    <input type="text"
                           name="modele"
                           value="{{ request('modele') }}"
                           placeholder="ex : Clio"
                           class="w-full border rounded-lg px-2 py-2 text-xs">
                </div>

                {{-- Year range --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Année</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="annee_min"
                               value="{{ request('annee_min') }}"
                               placeholder="Min"
                               class="border rounded-lg px-2 py-2 text-xs">
                        <input type="number" name="annee_max"
                               value="{{ request('annee_max') }}"
                               placeholder="Max"
                               class="border rounded-lg px-2 py-2 text-xs">
                    </div>
                </div>

                {{-- Mileage range --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Kilométrage</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="km_min"
                               value="{{ request('km_min') }}"
                               placeholder="Min km"
                               class="border rounded-lg px-2 py-2 text-xs">
                        <input type="number" name="km_max"
                               value="{{ request('km_max') }}"
                               placeholder="Max km"
                               class="border rounded-lg px-2 py-2 text-xs">
                    </div>
                </div>

                {{-- Energy --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Carburant</label>
                    @php
                        $carb = request('carburant', 'any');
                    @endphp
                    <div class="space-y-1 text-xs">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="carburant" value="any" {{ $carb === 'any' ? 'checked' : '' }}>
                            <span>Peu importe</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="carburant" value="Diesel" {{ $carb === 'Diesel' ? 'checked' : '' }}>
                            <span>Diesel</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="carburant" value="Essence" {{ $carb === 'Essence' ? 'checked' : '' }}>
                            <span>Essence</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="carburant" value="Hybride" {{ $carb === 'Hybride' ? 'checked' : '' }}>
                            <span>Hybride</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="carburant" value="Électrique" {{ $carb === 'Électrique' ? 'checked' : '' }}>
                            <span>Électrique</span>
                        </label>
                    </div>
                </div>

                {{-- Boîte de vitesses --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Boîte de vitesses</label>
                    @php
                        $gear = request('boite_vitesse');
                    @endphp
                    <select name="boite_vitesse"
                            class="w-full border rounded-lg px-2 py-2 text-xs">
                        <option value="">Peu importe</option>
                        <option value="Manuelle"    {{ $gear === 'Manuelle' ? 'selected' : '' }}>Manuelle</option>
                        <option value="Automatique" {{ $gear === 'Automatique' ? 'selected' : '' }}>Automatique</option>
                    </select>
                </div>

                {{-- Wilaya --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Wilaya</label>
                    <input type="text"
                           name="wilaya"
                           value="{{ request('wilaya') }}"
                           placeholder="ex : Alger"
                           class="w-full border rounded-lg px-2 py-2 text-xs">
                </div>

                {{-- Max price --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Prix max</label>
                    <input type="number"
                           name="price_max"
                           value="{{ request('price_max') }}"
                           placeholder="ex : 3000000"
                           class="w-full border rounded-lg px-2 py-2 text-xs">
                </div>

                {{-- Free text --}}
                <div>
                    <label class="text-xs font-semibold block mb-1">Recherche texte</label>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Mots-clés (ex : BMW, clim, etc.)"
                           class="w-full border rounded-lg px-2 py-2 text-xs">
                </div>

                {{-- Filter actions --}}
                <div class="pt-2 flex flex-col gap-2">
                    <button type="submit"
                            class="w-full py-2 rounded-full bg-pink-600 text-white text-xs font-semibold hover:bg-pink-700">
                        Appliquer les filtres
                    </button>
                    <a href="{{ route('annonces.search') }}"
                       class="w-full text-center text-xs text-gray-500 hover:underline">
                        Réinitialiser tous les filtres
                    </a>
                </div>
            </form>
        </aside>

        {{-- CENTER COLUMN – Results list --}}
        <main class="space-y-3">
            @if ($annonces->count())
                @foreach ($annonces as $annonce)
                    {{-- One result card --}}
                    <a href="{{ route('annonces.show', $annonce->id) }}"
                       class="bg-white rounded-2xl shadow flex flex-col md:flex-row overflow-hidden hover:shadow-md transition">

                        {{-- Image --}}
                        <img
                            src="{{ $annonce->image_path ? asset('storage/'.$annonce->image_path) : asset('images/placeholder-car.jpg') }}"
                            alt="Photo voiture"
                            class="w-full md:w-64 h-44 object-cover"
                        />

                        {{-- Content --}}
                        <div class="flex-1 p-4 flex flex-col justify-between gap-2">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2">
                                <div>
                                    {{-- Title --}}
                                    <h2 class="text-base md:text-lg font-semibold">
                                        {{ $annonce->titre }}
                                    </h2>
                                    @if(($annonce->views ?? 0) >= 50)
                                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[11px] bg-orange-50 text-orange-700 border border-orange-200">
                                    🔥 Annonce populaire
                                             </span>
                                    @endif
                                    {{-- Brand / model --}}
                                    <p class="text-xs text-gray-500">
                                        {{ $annonce->marque }}
                                        @if($annonce->modele)
                                            • {{ $annonce->modele }}
                                        @endif
                                    </p>

                                    {{-- Specs --}}
                                    <p class="text-xs text-gray-400 mt-1">
                                        @if($annonce->annee)
                                            {{ $annonce->annee }} •
                                        @endif

                                        @if($annonce->kilometrage)
                                            {{ number_format($annonce->kilometrage, 0, ',', ' ') }} km •
                                        @endif

                                        @if($annonce->carburant)
                                            {{ $annonce->carburant }} •
                                        @endif

                                        @if($annonce->boite_vitesse)
                                            {{ $annonce->boite_vitesse }}
                                        @endif
                                        {{ $annonce->views ?? 0 }} vue(s)
                                        @if(($annonce->views ?? 0) >= 50)
        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                     bg-orange-50 text-orange-700 border border-orange-200">
            🔥 Populaire
        </span>
    @endif
                                    </p>

                                    {{-- Short description --}}
                                    <p class="mt-2 text-xs md:text-sm text-gray-600 line-clamp-2">
                                        {{ \Illuminate\Support\Str::limit($annonce->description, 150) }}
                                    </p>
                                </div>

                                {{-- Price & city --}}
                                <div class="text-right md:min-w-[120px]">
                                    <p class="text-lg md:text-xl font-bold text-pink-600">
                                        {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $annonce->ville ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Actions row (favorites) --}}
                            @php
                                $isFavorite = auth()->check()
                                    ? $annonce->favorites->contains('user_id', auth()->id())
                                    : false;
                            @endphp

                            <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                @auth
                                    {{-- Bouton favoris qui ne casse pas le lien de la carte --}}
                                    <button
                                        type="button"
                                        onclick="event.preventDefault(); event.stopPropagation(); document.getElementById('fav-form-{{ $annonce->id }}').submit();"
                                        class="flex items-center gap-1 hover:text-pink-600"
                                    >
                                        @if($isFavorite)
                                            <span>♥</span>
                                            <span>Retirer des favoris</span>
                                        @else
                                            <span>♡</span>
                                            <span>Ajouter aux favoris</span>
                                        @endif
                                    </button>

                                    {{-- Formulaire POST caché --}}
                                    <form id="fav-form-{{ $annonce->id }}"
                                          action="{{ route('favorites.toggle', $annonce) }}"
                                          method="POST"
                                          class="hidden">
                                        @csrf
                                    </form>
                                @else
                                    {{-- Non connecté : lien vers login --}}
                                    <a href="{{ route('login') }}"
                                       onclick="event.stopPropagation();"
                                       class="flex items-center gap-1 hover:text-pink-600">
                                        <span>♡</span>
                                        <span>Ajouter aux favoris</span>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </a>
                @endforeach

                {{-- Pagination --}}
                <div class="pt-4">
                    {{ $annonces->links() }}
                </div>
            @else
                <p class="text-sm text-gray-500">
                    Aucune annonce ne correspond à ces critères.
                </p>
            @endif
        </main>

        {{-- RIGHT COLUMN – Side panel (ads / info placeholders) --}}
        <aside class="hidden lg:block space-y-4">
            <div class="bg-white rounded-2xl shadow p-4">
                <p class="text-xs uppercase font-semibold text-gray-400 mb-1">Publicité</p>
                <div class="bg-gradient-to-br from-pink-600 to-purple-600 rounded-xl p-4 text-white text-sm">
                    <p class="font-semibold mb-1">autoDZ Financement</p>
                    <p class="text-xs mb-3">Simulez votre crédit auto et trouvez la mensualité qui vous convient.</p>
                    <button class="px-3 py-1 rounded-full bg-white text-pink-600 text-xs font-semibold">
                        Faire une simulation
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-4 text-xs text-gray-600 space-y-2">
                <p class="font-semibold text-gray-800">Conseils autoDZ</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Vérifiez le carnet d'entretien.</li>
                    <li>Essayez le véhicule sur route.</li>
                    <li>Comparez les prix sur plusieurs annonces.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
