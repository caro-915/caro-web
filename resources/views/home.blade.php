@extends('layouts.app')

@section('content')

    {{-- HERO : Search form + marketing block --}}
    <section class="mb-10">
        <div class="bg-gradient-to-r from-gray-100 to-white rounded-3xl px-6 py-8 md:px-10 md:py-12 flex flex-col md:flex-row gap-8 md:items-center">
            
            {{-- LEFT : Search card --}}
            <div class="w-full md:max-w-md">
                                <form method="GET" action="{{ route('annonces.search') }}" class="bg-white rounded-3xl shadow-lg p-5 md:p-6 space-y-4">    
                    {{-- Vehicle type selector --}}
                    <div class="flex items-center gap-2 text-xs md:text-sm">
                        {{-- Hidden field actually used by backend --}}
                        <input type="hidden" name="vehicle_type" id="vehicle_type_input" value="{{ request('vehicle_type', 'car') }}">
                        
                        {{-- Buttons are only UI helpers that update the hidden field --}}
                        <button type="button"
                                data-type="car"
                                class="vehicle-type-btn flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                                       {{ request('vehicle_type', 'car') === 'car' ? 'bg-pink-500 text-white border-pink-500' : 'bg-white text-gray-600 border-gray-200' }}">
                            🚗 Voiture
                        </button>
                        <button type="button"
                                data-type="van"
                                class="vehicle-type-btn flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                                       {{ request('vehicle_type') === 'van' ? 'bg-pink-500 text-white border-pink-500' : 'bg-white text-gray-600 border-gray-200' }}">
                            🚐 Utilitaire
                        </button>
                        <button type="button"
                                data-type="moto"
                                class="vehicle-type-btn flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                                       {{ request('vehicle_type') === 'moto' ? 'bg-pink-500 text-white border-pink-500' : 'bg-white text-gray-600 border-gray-200' }}">
                            🏍 Moto
                        </button>
                    </div>

                    {{-- Brand / Model --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Marque</label>
                            <select name="marque" id="filter_marque" ...>
    <option value="">Peu importe</option>
    @foreach ($marques as $brand)
       
             <option value="{{ $brand->name }}">{{ $brand->name }}</option>
        </option>
    @endforeach
</select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1">Modèle</label>
                            <select name="modele" id="filter_modele" ...>
    <option value="">Peu importe</option>
    @foreach ($modeles as $modele)
        <option value="{{ $modele->name }}" 
            {{ request('modele') == $modele->name ? 'selected' : '' }}>
            {{ $modele->name }}
        </option>
    @endforeach
</select>
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
                                class="w-full py-3 rounded-full bg-pink-600 text-white text-sm font-semibold hover:bg-pink-700">
                            Rechercher
                        </button>
                    </div>
                </form>
            </div>

            {{-- RIGHT : Marketing block "vendre son véhicule" --}}
            <div class="flex-1">
                <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-4">
                    Trouvez <span class="text-pink-500">la voiture d’occasion</span><br>
                    qui vous correspond
                </h1>
                <p class="text-gray-600 text-sm md:text-base mb-6 max-w-xl">
                    Recherchez parmi des milliers d'annonces vérifiées partout en Algérie. 
                    Filtrez par marque, budget, wilaya et trouvez votre prochaine voiture en quelques clics.
                </p>

                {{-- Marketing block for selling a vehicle --}}
                <div class="bg-white bg-opacity-80 border border-pink-100 rounded-2xl p-4 md:p-5 inline-flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex-1">
                        <h2 class="text-sm md:text-base font-semibold mb-1">Vendre votre véhicule ?</h2>
                        <p class="text-xs md:text-sm text-gray-600">
                            Déposez votre annonce gratuitement et touchez des milliers d’acheteurs potentiels en quelques minutes.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('annonces.create') }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-600 text-white text-xs md:text-sm font-semibold hover:bg-pink-700">
                            Déposer une annonce
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION : Marques populaires --}}
    <section class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Marques populaires</h2>
        <div class="bg-white rounded-2xl shadow px-4 py-4 flex flex-wrap gap-3">
            @forelse ($popularMarques as $marque)
                <a href="{{ route('home', ['marque' => $marque->name]) }}"
                   class="px-3 py-1 rounded-full border text-xs md:text-sm text-gray-700 hover:border-pink-500 hover:text-pink-600">
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
                   class="px-3 py-1 rounded-full border text-xs md:text-sm text-gray-700 hover:border-pink-500 hover:text-pink-600">
                    {{ $modele->name }} ({{ $modele->annonces_count }})
                </a>
            @empty
                <p class="text-xs text-gray-500">Aucun modèle populaire pour le moment.</p>
            @endforelse
        </div>
    </section>


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

        typeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.getAttribute('data-type');
                typeInput.value = type;

                // Update active state
                typeButtons.forEach(b => b.classList.remove('bg-pink-500', 'text-white', 'border-pink-500'));
                typeButtons.forEach(b => b.classList.add('bg-white', 'text-gray-600', 'border-gray-200'));
                btn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                btn.classList.add('bg-pink-500', 'text-white', 'border-pink-500');
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
    </script>
@endsection
