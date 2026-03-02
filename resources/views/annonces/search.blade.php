@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
    
    // Build SEO title and description based on filters
    $seoTitleParts = [];
    $seoDescParts = [];
    
    if (request('marque')) {
        $seoTitleParts[] = request('marque');
    }
    if (request('modele')) {
        $seoTitleParts[] = request('modele');
    }
    if (request('vehicle_type') === 'Moto') {
        $seoTitleParts[] = 'Motos';
    } elseif (request('vehicle_type') === 'Voiture') {
        $seoTitleParts[] = 'Voitures';
    }
    if (request('wilaya')) {
        $seoTitleParts[] = request('wilaya');
    }
    
    $seoTitle = count($seoTitleParts) > 0 
        ? implode(' ', $seoTitleParts) . ' d\'occasion - Annonces | ElSayara'
        : 'Annonces Véhicules d\'Occasion en Algérie | ElSayara';
    
    $seoDesc = 'Recherchez parmi ' . (isset($annonces) ? number_format($annonces->total()) : 'des milliers') . ' annonces de véhicules d\'occasion';
    if (request('marque')) {
        $seoDesc .= ' ' . request('marque');
    }
    $seoDesc .= ' en Algérie. Filtrez par prix, année, kilométrage et wilaya.';
@endphp

@section('seo_title', $seoTitle)
@section('seo_description', $seoDesc)

@section('content')
<!-- Force cache refresh v2 -->
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8">
    @php
        $currentType = request('vehicle_type');
        $title = "Véhicules d'occasion";
        if ($currentType === 'Voiture') {
            $title = "Voitures d'occasion";
        } elseif ($currentType === 'Moto') {
            $title = "Motos d'occasion";
        }
    @endphp

    {{-- Title + total results --}}
    <div class="flex flex-row items-end justify-between gap-2 mb-6">
        <div>
            <h1 class="text-3xl font-bold">
                {{ $title }}
                @if($annonces->total())
                    <span class="text-pink-600 font-extrabold">
                        – {{ number_format($annonces->total(), 0, ',', ' ') }} annonces
                    </span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Affinez votre recherche avec les filtres à gauche.
            </p>
        </div>

        {{-- Sort selector (connecté au contrôleur) --}}
        <div class="mt-2">
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
                        class="border rounded-lg px-3 py-2 text-sm"
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
    <div class="grid gap-6 lg:grid-cols-[260px,minmax(0,1fr),260px]">
        {{-- LEFT COLUMN – Filters --}}
        <div x-data="{ filtersOpen: false }">
            {{-- Mobile toggle button --}}
            <button @click="filtersOpen = !filtersOpen"
                    class="lg:hidden w-full mb-3 flex items-center justify-center gap-2 bg-white rounded-xl shadow px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span x-text="filtersOpen ? 'Masquer les filtres' : 'Afficher les filtres'">Afficher les filtres</span>
                <svg class="w-4 h-4 transition" :class="{ 'rotate-180': filtersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <aside :class="filtersOpen ? '' : 'hidden lg:block'"
                   class="bg-white rounded-2xl shadow p-4 space-y-4">
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
                            $types = ['Voiture' => 'Voiture', 'Moto' => 'Moto'];
                        @endphp
                        @foreach($types as $value => $label)
                            <button type="submit"
                                    name="vehicle_type"
                                    value="{{ $value }}"
                                    class="px-3 py-1 rounded-full border 
                                        {{ $currentType === $value ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-200' }}">
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
                           placeholder="Marque, modèle, ..."
                           class="w-full border rounded-lg px-2 py-2 text-xs">
                </div>

                {{-- Filter actions --}}
                <div class="pt-2 flex flex-col gap-2">
                    <button type="submit"
                            class="w-full py-2 rounded-full bg-gray-800 text-white text-xs font-semibold hover:bg-gray-900">
                        Appliquer les filtres
                    </button>
                    <a href="{{ route('annonces.search') }}"
                       class="w-full text-center text-xs text-gray-500 hover:underline">
                        Réinitialiser tous les filtres
                    </a>
                </div>
            </form>
        </aside>
        </div>

        {{-- CENTER COLUMN – Results list --}}
        <main class="space-y-3">
            @if ($annonces->count())
                @foreach ($annonces as $annonce)
                    @php
                            $disk = config('filesystems.default', 'public');
                        if ($annonce->image_path) {
                            $path = ltrim($annonce->image_path, '/');
                            $path = preg_replace('#^storage/#', '', $path);
                            
                            if ($disk !== 'public' && $disk !== 'local') {
                                $mainImage = Storage::disk($disk)->url($path);
                            } else {
                                $mainImage = asset('storage/' . $path);
                            }
                        } elseif ($annonce->image_url) {
                            $mainImage = $annonce->image_url;
                        } else {
                            $mainImage = asset('images/placeholder-car.jpg');
                        }
                    @endphp
                    {{-- One result card --}}
                    <a href="{{ route('annonces.show', ['annonce' => $annonce->id, 'slug' => $annonce->slug ?: Str::slug($annonce->titre)]) }}"
                       class="bg-white rounded-2xl shadow flex flex-row overflow-hidden hover:shadow-md transition">

                        {{-- Image - Responsive: petit sur mobile, plus grand sur desktop --}}
                        <img
                            src="{{ $mainImage }}"
                            alt="Photo voiture"
                            class="w-32 h-32 sm:w-40 sm:h-36 lg:w-52 lg:h-40 object-cover shrink-0"
                            onerror="this.src='{{ asset('images/placeholder-car.jpg') }}'"
                        />

                        {{-- Content --}}
                        <div class="flex-1 p-3 sm:p-4 flex flex-col justify-between gap-1 sm:gap-2">
                            <div class="flex flex-row justify-between items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    {{-- Title - Responsive text size --}}
                                    <h2 class="text-sm sm:text-base font-semibold line-clamp-2">
                                        {{ $annonce->titre }}
                                    </h2>
                                    <div class="flex gap-2 mt-1 flex-wrap">
                                        @php
                                            $isBoosted = \App\Models\Boost::where('annonce_id', $annonce->id)
                                                ->where('status', 'active')
                                                ->where('expires_at', '>', now())
                                                ->exists();
                                        @endphp
                                        @if($isBoosted)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] bg-pink-50 text-pink-700 border border-pink-200">
                                                ⭐ À la une
                                            </span>
                                        @endif
                                        @if(($annonce->views ?? 0) >= 50)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] bg-orange-50 text-orange-700 border border-orange-200">
                                                🔥 Populaire
                                            </span>
                                        @endif
                                    </div>
                                    {{-- Brand / model --}}
                                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">
                                        {{ $annonce->marque }}
                                        @if($annonce->modele)
                                            • {{ $annonce->modele }}
                                        @endif
                                    </p>

                                    {{-- Specs - Hide on very small screens, show abbreviated on mobile --}}
                                    <p class="hidden sm:block text-xs text-gray-400 mt-1">
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
                                    </p>

                                    {{-- Short description - Hide on mobile --}}
                                    <p class="hidden md:block mt-2 text-xs text-gray-600 line-clamp-2">
                                        {{ \Illuminate\Support\Str::limit($annonce->description, 150) }}
                                    </p>
                                </div>

                                {{-- Price & city - More compact on mobile --}}
                                <div class="text-right shrink-0">
                                    <p class="text-base sm:text-lg font-bold text-pink-600">
                                        {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                                    </p>
                                    <p class="text-[10px] sm:text-xs text-gray-500 mt-1">
                                        {{ $annonce->ville ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Actions row (favorites) - Always show button for consistent layout --}}
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
                                        class="flex items-center gap-1 hover:text-gray-800"
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
                                    {{-- Non connecté : afficher le même bouton pour cohérence du design --}}
                                    <button
                                        type="button"
                                        onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('login') }}';"
                                        class="flex items-center gap-1 hover:text-gray-800"
                                    >
                                        <span>♡</span>
                                        <span>Ajouter aux favoris</span>
                                    </button>
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
        <aside class="space-y-4">
            <div class="bg-white rounded-2xl shadow p-4">
                <p class="text-xs uppercase font-semibold text-gray-400 mb-1">Publicité</p>
                <div class="bg-gradient-to-br from-gray-800 to-purple-600 rounded-xl p-4 text-white text-sm">
                    <p class="font-semibold mb-1">ElSayara Financement</p>
                    <p class="text-xs mb-3">Simulez votre crédit auto et trouvez la mensualité qui vous convient.</p>
                    <button class="px-3 py-1 rounded-full bg-white text-gray-800 text-xs font-semibold">
                        Faire une simulation
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-4 text-xs text-gray-600 space-y-2">
                <p class="font-semibold text-gray-800">Conseils ElSayara</p>
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
