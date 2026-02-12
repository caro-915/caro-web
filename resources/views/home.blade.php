@extends('layouts.app')

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
                    {{-- Vehicle type selector --}}
                    <div class="flex items-center gap-2 text-xs md:text-sm">
                        {{-- Hidden field actually used by backend - default to empty (no filter) --}}
                        <input type="hidden" name="vehicle_type" id="vehicle_type_input" value="{{ request('vehicle_type', '') }}">
                        {{-- Text search OR brand/model --}}
                        <div class="space-y-2 w-full">
                            <label class="block text-xs font-semibold mb-1">Recherche texte</label>
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="Marque, modèle, mot-clé..."
                                   class="w-full border rounded-lg p-2 text-xs md:text-sm">
                        </div>

                        <p class="text-center text-[11px] uppercase tracking-wide text-gray-400 w-full">Ou</p>

                        
                        {{-- Buttons are only UI helpers that update the hidden field --}}
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
                                <input type="text" 
                                       name="modele" 
                                       id="home_modele_input"
                                       value="{{ request('modele') }}"
                                       data-placeholder-voiture="Peu importe"
                                       data-placeholder-moto="ex : MT-07, CB500F"
                                       placeholder="Peu importe"
                                       list="home_model_options"
                                       class="w-full border rounded-lg p-2 text-xs md:text-sm">
                                <datalist id="home_model_options"></datalist>
                                   placeholder="ex : Yamaha, Honda"
                                   disabled>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1">Modèle</label>
                            <input type="text"
                                name="modele"
                                id="home_modele_input"
                                value="{{ request('modele') }}"
                                list="home_model_options"
                                autocomplete="off"
                                data-placeholder-voiture="Peu importe"
                                data-placeholder-moto="ex : MT-07, CB500F"
                                placeholder="Peu importe"
                                class="w-full border rounded-lg p-2 text-xs md:text-sm">
                            <datalist id="home_model_options"></datalist>
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

                    {{-- PRO upgrade card (visible only if not PRO) --}}
                    @if(auth()->check() && !auth()->user()->isPro())
                        <div class="bg-gradient-to-r from-pink-50 to-pink-100 border border-pink-300 rounded-2xl p-4 md:p-5 inline-flex flex-col md:flex-row md:items-center gap-4 w-full">
                            <div class="flex-1">
                                <h2 class="text-sm md:text-base font-semibold mb-1">✨ Passer en PRO</h2>
                                <p class="text-xs md:text-sm text-gray-700">
                                    Débloquez 10 annonces, boostez vos annonces et bénéficiez de fonctionnalités premium.
                                </p>
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
                <a href="{{ route('annonces.show', $ad->id) }}"
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
                <a href="{{ route('annonces.show', $ad->id) }}"
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
            <p class="text-xs md:text-sm text-gray-500 mb-4">Choisissez une marque pour voir tous les modèles associés déjà intégrés sur Caro.</p>
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


    <section id="about" class="mt-16 bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="text-2xl font-bold mb-3">À propos de Caro</h2>

    <p class="text-sm md:text-base text-gray-600 leading-relaxed">
        Caro est une plateforme algérienne dédiée à la vente et à l'achat de véhicules
        entre particuliers et professionnels.
        Notre objectif est de proposer une expérience simple, fiable et rapide pour
        trouver le véhicule idéal.
    </p>
</section>

<section id="contact-us" class="mt-10 bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="text-2xl font-bold mb-3">Nous contacter</h2>

    <p class="text-sm md:text-base text-gray-600 mb-4">
        Une question, une suggestion ou un problème ?
        Contactez-nous :
    </p>

    <ul class="text-sm md:text-base text-gray-700 space-y-2">
        <li>📧 Email : <strong>contact@caro.dz</strong></li>
        <li>📞 Téléphone : <strong>05 00 00 00 00</strong></li>
    </ul>
</section>




    {{-- JS: handle vehicle type buttons + dynamic models (si tu l’utilises déjà, fusionne) --}}
    <script>
        // Handle vehicle type button selection
        const typeInput = document.getElementById('vehicle_type_input');
        const typeButtons = document.querySelectorAll('.vehicle-type-btn');
        const homeBrandDropdown = document.getElementById('home_brand_dropdown');
        const homeMarqueHidden = document.getElementById('home_marque_hidden');
        const homeMarqueText = document.getElementById('home_marque_text');
        const homeModeleInput = document.getElementById('home_modele_input');
        const homeModelDatalist = document.getElementById('home_model_options');

        const brandModelData = @json($brandModelMap);
        const brandModelLookup = Array.isArray(brandModelData)
            ? Object.fromEntries(brandModelData.map(entry => [entry.brand, entry.models]))
            : {};

        function updateHomeModelOptions(brand) {
            if (!homeModelDatalist) {
                return;
            }

            homeModelDatalist.innerHTML = '';
            const models = brand ? (brandModelLookup[brand] || []) : [];

            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                homeModelDatalist.appendChild(option);
            });
        }

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

            if (homeBrandDropdown) {
                homeBrandDropdown.classList.toggle('hidden', isMoto);
            }

            if (homeMarqueHidden) {
                homeMarqueHidden.disabled = isMoto;
                if (isMoto) {
                    homeMarqueHidden.value = '';
                }
            }

            if (homeMarqueText) {
                homeMarqueText.classList.toggle('hidden', !isMoto);
                homeMarqueText.disabled = !isMoto;
                const placeholder = isMoto
                    ? homeMarqueText.dataset.placeholderMoto
                    : homeMarqueText.dataset.placeholderVoiture;
                if (placeholder) {
                    homeMarqueText.placeholder = placeholder;
                }
            }

            if (homeModeleInput) {
                const placeholder = isMoto
                    ? homeModeleInput.dataset.placeholderMoto
                    : homeModeleInput.dataset.placeholderVoiture;
                if (placeholder) {
                    homeModeleInput.placeholder = placeholder;
                }
                if (isMoto) {
                    homeModeleInput.value = '';
                }
            }

            if (isMoto) {
                updateHomeModelOptions('');
            } else if (homeMarqueHidden && homeMarqueHidden.value) {
                updateHomeModelOptions(homeMarqueHidden.value);
            }
        }

        if (typeInput && typeInput.value) {
            setActiveHomeTypeButton(typeInput.value);
        }
        applyHomeVehicleTypeUI(typeInput ? typeInput.value : '');

        typeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                typeInput.value = type;

                // Update active state
                setActiveHomeTypeButton(type);
                applyHomeVehicleTypeUI(type);
            });
        });

        // Dynamic models loading (si tu l'avais déjà, garde ta logique)
        const baseUrlFilter = "{{ url('/api/marques') }}";
        const selectMarqueFilter = document.getElementById('filter_marque');
        const selectModeleFilter = document.getElementById('filter_modele');

        if (selectMarqueFilter) {
            selectMarqueFilter.addEventListener('change', function () {
                const marqueId = this.value;

                if (!marqueId) {
                    selectModeleFilter.innerHTML = '<option value=\"\">Peu importe</option>';
                    return;
                }

                fetch(`${baseUrlFilter}/${marqueId}/modeles`)
                    .then(response => response.json())
                    .then(data => {
                        selectModeleFilter.innerHTML = '<option value=\"\">Peu importe</option>';
                        data.forEach(modele => {
                            const option = document.createElement('option');
                            option.value = modele.id;
                            option.textContent = modele.name;
                            selectModeleFilter.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading models:', error);
                    });
            });
        }

        // Alpine.js dropdown for brand (home)
        function brandDropdownHome() {
            return {
                open: false,
                search: '',
                selected: "{{ request('marque') }}",
                brands: @json($marques),
                filteredBrands() {
                    return this.search
                        ? this.brands.filter(b => b.toLowerCase().includes(this.search.toLowerCase()))
                        : this.brands;
                },
                selectBrand(value) {
                    this.selected = value;
                    this.open = false;
                    const input = document.querySelector('input[name="marque"]');
                    if (input) {
                        input.value = value;
                    }
                    if (homeModeleInput) {
                        homeModeleInput.value = '';
                    }
                    updateHomeModelOptions(value);
                }
            };
        }

        if (homeMarqueHidden && homeMarqueHidden.value) {
            updateHomeModelOptions(homeMarqueHidden.value);
        }
    </script>
@endsection
