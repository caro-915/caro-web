@extends('layouts.app')

@section('content')
@php
    $subscriptionServiceIntro = app(\App\Services\SubscriptionService::class);
    $features = $subscriptionServiceIntro->getFeatures(auth()->user());
    $maxImagesIntro = $features['max_images_per_ad'] ?? 4;
@endphp
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-1">Déposer une annonce</h1>
        <p class="text-xs md:text-sm text-gray-500">
            Remplissez les informations de votre vehicule et ajoutez jusqu'à {{ $maxImagesIntro }} photos.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-xs md:text-sm bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('annonces.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow p-4 md:p-6 space-y-6">
        @csrf

        {{-- Vendeur professionnel ou particulier ? --}}
        <div>
            <label class="block text-xs font-semibold mb-2">Vendeur professionnel ou particulier ? <span class="text-red-500">*</span></label>
            <div class="flex gap-4 text-xs md:text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="seller_type" value="particulier"
                           {{ old('seller_type', 'particulier') === 'particulier' ? 'checked' : '' }}>
                    Particulier
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="seller_type" value="pro"
                           {{ old('seller_type', 'particulier') === 'pro' ? 'checked' : '' }}>
                    Professionnel
                </label>
            </div>
            @error('seller_type')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Véhicule neuf ? --}}
        <div>
            <label class="block text-xs font-semibold mb-2">Véhicule neuf ? <span class="text-red-500">*</span></label>
            <div class="flex gap-4 text-xs md:text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="condition" value="non"
                           {{ old('condition', 'non') === 'non' ? 'checked' : '' }}>
                    Non
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="condition" value="oui"
                           {{ old('condition', 'non') === 'oui' ? 'checked' : '' }}>
                    Oui
                </label>
            </div>
            @error('condition')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Type de véhicule --}}
        <div>
            <label class="block text-xs font-semibold mb-2">Type de véhicule <span class="text-red-500">*</span></label>
            <input type="hidden" name="vehicle_type" id="vehicle_type_input" value="{{ old('vehicle_type') }}">
            <div class="flex items-center gap-2 text-xs md:text-sm">
                <button type="button"
                        data-type="Voiture"
                        class="vehicle-type-btn-create flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                               {{ old('vehicle_type') === 'Voiture' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-200' }}">
                    🚗 Voiture
                </button>
                <button type="button"
                        data-type="Moto"
                        class="vehicle-type-btn-create flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                               {{ old('vehicle_type') === 'Moto' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-200' }}">
                    🏍 Moto
                </button>
            </div>
            @error('vehicle_type')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Titre + prix --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1">Titre de l'annonce <span class="text-red-500">*</span></label>
                <input type="text" name="titre" id="titre_input" value="{{ old('titre') }}"
                       data-placeholder-voiture="ex : Renault Clio 1.5 DCI 2018 très bon état"
                       data-placeholder-moto="ex : Yamaha MT-07 2021 très bon état"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('titre') ? 'border-red-500' : '' }}"
                       placeholder="ex : Renault Clio 1.5 DCI 2018 très bon état">
                @error('titre')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Prix (DA) <span class="text-red-500">*</span></label>
                <input type="number" name="prix" value="{{ old('prix') }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('prix') ? 'border-red-500' : '' }}"
                       placeholder="ex : 2500000">
                @error('prix')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Marque / modèle / ville --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Marque</label>

                <div id="brand_dropdown_wrapper" x-data="brandDropdownCreate()" class="relative">
                    <button type="button" @click="open = !open"
                            class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm text-left bg-white flex justify-between items-center {{ $errors->has('marque') ? 'border-red-500' : '' }}">
                        <span x-text="selected || 'Sélectionner une marque'"></span>
                        <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>

                    <input type="hidden" name="marque" id="marque_hidden_input" :value="selected">

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
                            <template x-for="brand in filteredBrands()" :key="brand">
                                <button type="button"
                                        @click="selectBrand(brand)"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b last:border-b-0">
                                    <span x-text="brand"></span>
                                </button>
                            </template>
                            <div x-show="filteredBrands().length === 0" class="px-3 py-2 text-gray-500 text-xs text-center">
                                Aucune marque trouvée
                            </div>
                        </div>
                    </div>

                    <select name="marque" id="marque_hidden_select" class="hidden">
                        <option value=""></option>
                    </select>
                </div>

                <input type="text" name="marque" id="marque_text_input" value="{{ old('marque') }}"
                       data-placeholder-voiture="ex : Renault, Peugeot"
                       data-placeholder-moto="ex : Yamaha, Honda"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm hidden"
                       placeholder="ex : Yamaha, Honda"
                       disabled>

                @error('marque')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Modèle</label>
                
                <div id="model_dropdown_wrapper" x-data="modelDropdownCreate()" class="relative">
                    <button type="button" 
                            @click="open = !open"
                            :disabled="!availableModels.length"
                            :class="{ 'opacity-50 cursor-not-allowed': !availableModels.length }"
                            class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm text-left bg-white flex justify-between items-center">
                        <span x-text="selected || (availableModels.length ? 'Sélectionner un modèle' : 'Choisissez une marque d\'abord')"></span>
                        <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>

                    <input type="hidden" name="modele" id="modele_hidden_input" :value="selected">

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
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b font-semibold">
                                <span>Peu importe</span>
                            </button>
                            <template x-for="model in filteredModels()" :key="model">
                                <button type="button"
                                        @click="selectModel(model)"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b last:border-b-0">
                                    <span x-text="model"></span>
                                </button>
                            </template>
                            <div x-show="filteredModels().length === 0" class="px-3 py-2 text-gray-500 text-xs text-center">
                                Aucun modèle trouvé
                            </div>
                        </div>
                    </div>
                </div>

                <input type="text" name="modele" id="modele_text_input" value="{{ old('modele') }}"
                       data-placeholder-voiture="ex : Clio, Megane"
                       data-placeholder-moto="ex : MT-07, CB500F"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm hidden"
                       placeholder="ex : MT-07, CB500F"
                       disabled>
            </div>

            <div x-data="wilayaDropdownCreate()" class="relative">
                <label class="block text-xs font-semibold mb-1">Wilaya</label>
                
                <button type="button" @click="toggleDropdown()"
                        class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm text-left bg-white flex justify-between items-center">
                    <span x-text="selected || 'Sélectionner une wilaya'"></span>
                    <svg class="w-4 h-4 transition" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>

                <input type="hidden" name="ville" :value="selected">

                {{-- Dropdown menu --}}
                <div x-show="open" @click.away="open = false"
                     class="absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50 overflow-hidden flex flex-col"
                     style="max-height: 280px;">
                    
                    {{-- Barre de recherche --}}
                    <div class="sticky top-0 p-2 border-b bg-white">
                        <input type="text" x-model="search" placeholder="Rechercher une wilaya..."
                               class="w-full border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-pink-500">
                    </div>
                    
                    {{-- Liste des wilayas (scroll après 8 items) --}}
                    <div class="overflow-y-auto flex-1" style="max-height: 240px;">
                        <button type="button"
                                @click="selectWilaya('')"
                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b font-semibold">
                            <span>Toute l'Algérie (Peu importe)</span>
                        </button>
                        <template x-for="wilaya in filteredWilayas()" :key="wilaya">
                            <button type="button"
                                    @click="selectWilaya(wilaya)"
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 border-b last:border-b-0">
                                <span x-text="wilaya"></span>
                            </button>
                        </template>
                        <div x-show="filteredWilayas().length === 0" class="px-3 py-2 text-gray-500 text-xs text-center">
                            Aucune wilaya trouvée
                        </div>
                    </div>
                </div>

                <select name="ville" class="hidden">
                    <option value=""></option>
                </select>
            </div>
        </div>
        
        {{-- Année / km / carburant / boite --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Année</label>
                <input type="number" name="annee" value="{{ old('annee') }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                       placeholder="ex : 2018">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Kilométrage (km)</label>
                <input type="number" name="kilometrage" value="{{ old('kilometrage') }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                       placeholder="ex : 120000">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Carburant <span class="text-red-500">*</span></label>
                <select name="carburant" id="carburant_select" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('carburant') ? 'border-red-500' : '' }}">
                    <option value="">Sélectionnez</option>
                    @foreach(['Essence','Diesel','Hybride','Électrique'] as $fuel)
                        <option value="{{ $fuel }}" {{ old('carburant') === $fuel ? 'selected' : '' }}>
                            {{ $fuel }}
                        </option>
                    @endforeach
                </select>
                @error('carburant')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Boîte de vitesses <span class="text-red-500">*</span></label>
                <select name="boite_vitesse" id="boite_vitesse_select" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('boite_vitesse') ? 'border-red-500' : '' }}">
                    <option value="">Sélectionnez</option>
                    @foreach(['Manuelle','Automatique'] as $gear)
                        <option value="{{ $gear }}" {{ old('boite_vitesse') === $gear ? 'selected' : '' }}>
                            {{ $gear }}
                        </option>
                    @endforeach
                </select>
                @error('boite_vitesse')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Détails --}}
        <div>
            <h2 class="text-sm md:text-base font-bold mb-3">Détails</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-1">Couleur</label>
                    <select name="couleur" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                        <option value="">— Choisir —</option>
                        <option value="Blanc" {{ old('couleur') === 'Blanc' ? 'selected' : '' }}>Blanc</option>
                        <option value="Noir" {{ old('couleur') === 'Noir' ? 'selected' : '' }}>Noir</option>
                        <option value="Gris" {{ old('couleur') === 'Gris' ? 'selected' : '' }}>Gris</option>
                        <option value="Argent" {{ old('couleur') === 'Argent' ? 'selected' : '' }}>Argent</option>
                        <option value="Bleu" {{ old('couleur') === 'Bleu' ? 'selected' : '' }}>Bleu</option>
                        <option value="Rouge" {{ old('couleur') === 'Rouge' ? 'selected' : '' }}>Rouge</option>
                        <option value="Vert" {{ old('couleur') === 'Vert' ? 'selected' : '' }}>Vert</option>
                        <option value="Beige" {{ old('couleur') === 'Beige' ? 'selected' : '' }}>Beige</option>
                        <option value="Orange" {{ old('couleur') === 'Orange' ? 'selected' : '' }}>Orange</option>
                        <option value="Marron" {{ old('couleur') === 'Marron' ? 'selected' : '' }}>Marron</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1">Document</label>
                    <select name="document_type" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                        <option value="">— Choisir —</option>
                        <option value="carte_grise" {{ old('document_type') === 'carte_grise' ? 'selected' : '' }}>Carte grise</option>
                        <option value="procuration" {{ old('document_type') === 'procuration' ? 'selected' : '' }}>Procuration</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1">Finition</label>
                    <input type="text" name="finition" id="finition_input" value="{{ old('finition') }}"
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                           placeholder="Ex : Allure, GT Line, Titanium">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="mt-2">
            <label class="inline-flex items-center gap-2 text-xs md:text-sm text-gray-700">
                <input type="checkbox" name="show_phone" value="1"
                       class="rounded border-gray-300 text-gray-800 focus:ring-gray-800"
                       {{ old('show_phone', 1) ? 'checked' : '' }}>
                <span>Afficher mon numéro de téléphone sur l’annonce</span>
            </label>
            <p class="mt-1 text-[11px] text-gray-400">
                Si vous décochez, les acheteurs pourront uniquement vous envoyer des messages via ElSayara.
            </p>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-xs font-semibold mb-1">Description</label>
            <textarea name="description" rows="5"
                      class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                      placeholder="Décrivez l'état du vehicule, l'historique, les options, etc.">{{ old('description') }}</textarea>
        </div>

        {{-- Images --}}
        <div>
            <label class="block text-xs font-semibold mb-1">
                Photos du vehicule 
                <span class="text-gray-400">(jusqu'à {{ $maxImagesIntro }} photos)</span>
            </label>
            <p class="text-[11px] text-gray-500 mb-2">
                Formats acceptés : JPG, JPEG, PNG, WEBP. Taille max : 4 Mo par photo.
            </p>

            <div id="images_container">
                <!-- Initial input -->
                <div class="image-input-group flex items-center gap-2 mb-2">
                    <input type="file"
                           name="images[]"
                           accept="image/*"
                           class="flex-1 text-xs md:text-sm text-gray-600
                                  file:mr-3 file:py-1 file:px-3
                                  file:rounded-lg file:border-0
                                  file:text-xs file:font-semibold
                                  file:bg-gray-50 file:text-gray-900
                                  hover:file:bg-gray-100">
                    <button type="button" class="remove-image-btn text-red-500 hover:text-red-700" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="button" id="add_image_btn"
                    class="mt-2 px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                + Ajouter une photo
            </button>

            <div id="images_preview" class="mt-3 grid grid-cols-2 md:grid-cols-5 gap-2"></div>
        </div>

        {{-- Actions --}}
        <div class="pt-2 flex flex-col md:flex-row gap-3 md:justify-end">
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-gray-200 text-xs md:text-sm text-gray-600 hover:border-gray-300">
                Annuler
            </a>
            <button type="submit" id="submitBtn"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-gray-800 text-white text-xs md:text-sm font-semibold hover:bg-gray-900">
                <span id="submitText">Publier l'annonce</span>
                <span id="submitLoader" class="hidden ml-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let formSubmitted = false;

    // Pré-sélectionner "Voiture" par défaut si aucune valeur n'a été définie
    const vehicleTypeInput = document.getElementById('vehicle_type_input');
    const vehicleButtons = document.querySelectorAll('.vehicle-type-btn-create');
    const brandDropdownWrapper = document.getElementById('brand_dropdown_wrapper');
    const modelDropdownWrapper = document.getElementById('model_dropdown_wrapper');
    const marqueHiddenInput = document.getElementById('marque_hidden_input');
    const marqueHiddenSelect = document.getElementById('marque_hidden_select');
    const marqueTextInput = document.getElementById('marque_text_input');
    const modeleTextInput = document.getElementById('modele_text_input');
    const titreInput = document.getElementById('titre_input');

    function setActiveVehicleButton(type) {
        vehicleButtons.forEach(btn => {
            const isActive = btn.getAttribute('data-type') === type;
            btn.classList.toggle('bg-gray-800', isActive);
            btn.classList.toggle('text-white', isActive);
            btn.classList.toggle('border-gray-800', isActive);
            btn.classList.toggle('bg-white', !isActive);
            btn.classList.toggle('text-gray-700', !isActive);
            btn.classList.toggle('border-gray-200', !isActive);
        });
    }

    function applyVehicleTypeUI(type) {
        const isMoto = type === 'Moto';

        // Pour les motos, on cache les dropdowns marque/modèle et affiche les champs texte
        if (brandDropdownWrapper) {
            brandDropdownWrapper.classList.toggle('hidden', isMoto);
        }

        if (modelDropdownWrapper) {
            modelDropdownWrapper.classList.toggle('hidden', isMoto);
        }

        if (marqueTextInput) {
            marqueTextInput.classList.toggle('hidden', !isMoto);
            marqueTextInput.disabled = !isMoto;
            const placeholder = isMoto
                ? marqueTextInput.dataset.placeholderMoto
                : marqueTextInput.dataset.placeholderVoiture;
            if (placeholder) {
                marqueTextInput.placeholder = placeholder;
            }
        }

        if (modeleTextInput) {
            modeleTextInput.classList.toggle('hidden', !isMoto);
            modeleTextInput.disabled = !isMoto;
            const placeholder = isMoto
                ? modeleTextInput.dataset.placeholderMoto
                : modeleTextInput.dataset.placeholderVoiture;
            if (placeholder) {
                modeleTextInput.placeholder = placeholder;
            }
        }

        if (marqueHiddenInput) {
            marqueHiddenInput.disabled = isMoto;
            if (isMoto) {
                // Réinitialiser les dropdowns Alpine
                const brandDropdown = Alpine.$data(brandDropdownWrapper);
                if (brandDropdown) {
                    brandDropdown.selected = '';
                }
                
                const modelDropdown = Alpine.$data(modelDropdownWrapper);
                if (modelDropdown) {
                    modelDropdown.selected = '';
                    modelDropdown.availableModels = [];
                }
            }
        }

        if (marqueHiddenSelect) {
            marqueHiddenSelect.disabled = isMoto;
        }

        if (titreInput) {
            const placeholder = isMoto
                ? titreInput.dataset.placeholderMoto
                : titreInput.dataset.placeholderVoiture;
            if (placeholder) {
                titreInput.placeholder = placeholder;
            }
        }

        // Griser les champs boite_vitesse et finition pour Moto
        const boiteVitesseSelect = document.getElementById('boite_vitesse_select');
        const finitionInput = document.getElementById('finition_input');

        if (boiteVitesseSelect) {
            if (isMoto) {
                // Pour Moto: désactiver + mettre valeur N/A
                boiteVitesseSelect.disabled = true;
                boiteVitesseSelect.value = 'N/A';
                // Ajouter option N/A si elle n'existe pas
                if (!Array.from(boiteVitesseSelect.options).find(opt => opt.value === 'N/A')) {
                    const naOption = new Option('N/A', 'N/A', true, true);
                    boiteVitesseSelect.add(naOption);
                }
            } else {
                // Pour Voiture: réactiver + retirer N/A
                boiteVitesseSelect.disabled = false;
                const naOption = Array.from(boiteVitesseSelect.options).find(opt => opt.value === 'N/A');
                if (naOption) {
                    naOption.remove();
                }
                // Réinitialiser si était N/A
                if (boiteVitesseSelect.value === 'N/A') {
                    boiteVitesseSelect.value = '';
                }
            }
            boiteVitesseSelect.classList.toggle('opacity-50', isMoto);
            boiteVitesseSelect.classList.toggle('cursor-not-allowed', isMoto);
        }

        if (finitionInput) {
            finitionInput.disabled = isMoto;
            finitionInput.classList.toggle('opacity-50', isMoto);
            finitionInput.classList.toggle('cursor-not-allowed', isMoto);
        }

        // Masquer/afficher l'option Hybride dans le carburant selon le type
        const carburantSelect = document.getElementById('carburant_select');
        if (carburantSelect) {
            const hybridOption = Array.from(carburantSelect.options).find(opt => opt.value === 'Hybride');
            if (hybridOption) {
                hybridOption.hidden = isMoto;
                // Si Hybride était sélectionné et on passe à Moto, réinitialiser
                if (isMoto && carburantSelect.value === 'Hybride') {
                    carburantSelect.value = '';
                }
            }
        }
    }
    
    if (!vehicleTypeInput.value) {
        // Pas de valeur sauvegardée, pré-sélectionner "Voiture"
        vehicleTypeInput.value = 'Voiture';
    }

    setActiveVehicleButton(vehicleTypeInput.value);
    applyVehicleTypeUI(vehicleTypeInput.value);

    // Gestion des clics sur les boutons de type de véhicule
    vehicleButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const type = btn.getAttribute('data-type');
            vehicleTypeInput.value = type;
            setActiveVehicleButton(type);
            applyVehicleTypeUI(type);
        });
    });

    // Validation function
    function validateForm() {
        const errors = [];
        const errorFields = [];
        
        // Reset all borders
        document.querySelectorAll('input, select').forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        // Titre
        const titre = document.querySelector('input[name="titre"]');
        if (!titre || !titre.value.trim()) {
            errors.push('Le titre est obligatoire.');
            if (titre) {
                titre.classList.add('border-red-500');
                errorFields.push(titre);
            }
        }
        
        // Prix
        const prix = document.querySelector('input[name="prix"]');
        if (!prix || !prix.value.trim()) {
            errors.push('Le prix est obligatoire.');
            if (prix) {
                prix.classList.add('border-red-500');
                errorFields.push(prix);
            }
        }
        
        // Carburant
        const carburant = document.querySelector('select[name="carburant"]');
        if (!carburant || !carburant.value) {
            errors.push('Le type de carburant est obligatoire.');
            if (carburant) {
                carburant.classList.add('border-red-500');
                errorFields.push(carburant);
            }
        }
        
        // Boîte de vitesses (seulement si Voiture)
        const vehicleType = document.querySelector('input[name="vehicle_type"]');
        const isVoiture = vehicleType && vehicleType.value === 'Voiture';
        
        if (isVoiture) {
            const boiteVitesse = document.querySelector('select[name="boite_vitesse"]');
            if (!boiteVitesse || !boiteVitesse.value || boiteVitesse.value === '') {
                errors.push('La boîte de vitesses est obligatoire.');
                if (boiteVitesse) {
                    boiteVitesse.classList.add('border-red-500');
                    errorFields.push(boiteVitesse);
                }
            }
        }
        
        return { errors, errorFields };
    }

    // Display errors
    function displayErrors(errors, errorFields) {
        // Remove existing error box
        const existingError = document.querySelector('.validation-errors');
        if (existingError) {
            existingError.remove();
        }
        
        if (errors.length === 0) return;
        
        const errorBox = document.createElement('div');
        errorBox.className = 'validation-errors mb-4 text-xs md:text-sm bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3';
        
        let errorHtml = '<p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p><ul class="list-disc list-inside space-y-0.5">';
        errors.forEach(error => {
            errorHtml += `<li>${error}</li>`;
        });
        errorHtml += '</ul>';
        
        errorBox.innerHTML = errorHtml;
        
        // Insert before form
        const form = document.querySelector('form');
        form.parentNode.insertBefore(errorBox, form);
        
        // Scroll to first error field or error box
        if (errorFields && errorFields.length > 0) {
            errorFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Form submit validation
    const annonceForm = document.querySelector('form[action*="annonces"]');
    if (annonceForm) {
        console.log('Form found and listener being attached');
        annonceForm.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            
            // Validate form
            const result = validateForm();
            console.log('Validation result:', result);
            
            if (result.errors.length > 0) {
                console.log('Validation failed - preventing submission');
                e.preventDefault();
                e.stopPropagation();
                displayErrors(result.errors, result.errorFields);
                return false;
            }
            
            console.log('Validation passed - showing loader');
            formSubmitted = true;
            
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoader = document.getElementById('submitLoader');
            
            if (submitBtn && submitText && submitLoader) {
                submitBtn.disabled = true;
                submitText.textContent = 'Publication en cours...';
                submitLoader.classList.remove('hidden');
            }
        });
    } else {
        console.error('Form not found! Selector: form[action*="annonces"]');
    }

});
    // Dynamic models based on brand (removed - now using text inputs)
    
    const imagesContainer = document.getElementById('images_container');
    const addImageBtn = document.getElementById('add_image_btn');
    const imagesPreview = document.getElementById('images_preview');

    const MAX_IMAGES = {{ $maxImagesIntro }};
    const IS_PRO = {{ $isPro ? 'true' : 'false' }};
    let imageCount = 1;

    console.log('=== IMAGE UPLOAD CONFIG ===');
    console.log('MAX_IMAGES:', MAX_IMAGES);
    console.log('IS_PRO:', IS_PRO);
    console.log('Initial imageCount:', imageCount);

    addImageBtn.addEventListener('click', () => {
        console.log('Click ajouter - imageCount actuel:', imageCount, '/ MAX_IMAGES:', MAX_IMAGES);
        if (imageCount >= MAX_IMAGES) {
            alert('Vous pouvez ajouter au maximum ' + MAX_IMAGES + ' photos.' + 
                  (IS_PRO ? '' : ' Passez PRO pour 8 photos!'));
            return;
        }

        imageCount++;
        const newInputGroup = document.createElement('div');
        newInputGroup.className = 'image-input-group flex items-center gap-2 mb-2';
        newInputGroup.innerHTML = `
            <input type="file"
                   name="images[]"
                   accept="image/*"
                   class="flex-1 text-xs md:text-sm text-gray-600
                          file:mr-3 file:py-1 file:px-3
                          file:rounded-lg file:border-0
                          file:text-xs file:font-semibold
                          file:bg-gray-50 file:text-gray-900
                          hover:file:bg-gray-100">
            <button type="button" class="remove-image-btn text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        imagesContainer.appendChild(newInputGroup);
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const groups = imagesContainer.querySelectorAll('.image-input-group');
        groups.forEach((group, index) => {
            const removeBtn = group.querySelector('.remove-image-btn');
            if (groups.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    imagesContainer.addEventListener('click', (e) => {
        if (e.target.closest('.remove-image-btn')) {
            e.target.closest('.image-input-group').remove();
            imageCount--;
            updateRemoveButtons();
            updatePreview();
        }
    });

    function updatePreview() {
        imagesPreview.innerHTML = '';
        const inputs = imagesContainer.querySelectorAll('input[type="file"]');
        inputs.forEach((input, index) => {
            if (input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-20 object-cover rounded-lg border border-gray-200';
                    imagesPreview.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    }

    imagesContainer.addEventListener('change', updatePreview);

    // Alpine.js: Données partagées pour marque/modèle
    const brandModelsData = @json($brandModelsMap);

    // Brand Dropdown for Create Annonce (Alpine.js)
    function brandDropdownCreate() {
        return {
            open: false,
            search: '',
            selected: '{{ old("marque") }}',
            brands: @json($brands->map(function($b) { return $b; })),
            
            filteredBrands() {
                return this.brands.filter(brand => 
                    brand.toLowerCase().includes(this.search.toLowerCase())
                );
            },
            
            selectBrand(value) {
                this.selected = value;
                this.search = value;
                this.open = false;
                document.querySelector('select[name="marque"]').value = value;
                
                // Mettre à jour les modèles disponibles
                const modelDropdown = Alpine.$data(document.getElementById('model_dropdown_wrapper'));
                if (modelDropdown) {
                    modelDropdown.updateAvailableModels(value);
                    modelDropdown.selected = '';
                }
            }
        }
    }

    // Model Dropdown for Create Annonce (Alpine.js)
    function modelDropdownCreate() {
        return {
            open: false,
            search: '',
            selected: '{{ old("modele") }}',
            availableModels: [],
            init() {
                const selectedBrand = document.querySelector('input[name="marque"]')?.value || '{{ old("marque") }}';
                if (selectedBrand) {
                    this.updateAvailableModels(selectedBrand);
                }
            },
            updateAvailableModels(brand) {
                if (brand && brandModelsData[brand]) {
                    this.availableModels = brandModelsData[brand];
                } else {
                    this.availableModels = [];
                    this.selected = '';
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
        }
    }

    // Wilaya Dropdown for Create Annonce (Alpine.js)
    function wilayaDropdownCreate() {
        return {
            open: false,
            search: '',
            selected: '{{ old("ville") }}',
            wilayas: @json($wilayas),
            
            filteredWilayas() {
                return this.wilayas.filter(wilaya => 
                    wilaya.toLowerCase().includes(this.search.toLowerCase())
                );
            },
            
            selectWilaya(value) {
                this.selected = value;
                this.open = false;
                document.querySelector('select[name="ville"]').value = value;
            },
            
            toggleDropdown() {
                this.open = !this.open;
                if (this.open) {
                    this.search = ''; // Vider le champ de recherche quand on ouvre le dropdown
                }
            }
        }
    }
</script>
@endsection
