@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-1">Déposer une annonce</h1>
        <p class="text-xs md:text-sm text-gray-500">
            Remplissez les informations de votre vehicule et ajoutez jusqu'à 5 photos.
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
                        data-type="Utilitaire"
                        class="vehicle-type-btn-create flex-1 flex items-center justify-center gap-1 py-2 rounded-full border text-xs md:text-sm
                               {{ old('vehicle_type') === 'Utilitaire' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-200' }}">
                    🚐 Utilitaire
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
                <input type="text" name="titre" value="{{ old('titre') }}"
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
                <label class="block text-xs font-semibold mb-1">Marque <span class="text-red-500">*</span></label>
                <select name="marque" id="marque_select" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('marque') ? 'border-red-500' : '' }}">
                    <option value="">Sélectionnez une marque</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->name }}" {{ old('marque') === $brand->name ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                @error('marque')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Modèle</label>
                <select name="modele" id="modele_select" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez d'abord une marque</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Ville / Wilaya</label>
                <input type="text" name="ville" value="{{ old('ville') }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                       placeholder="ex : Alger">
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
                <select name="carburant" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('carburant') ? 'border-red-500' : '' }}">
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
                <select name="boite_vitesse" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('boite_vitesse') ? 'border-red-500' : '' }}">
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
                    <input type="text" name="finition" value="{{ old('finition') }}"
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
                Si vous décochez, les acheteurs pourront uniquement vous envoyer des messages via Caro.
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
                Photos du vehicule <span class="text-gray-400">(jusqu'à 5 photos)</span>
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
    
    if (!vehicleTypeInput.value) {
        // Pas de valeur sauvegardée, pré-sélectionner "Voiture"
        vehicleTypeInput.value = 'Voiture';
        vehicleButtons.forEach(btn => {
            if (btn.getAttribute('data-type') === 'Voiture') {
                btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
                btn.classList.add('bg-gray-800', 'text-white', 'border-gray-800');
            } else {
                btn.classList.remove('bg-gray-800', 'text-white', 'border-gray-800');
                btn.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
            }
        });
    }

    // Gestion des clics sur les boutons de type de véhicule
    vehicleButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const type = btn.getAttribute('data-type');
            vehicleTypeInput.value = type;
            
            vehicleButtons.forEach(b => {
                b.classList.remove('bg-gray-800', 'text-white', 'border-gray-800');
                b.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
            });
            btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
            btn.classList.add('bg-gray-800', 'text-white', 'border-gray-800');
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
        
        // Marque
        const marque = document.querySelector('select[name="marque"]');
        if (!marque || !marque.value) {
            errors.push('La marque est obligatoire.');
            if (marque) {
                marque.classList.add('border-red-500');
                errorFields.push(marque);
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
        
        // Boîte de vitesses
        const boiteVitesse = document.querySelector('select[name="boite_vitesse"]');
        if (!boiteVitesse || !boiteVitesse.value) {
            errors.push('La boîte de vitesses est obligatoire.');
            if (boiteVitesse) {
                boiteVitesse.classList.add('border-red-500');
                errorFields.push(boiteVitesse);
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
    const typeInputCreate = document.getElementById('vehicle_type_input');
    const typeButtonsCreate = document.querySelectorAll('.vehicle-type-btn-create');

    typeButtonsCreate.forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-type');
            typeInputCreate.value = type;

            typeButtonsCreate.forEach(b => {
                b.classList.remove('bg-gray-800', 'text-white', 'border-gray-800');
                b.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
            });

            btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
            btn.classList.add('bg-gray-800', 'text-white', 'border-gray-800');
        });
    });

    // Dynamic models based on brand
    const marqueSelect = document.getElementById('marque_select');
    const modeleSelect = document.getElementById('modele_select');

    if (marqueSelect && modeleSelect) {
        marqueSelect.addEventListener('change', function() {
            const brand = this.value;
            modeleSelect.innerHTML = '<option value="">Chargement...</option>';

            if (!brand) {
                modeleSelect.innerHTML = '<option value="">Sélectionnez d\'abord une marque</option>';
                return;
            }

            fetch(`/api/models?brand=${encodeURIComponent(brand)}`)
                .then(response => response.json())
                .then(models => {
                    modeleSelect.innerHTML = '<option value="">Sélectionnez un modèle</option>';
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        modeleSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des modèles:', error);
                    modeleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        });
    }

    const imagesContainer = document.getElementById('images_container');
    const addImageBtn = document.getElementById('add_image_btn');
    const imagesPreview = document.getElementById('images_preview');

    let imageCount = 1;

    addImageBtn.addEventListener('click', () => {
        if (imageCount >= 5) {
            alert('Vous pouvez ajouter au maximum 5 photos.');
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
</script>
@endsection
