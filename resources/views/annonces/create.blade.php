@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-1">Déposer une annonce</h1>
        <p class="text-xs md:text-sm text-gray-500">
            Remplissez les informations de votre vehicule et ajoutez jusqu'à 4 photos.
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

        {{-- Type véhicule --}}
        <div>
            <label class="block text-xs font-semibold mb-2">Type de véhicule</label>
            <input type="hidden" name="vehicle_type" id="vehicle_type_input" value="{{ old('vehicle_type', 'car') }}">
            <div class="flex flex-wrap gap-2 text-xs">
                @php
                    $types = ['car' => 'Voiture', 'van' => 'Utilitaire', 'moto' => 'Moto'];
                    $currentType = old('vehicle_type', 'car');
                @endphp
                @foreach($types as $value => $label)
                    <button type="button"
                            data-type="{{ $value }}"
                            class="vehicle-type-btn-create px-3 py-1.5 rounded-full border
                                {{ $currentType === $value ? 'bg-pink-600 text-white border-pink-600' : 'bg-white text-gray-700 border-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Etat --}}
        <div>
            <label class="block text-xs font-semibold mb-2">État</label>
            <div class="flex gap-4 text-xs md:text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="condition" value="occasion"
                           {{ old('condition', 'occasion') === 'occasion' ? 'checked' : '' }}>
                    Occasion
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="condition" value="neuf"
                           {{ old('condition', 'occasion') === 'neuf' ? 'checked' : '' }}>
                    Neuf
                </label>
            </div>
        </div>

        {{-- Titre + prix --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1">Titre de l'annonce</label>
                <input type="text" name="titre" value="{{ old('titre') }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                       placeholder="ex : Renault Clio 1.5 DCI 2018 très bon état">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Prix (DA)</label>
                <input type="number" name="prix" value="{{ old('prix') }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                       placeholder="ex : 2500000">
            </div>
        </div>

        {{-- Marque / modèle / ville --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Marque</label>
                <select name="marque" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez une marque</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->name }}" {{ old('marque') === $brand->name ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Modèle</label>
                <select name="modele" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez un modèle</option>
                    @foreach($models as $model)
                        <option value="{{ $model->name }}" {{ old('modele') === $model->name ? 'selected' : '' }}>
                            {{ $model->name }} @if($model->brand) ({{ $model->brand->name }}) @endif
                        </option>
                    @endforeach
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
                <label class="block text-xs font-semibold mb-1">Carburant</label>
                <select name="carburant" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez</option>
                    @foreach(['Essence','Diesel','Hybride','Électrique'] as $fuel)
                        <option value="{{ $fuel }}" {{ old('carburant') === $fuel ? 'selected' : '' }}>
                            {{ $fuel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Boîte de vitesses</label>
                <select name="boite_vitesse" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez</option>
                    @foreach(['Manuelle','Automatique'] as $gear)
                        <option value="{{ $gear }}" {{ old('boite_vitesse') === $gear ? 'selected' : '' }}>
                            {{ $gear }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Détails --}}
        <div>
            <h2 class="text-sm md:text-base font-bold mb-3">Détails</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold mb-1">Couleur</label>
                    <input type="text" name="couleur" value="{{ old('couleur') }}"
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm"
                           placeholder="Ex : Blanc, Noir, Gris">
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
                       class="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                       {{ old('show_phone', 1) ? 'checked' : '' }}>
                <span>Afficher mon numéro de téléphone sur l’annonce</span>
            </label>
            <p class="mt-1 text-[11px] text-gray-400">
                Si vous décochez, les acheteurs pourront uniquement vous envoyer des messages via autoDZ.
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
                Photos du vehicule <span class="text-gray-400">(jusqu'à 4 photos)</span>
            </label>
            <p class="text-[11px] text-gray-500 mb-2">
                Formats acceptés : JPG, JPEG, PNG, WEBP. Taille max : 4 Mo par photo.
            </p>

            <input type="file"
                   name="images[]"
                   id="images_input"
                   accept="image/*"
                   multiple
                   class="block w-full text-xs md:text-sm text-gray-600
                          file:mr-3 file:py-2 file:px-4
                          file:rounded-full file:border-0
                          file:text-xs file:font-semibold
                          file:bg-pink-50 file:text-pink-700
                          hover:file:bg-pink-100">

            <div id="images_preview" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
        </div>

        {{-- Actions --}}
        <div class="pt-2 flex flex-col md:flex-row gap-3 md:justify-end">
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-gray-200 text-xs md:text-sm text-gray-600 hover:border-gray-300">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-pink-600 text-white text-xs md:text-sm font-semibold hover:bg-pink-700">
                Publier l'annonce
            </button>
        </div>

    </form>
</div>

<script>
    const typeInputCreate = document.getElementById('vehicle_type_input');
    const typeButtonsCreate = document.querySelectorAll('.vehicle-type-btn-create');

    typeButtonsCreate.forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.getAttribute('data-type');
            typeInputCreate.value = type;

            typeButtonsCreate.forEach(b => {
                b.classList.remove('bg-pink-600', 'text-white', 'border-pink-600');
                b.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
            });

            btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
            btn.classList.add('bg-pink-600', 'text-white', 'border-pink-600');
        });
    });

    const imagesInput = document.getElementById('images_input');
    const imagesPreview = document.getElementById('images_preview');

    if (imagesInput) {
        imagesInput.addEventListener('change', function () {
            const files = Array.from(this.files);

            if (files.length > 4) {
                alert('Vous pouvez télécharger au maximum 4 photos.');
                const dataTransfer = new DataTransfer();
                files.slice(0, 4).forEach(file => dataTransfer.items.add(file));
                this.files = dataTransfer.files;
            }

            imagesPreview.innerHTML = '';

            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-24 object-cover rounded-xl border border-gray-200';
                    imagesPreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
</script>
@endsection
