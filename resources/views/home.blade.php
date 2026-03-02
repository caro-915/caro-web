@extends('layouts.app')

@section('seo_title', 'ElSayara – Achat et Vente de Véhicules d\'Occasion en Algérie')
@section('seo_description', 'Trouvez votre prochaine voiture, moto ou utilitaire sur ElSayara. Des milliers d\'annonces vérifiées partout en Algérie. Recherche facile par marque, prix et wilaya.')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')



    {{-- HERO : Search form + marketing block --}}
    <section class="mb-10">
        <div class="bg-gradient-to-r from-gray-100 to-white rounded-3xl px-6 py-8 md:px-10 md:py-12 flex flex-col md:flex-row gap-8 md:items-center">
            
            {{-- LEFT : Search card --}}
            <div class="w-full md:max-w-md">
                                <form method="GET" action="{{ route('annonces.search') }}" class="bg-white rounded-3xl shadow-lg p-5 md:p-6 space-y-4">
                                    {{-- Hidden field actually used by backend - default to empty (no filter) --}}
                                    <input type="hidden" name="vehicle_type" id="vehicle_type_input" value="{{ request('vehicle_type', '') }}">

                                    {{-- Vehicle type first, then text search --}}
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Type de véhicule</label>
                                            <div class="flex items-center gap-2 text-xs md:text-sm">
                                                {{-- Buttons act as UI helpers that update the hidden field --}}
                                                <button type="button"
                                                        data-type="Voiture"
                                                        class="vehicle-type-btn flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                                                            {{ request('vehicle_type') === 'Voiture' ? 'bg-gray-500 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200' }}">
                                                    🚗 Voiture
                                                </button>
                                                <button type="button"
                                                        data-type="Moto"
                                                        class="vehicle-type-btn flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                                                            {{ request('vehicle_type') === 'Moto' ? 'bg-gray-500 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200' }}">
                                                    🏍 Moto
                                                </button>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold mb-1">Recherche texte</label>
                                            <input type="text"
                                                   name="home_q"
                                                   value="{{ request('home_q') }}"
                                                   placeholder="Marque, modèle, mot-clé..."
                                                   class="w-full border rounded-lg p-2 text-xs md:text-sm">
                                        </div>

                                        <div class="flex items-center justify-center">
                                            <span class="px-2 py-1 text-[10px] uppercase tracking-wide text-gray-500 bg-gray-100 rounded-full">ou</span>
                                        </div>
                                    </div>

                    {{-- Brand / Model --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Marque</label>

                            <div id="home_brand_dropdown" x-data="brandDropdownHome()" class="relative">
                                <button type="button" @click="open = !open"
                                        class="w-full border rounded-lg p-2 text-xs md:text-sm text-left bg-white flex justify-between items-center">
                                    <span x-text="selected || 'Peu importe'"></span>
                                    <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                </button>

                                <input type="hidden" name="marque" id="home_marque_hidden" :value="selected">

                                {{-- Dropdown menu --}}
                                <div x-show="open" @click.away="open = false"
                                     class="absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50 overflow-hidden flex flex-col"
                                     style="max-height: 280px;">
                                    {{-- Barre de recherche --}}
                                    <div class="sticky top-0 p-2 border-b bg-white">
                                        <input type="text" x-model="search" placeholder="Rechercher une marque..."
                                               class="w-full border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-pink-500">
                                    </div>

                                    {{-- Liste des marques (scroll après 8 items) --}}
                                    <div class="overflow-y-auto flex-1" style="max-height: 240px;">
                                        <button type="button"
                                                @click="selectBrand('')"
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b">
                                            <span>Peu importe</span>
                                        </button>
                                        <template x-for="brand in filteredBrands()" :key="brand">
                                            <button type="button"
                                                    @click="selectBrand(brand)"
                                                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b last:border-b-0">
                                                <span x-text="brand"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1">Modèle</label>
                            <div id="home_model_dropdown" x-data="modelDropdownHome()" class="relative">
                                <button type="button" 
                                        @click="open = !open"
                                        class="w-full border rounded-lg p-2 text-xs md:text-sm text-left bg-white flex justify-between items-center">
                                    <span x-text="selected || 'Peu importe'"></span>
                                    <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                </button>

                                <input type="hidden" name="modele" id="home_modele_hidden" :value="selected">

                                {{-- Dropdown menu --}}
                                <div x-show="open" @click.away="open = false"
                                     class="absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50 overflow-hidden flex flex-col"
                                     style="max-height: 280px;">
                                    {{-- Barre de recherche --}}
                                    <div class="sticky top-0 p-2 border-b bg-white">
                                        <input type="text" x-model="search" placeholder="Rechercher un modèle..."
                                               class="w-full border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-pink-500">
                                    </div>

                                    {{-- Liste des modèles --}}
                                    <div class="overflow-y-auto flex-1" style="max-height: 240px;">
                                        <button type="button"
                                                @click="selectModel('')"
                                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b">
                                            <span>Peu importe</span>
                                        </button>
                                        <template x-for="model in filteredModels()" :key="model">
                                            <button type="button"
                                                    @click="selectModel(model)"
                                                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b last:border-b-0">
                                                <span x-text="model"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Price max / Energy --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Prix max</label>
                            <input type="number" name="price_max" value="{{ request('price_max') }}"
                                   class="w-full border rounded-lg p-2 text-xs md:text-sm" placeholder="Pas de limite">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Carburant</label>
                            <select name="carburant"
                                    class="w-full border rounded-lg p-2 text-xs md:text-sm">
                                <option value="any">Peu importe</option>
                                <option value="Essence"  {{ request('carburant') === 'Essence' ? 'selected' : '' }}>Essence</option>
                                <option value="Diesel"   {{ request('carburant') === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                <option value="Hybride"  {{ request('carburant') === 'Hybride' ? 'selected' : '' }}>Hybride</option>
                                <option value="Électrique" {{ request('carburant') === 'Électrique' ? 'selected' : '' }}>Électrique</option>
                            </select>
                        </div>
                    </div>

                    {{-- Wilaya --}}
                    <div>
                        <label class="block text-xs font-semibold mb-1">Wilaya / Code postal</label>
                        <input type="text" name="wilaya" value="{{ request('wilaya') }}"
                               class="w-full border rounded-lg p-2 text-xs md:text-sm" placeholder="ex : Alger, 16000">
                    </div>

                    {{-- CTA buttons --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-3 rounded-full bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900">
                            Rechercher
                        </button>
                    </div>
                </form>
            </div>

            {{-- RIGHT : Marketing block "vendre son véhicule" --}}
            <div class="flex-1">
                <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-4">
                    Trouvez <span class="text-pink-600">la voiture d'occasion</span><br>
                    qui vous correspond
                </h1>
                <p class="text-gray-600 text-sm md:text-base mb-6 max-w-xl">
                    Recherchez parmi des milliers d'annonces vérifiées partout en Algérie. 
                    Filtrez par marque, budget, wilaya et trouvez votre prochaine voiture en quelques clics.
                </p>

                {{-- Marketing block for selling a vehicle --}}
                <div class="space-y-3">
                    {{-- Sell vehicle card --}}
                    <div class="bg-white bg-opacity-80 border border-pink-100 rounded-2xl p-4 md:p-5 inline-flex flex-col md:flex-row md:items-center gap-4 w-full">
                        <div class="flex-1">
                            <h2 class="text-sm md:text-base font-semibold mb-1">Vendre votre véhicule ?</h2>
                            <p class="text-xs md:text-sm text-gray-600">
                                Déposez votre annonce gratuitement et touchez des milliers d'acheteurs potentiels en quelques minutes.
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('annonces.create') }}"
                               class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-gray-800 text-white text-xs md:text-sm font-semibold hover:bg-gray-900">
                                Déposer une annonce
                            </a>
                        </div>
                    </div>

                    {{-- PRO upgrade card - TEMPORARILY HIDDEN (change false to true to re-enable) --}}
                    @if(false && auth()->check() && !auth()->user()->hasProPlan())
                        <div class="bg-gradient-to-r from-pink-50 to-pink-100 border border-pink-300 rounded-2xl p-4 md:p-5 inline-flex flex-col md:flex-row md:items-center gap-4 w-full">
                            <div class="flex-1">
                                @if(auth()->user()->isPro() && !auth()->user()->hasProPlan())
                                    {{-- User has Premium subscription --}}
                                    <h2 class="text-sm md:text-base font-semibold mb-1">🚀 Passez au plan PRO</h2>
                                    <p class="text-xs md:text-sm text-gray-700">
                                        Vous êtes en <strong>Premium</strong>. Passez au PRO pour débloquer jusqu'à 10 annonces, 5 boosts/mois et 30 jours d'accès.
                                    </p>
                                @else
                                    {{-- Free user --}}
                                    <h2 class="text-sm md:text-base font-semibold mb-1">✨ Passer en PRO</h2>
                                    <p class="text-xs md:text-sm text-gray-700">
                                        Débloquez plus d'annonces, boostez vos annonces et bénéficiez de fonctionnalités exclusives.
                                    </p>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('pro.index') }}"
                                   class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-600 text-white text-xs md:text-sm font-semibold hover:bg-pink-700">
                                    💳 Passer au PRO
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>



    {{-- SECTION : Dernières annonces --}}
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg md:text-xl font-semibold">Dernières annonces</h2>
            <a href="{{ route('annonces.search') }}" class="text-xs md:text-sm text-gray-600 hover:text-gray-800">
                Voir tout →
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @forelse ($latestAds as $ad)
                @php
                    $disk = config('filesystems.default', 'public');
                    $mainImage = null;
                    
                    if ($ad->image_path) {
                        $path = ltrim($ad->image_path, '/');
                        $path = preg_replace('#^storage/#', '', $path);
                        
                        if ($disk !== 'public' && $disk !== 'local') {
                            $mainImage = Storage::disk($disk)->url($path);
                        } else {
                            $mainImage = asset('storage/' . $path);
                        }
                    } elseif ($ad->image_url) {
                        $mainImage = $ad->image_url;
                    } else {
                        $mainImage = asset('images/placeholder-car.jpg');
                    }
                @endphp
                <a href="{{ route('annonces.show', ['annonce' => $ad->id, 'slug' => $ad->slug ?: Str::slug($ad->titre)]) }}"
                   class="bg-white rounded-xl shadow-sm hover:shadow transition overflow-hidden group">
                    <div class="relative h-24 overflow-hidden bg-gray-100">
                        <img src="{{ $mainImage }}" 
                             alt="{{ $ad->titre }}"
                             class="w-full h-full object-cover"
                             onerror="this.src='{{ asset('images/placeholder-car.jpg') }}'">
                    </div>
                    <div class="p-2">
                        <p class="text-xs font-semibold truncate">{{ $ad->titre }}</p>
                        <p class="text-xs text-pink-600 font-bold">{{ number_format($ad->prix, 0, ',', ' ') }} DA</p>
                    </div>
                </a>
            @empty
                <p class="text-xs text-gray-500 col-span-full text-center py-4">Aucune annonce disponible.</p>
            @endforelse
        </div>
    </section>

    {{-- SECTION : Top annonces (plus de vues) --}}
    <section id="top-annonces" class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg md:text-xl font-semibold">🔥 Top annonces</h2>
            <span class="text-xs md:text-sm text-gray-500">Les plus consultées</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse ($topAnnonces as $ad)
                @php
                    $disk = config('filesystems.default', 'public');
                    $mainImage = null;
                    
                    if ($ad->image_path) {
                        $path = ltrim($ad->image_path, '/');
                        $path = preg_replace('#^storage/#', '', $path);
                        
                        if ($disk !== 'public' && $disk !== 'local') {
                            $mainImage = Storage::disk($disk)->url($path);
                        } else {
                            $mainImage = asset('storage/' . $path);
                        }
                    } elseif ($ad->image_url) {
                        $mainImage = $ad->image_url;
                    } else {
                        $mainImage = asset('images/placeholder-car.jpg');
                    }
                @endphp
                <a href="{{ route('annonces.show', ['annonce' => $ad->id, 'slug' => $ad->slug ?: Str::slug($ad->titre)]) }}"
                   class="bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden">
                    <div class="relative h-40 overflow-hidden bg-gray-100">
                        <img src="{{ $mainImage }}" 
                             alt="{{ $ad->titre }}"
                             class="w-full h-full object-cover"
                             onerror="this.src='{{ asset('images/placeholder-car.jpg') }}'">
                        <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded-full text-xs font-semibold text-gray-700">
                            👁️ {{ $ad->views ?? 0 }} vues
                        </div>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-500 mb-1">{{ $ad->marque }} @if($ad->modele)• {{ $ad->modele }}@endif</p>
                        <p class="text-sm font-semibold truncate mb-2">{{ $ad->titre }}</p>
                        <p class="text-base text-pink-600 font-bold">{{ number_format($ad->prix, 0, ',', ' ') }} DA</p>
                    </div>
                </a>
            @empty
                <p class="text-xs text-gray-500 col-span-full text-center py-4">Aucune annonce populaire pour le moment.</p>
            @endforelse
        </div>
    </section>

    {{-- SECTION : Marques populaires --}}
    <section class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Marques populaires</h2>
        <div class="bg-white rounded-2xl shadow px-4 py-4 flex flex-wrap gap-3">
            @forelse ($popularMarques as $marque)
                <a href="{{ route('home', ['marque' => $marque->name]) }}"
                   class="px-3 py-1 rounded-full border text-xs md:text-sm text-gray-700 hover:border-gray-800 hover:text-gray-800">
                    {{ $marque->name }} ({{ $marque->annonces_count }})
                </a>
            @empty
                <p class="text-xs text-gray-500">Aucune marque populaire pour le moment.</p>
            @endforelse
        </div>
    </section>

    {{-- SECTION : Modèles populaires --}}
    <section class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Modèles populaires</h2>
        <div class="bg-white rounded-2xl shadow px-4 py-4 flex flex-wrap gap-3">
            @forelse ($popularModeles as $modele)
                <a href="{{ route('home', ['modele' => $modele->name]) }}"
                   class="px-3 py-1 rounded-full border text-xs md:text-sm text-gray-700 hover:border-gray-800 hover:text-gray-800">
                    {{ $modele->name }} ({{ $modele->annonces_count }})
                </a>
            @empty
                <p class="text-xs text-gray-500">Aucun modèle populaire pour le moment.</p>
            @endforelse
        </div>
    </section>

    {{-- SECTION : Catalogue marques & modèles --}}
    @if(!empty($brandModelMap))
        <section class="mb-12">
            <h2 class="text-xl font-semibold mb-2">Catalogue marques & modèles</h2>
            <p class="text-xs md:text-sm text-gray-500 mb-4">Choisissez une marque pour voir tous les modèles associés déjà intégrés sur ElSayara.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($brandModelMap as $entry)
                    <details class="bg-white rounded-2xl shadow border border-gray-100 p-4" @if(request('marque') === $entry['brand']) open @endif>
                        <summary class="cursor-pointer text-sm font-semibold text-gray-800 flex items-center justify-between">
                            <span>{{ $entry['brand'] }}</span>
                            <span class="text-xs text-gray-400">{{ count($entry['models']) }} modèles</span>
                        </summary>
                        <div class="mt-3 flex flex-wrap gap-2 max-h-44 overflow-y-auto pr-1">
                            @foreach($entry['models'] as $model)
                                <a href="{{ route('annonces.search', ['marque' => $entry['brand'], 'modele' => $model]) }}"
                                   class="px-2 py-1 rounded-full bg-gray-50 border border-gray-200 text-[11px] text-gray-700 hover:border-gray-800 hover:text-gray-900">
                                    {{ $model }}
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif


    {{-- SECTION : À propos --}}
    <section id="about" class="mt-16 bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow p-6 md:p-8">
        <div class="max-w-3xl">
            <h2 class="text-2xl font-bold mb-3">À propos de ElSayara</h2>
            <p class="text-sm md:text-base text-gray-600 leading-relaxed mb-6">
                ElSayara est la plateforme algérienne de référence pour l'achat et la vente de véhicules d'occasion.
                Notre mission : connecter acheteurs et vendeurs dans une expérience simple, transparente et sécurisée.
                Des milliers d'annonces vérifiées, un moteur de recherche puissant, et une communauté active partout en Algérie.
            </p>

            {{-- CTAs utiles --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('annonces.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 text-white text-sm font-semibold rounded-full hover:bg-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Déposer une annonce
                </a>
                <a href="{{ route('annonces.search') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Rechercher un véhicule
                </a>
                <a href="#top-annonces"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-pink-50 border border-pink-200 text-pink-700 text-sm font-semibold rounded-full hover:bg-pink-100 transition">
                    🔥 Voir les top annonces
                </a>
            </div>
        </div>
    </section>

    {{-- SECTION : Contact complet --}}
    <section id="contact-us" class="mt-10 bg-white rounded-2xl shadow p-6 md:p-8">
        <h2 class="text-2xl font-bold mb-4">Nous contacter</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Coordonnées --}}
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">📧 Email</h3>
                    <a href="{{ route('contact.show') }}" 
                       class="text-pink-600 hover:underline text-sm md:text-base">
                        {{ config('autodz.contact_email', 'contact@elsayara.com') }}
                    </a>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">📍 Localisation</h3>
                    <p class="text-sm md:text-base text-gray-600">Algérie</p>
                </div>

                @if(config('autodz.contact_phone'))
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">📞 Téléphone</h3>
                    <a href="tel:{{ config('autodz.contact_phone') }}" 
                       class="text-pink-600 hover:underline text-sm md:text-base">
                        {{ config('autodz.contact_phone') }}
                    </a>
                </div>
                @endif

                {{-- Réseaux sociaux --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">🌐 Réseaux sociaux</h3>
                    <div class="flex gap-3">
                        @if(config('autodz.social_facebook'))
                        <a href="{{ config('autodz.social_facebook') }}" target="_blank" rel="noopener"
                           class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                           title="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.77,7.46H14.5v-1.9c0-.9.6-1.1,1-1.1h3V.5h-4.33C10.24.5,9.5,3.44,9.5,5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z"/></svg>
                        </a>
                        @endif
                        @if(config('autodz.social_instagram'))
                        <a href="{{ config('autodz.social_instagram') }}" target="_blank" rel="noopener"
                           class="w-9 h-9 flex items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-pink-500 text-white hover:opacity-90 transition"
                           title="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12,2.16c3.2,0,3.58,0,4.85.07,3.25.15,4.77,1.69,4.92,4.92.06,1.27.07,1.65.07,4.85s0,3.58-.07,4.85c-.15,3.23-1.66,4.77-4.92,4.92-1.27.06-1.65.07-4.85.07s-3.58,0-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.65-.07-4.85s0-3.58.07-4.85C2.38,3.92,3.9,2.38,7.15,2.23,8.42,2.18,8.8,2.16,12,2.16ZM12,0C8.74,0,8.33,0,7.05.07c-4.35.2-6.78,2.62-7,7C0,8.33,0,8.74,0,12s0,3.67.07,4.95c.2,4.36,2.62,6.78,7,7C8.33,24,8.74,24,12,24s3.67,0,4.95-.07c4.35-.2,6.78-2.62,7-7C24,15.67,24,15.26,24,12s0-3.67-.07-4.95c-.2-4.35-2.62-6.78-7-7C15.67,0,15.26,0,12,0Zm0,5.84A6.16,6.16,0,1,0,18.16,12,6.16,6.16,0,0,0,12,5.84ZM12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16ZM18.41,4.15a1.44,1.44,0,1,0,1.44,1.44A1.44,1.44,0,0,0,18.41,4.15Z"/></svg>
                        </a>
                        @endif
                        @if(config('autodz.social_twitter'))
                        <a href="{{ config('autodz.social_twitter') }}" target="_blank" rel="noopener"
                           class="w-9 h-9 flex items-center justify-center rounded-full bg-black text-white hover:bg-gray-800 transition"
                           title="X (Twitter)">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        @endif
                        @if(config('autodz.social_tiktok'))
                        <a href="{{ config('autodz.social_tiktok') }}" target="_blank" rel="noopener"
                           class="w-9 h-9 flex items-center justify-center rounded-full bg-black text-white hover:bg-gray-800 transition"
                           title="TikTok">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
                        </a>
                        @endif
                    </div>
                    @if(!config('autodz.social_facebook') && !config('autodz.social_instagram') && !config('autodz.social_twitter') && !config('autodz.social_tiktok'))
                    <p class="text-xs text-gray-400">Bientôt disponible</p>
                    @endif
                </div>

                {{-- Notice support --}}
                <div class="bg-gray-50 rounded-lg p-3 mt-4">
                    <p class="text-xs text-gray-500">
                        💬 <strong>Support :</strong> Nous répondons généralement sous 24-48h ouvrées.
                    </p>
                </div>
            </div>

            {{-- Newsletter placeholder --}}
            <div class="bg-gradient-to-br from-pink-50 to-gray-50 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">📬 Newsletter</h3>
                <p class="text-xs text-gray-600 mb-4">
                    Recevez les meilleures offres et nouveautés directement dans votre boîte mail.
                </p>
                {{-- TODO: Implémenter le backend newsletter (NewsletterController + table subscribers) --}}
                <form action="#" method="POST" class="space-y-3" onsubmit="event.preventDefault(); alert('Newsletter bientôt disponible !');">
                    @csrf
                    <input type="email" name="newsletter_email" placeholder="Votre adresse email"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                    <button type="submit"
                            class="w-full px-4 py-2 bg-pink-600 text-white text-sm font-semibold rounded-lg hover:bg-pink-700 transition">
                        S'abonner
                    </button>
                </form>
                <p class="text-[10px] text-gray-400 mt-2">Pas de spam, promis. Désinscription possible à tout moment.</p>
            </div>
        </div>
    </section>




    {{-- JS: handle vehicle type buttons + dynamic brand/model switching --}}
    <script>
        // Data maps per vehicle type (from DB)
        const carBrandsMapData = @json($carBrandsMap);
        const motoBrandsMapData = @json($motoBrandsMap);
        const carBrandsList = @json($marques);
        const motoBrandsList = @json($marquesMotos);
        const brandModelsData = {...carBrandsMapData, ...motoBrandsMapData};

        // Handle vehicle type button selection
        const typeInput = document.getElementById('vehicle_type_input');
        const typeButtons = document.querySelectorAll('.vehicle-type-btn');
        const homeBrandDropdown = document.getElementById('home_brand_dropdown');
        const homeMarqueHidden = document.getElementById('home_marque_hidden');
        const homeModelDropdown = document.getElementById('home_model_dropdown');

        function setActiveHomeTypeButton(type) {
            typeButtons.forEach(btn => {
                const isActive = btn.getAttribute('data-type') === type;
                btn.classList.toggle('bg-gray-500', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('border-gray-800', isActive);
                btn.classList.toggle('bg-white', !isActive);
                btn.classList.toggle('text-gray-600', !isActive);
                btn.classList.toggle('border-gray-200', !isActive);
            });
        }

        function applyHomeVehicleTypeUI(type) {
            const isMoto = type === 'Moto';
            const newBrands = isMoto ? motoBrandsList : carBrandsList;

            // Swap brand list in dropdown (always visible, just different data)
            if (homeBrandDropdown) {
                const brandDropdownData = Alpine.$data(homeBrandDropdown);
                if (brandDropdownData) {
                    brandDropdownData.brands = newBrands;
                    brandDropdownData.selected = '';
                    brandDropdownData.search = '';
                }
            }

            // Reset model dropdown
            if (homeModelDropdown) {
                const modelDropdownData = Alpine.$data(homeModelDropdown);
                if (modelDropdownData) {
                    modelDropdownData.selected = '';
                    modelDropdownData.availableModels = [];
                }
            }

            if (homeMarqueHidden) {
                homeMarqueHidden.value = '';
            }
        }

        if (typeInput && typeInput.value) {
            setActiveHomeTypeButton(typeInput.value);
        }

        typeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                typeInput.value = type;

                // Update active state
                setActiveHomeTypeButton(type);
                applyHomeVehicleTypeUI(type);
            });
        });

        // Apply initial state after Alpine is ready
        document.addEventListener('alpine:initialized', () => {
            if (typeInput && typeInput.value) {
                applyHomeVehicleTypeUI(typeInput.value);
            }
        });

        // Alpine.js dropdown for brand (home)
        function brandDropdownHome() {
            return {
                open: false,
                search: '',
                selected: "{{ request('marque') }}",
                brands: carBrandsList,
                filteredBrands() {
                    return this.search
                        ? this.brands.filter(b => b.toLowerCase().includes(this.search.toLowerCase()))
                        : this.brands;
                },
                selectBrand(value) {
                    this.selected = value;
                    this.open = false;
                    
                    // Mettre à jour les modèles disponibles
                    const modelDropdown = Alpine.$data(document.getElementById('home_model_dropdown'));
                    if (modelDropdown) {
                        modelDropdown.updateAvailableModels(value);
                        modelDropdown.selected = '';
                    }
                }
            };
        }

        // Alpine.js dropdown for model (home)
        function modelDropdownHome() {
            return {
                open: false,
                search: '',
                selected: "{{ request('modele') }}",
                availableModels: [],
                init() {
                    const selectedBrand = document.querySelector('input[name="marque"]')?.value || "{{ request('marque') }}";
                    if (selectedBrand) {
                        this.updateAvailableModels(selectedBrand);
                    }
                },
                updateAvailableModels(brand) {
                    if (brand && brandModelsData[brand]) {
                        this.availableModels = brandModelsData[brand];
                    } else {
                        this.availableModels = [];
                    }
                },
                filteredModels() {
                    return this.search
                        ? this.availableModels.filter(m => m.toLowerCase().includes(this.search.toLowerCase()))
                        : this.availableModels;
                },
                selectModel(value) {
                    this.selected = value;
                    this.open = false;
                }
            };
        }
    </script>
@endsection
