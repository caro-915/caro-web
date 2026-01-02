@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-1">Modifier mon annonce</h1>
        <p class="text-xs md:text-sm text-gray-500">Mettez à jour les informations de votre véhicule.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-sm bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="edit-annonce-form"
          method="POST"
          action="{{ route('annonces.update', $annonce) }}"
          enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow p-4 md:p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Véhicule neuf ? --}}
        <div>
            <p class="text-xs font-semibold mb-2">Véhicule neuf ? <span class="text-red-500">*</span></p>

            <div class="flex items-center gap-6 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="condition" value="non"
                           {{ old('condition', $annonce->condition) === 'non' ? 'checked' : '' }}>
                    <span>Non</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="condition" value="oui"
                           {{ old('condition', $annonce->condition) === 'oui' ? 'checked' : '' }}>
                    <span>Oui</span>
                </label>
            </div>

            @error('condition')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Titre + Prix --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1">Titre de l'annonce <span class="text-red-500">*</span></label>
                <input type="text"
                       name="titre"
                       value="{{ old('titre', $annonce->titre) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Prix (DA) <span class="text-red-500">*</span></label>
                <input type="number"
                       name="prix"
                       value="{{ old('prix', $annonce->prix) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
            </div>
        </div>

        {{-- Marque / Modèle / Ville --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Marque <span class="text-red-500">*</span></label>
                <select name="marque" id="marque_select" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez une marque</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->name }}" {{ old('marque', $annonce->marque) === $brand->name ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Modèle</label>
                <select name="modele" id="modele_select" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                    <option value="">Sélectionnez d'abord une marque</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Ville / Wilaya</label>
                <input type="text" name="ville" value="{{ old('ville', $annonce->ville) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
            </div>
        </div>

        {{-- Année / km / carburant / boite --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Année</label>
                <input type="number" name="annee" value="{{ old('annee', $annonce->annee) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Kilométrage (km)</label>
                <input type="number" name="kilometrage" value="{{ old('kilometrage', $annonce->kilometrage) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Carburant <span class="text-red-500">*</span></label>
                <select name="carburant" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm {{ $errors->has('carburant') ? 'border-red-500' : '' }}">
                    <option value="">Sélectionnez</option>
                    @foreach(['Essence','Diesel','Hybride','Électrique'] as $fuel)
                        <option value="{{ $fuel }}" {{ old('carburant', $annonce->carburant) === $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
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
                        <option value="{{ $gear }}" {{ old('boite_vitesse', $annonce->boite_vitesse) === $gear ? 'selected' : '' }}>{{ $gear }}</option>
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
                        @foreach(['Blanc','Noir','Gris','Argent','Bleu','Rouge','Vert','Beige','Orange','Marron'] as $c)
                            <option value="{{ $c }}" {{ old('couleur', $annonce->couleur) === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Document</label>
                    <select name="document_type" class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                        <option value="">— Choisir —</option>
                        <option value="carte_grise" {{ old('document_type', $annonce->document_type ?? '') === 'carte_grise' ? 'selected' : '' }}>Carte grise</option>
                        <option value="procuration" {{ old('document_type', $annonce->document_type ?? '') === 'procuration' ? 'selected' : '' }}>Procuration</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1">Finition</label>
                    <input type="text" name="finition" value="{{ old('finition', $annonce->finition ?? '') }}"
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm">
                </div>
            </div>
        </div>

        {{-- Affichage téléphone --}}
        <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold mb-2">Contact et affichage num</p>
            <label class="inline-flex items-start gap-2 text-xs md:text-sm text-gray-700">
                <input type="checkbox"
                       name="show_phone"
                       value="1"
                       class="mt-0.5 rounded border-gray-300 text-gray-800 focus:ring-gray-800"
                       @checked(old('show_phone', $annonce->show_phone))>
                <span>
                    Afficher mon numéro de téléphone sur l’annonce
                    <span class="block text-[11px] text-gray-400">
                        Si vous décochez, les acheteurs pourront uniquement vous envoyer des messages via la messagerie interne.
                    </span>
                </span>
            </label>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-xs font-semibold mb-1">Description</label>
            <textarea name="description" rows="5"
                      class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800">{{ old('description', $annonce->description) }}</textarea>
        </div>

        {{-- Photos --}}
        <div>
            <label class="block text-xs font-semibold mb-2">Photos actuelles</label>

            @php
                $slots = ['image_path','image_path_2','image_path_3','image_path_4','image_path_5'];
            @endphp

            @if(collect($slots)->filter(fn($s) => !empty($annonce->$s))->count())
                <div id="existing_images_grid" class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4 justify-center mx-auto max-w-3xl">
                    @foreach($slots as $imgSlot)
                        @if(!empty($annonce->$imgSlot))
                            <div class="relative annonce-image-block group w-40 h-24 overflow-hidden">
                                  <img src="{{ asset('storage/' . $annonce->$imgSlot) }}"
                                      alt="Photo véhicule"
                                      onerror="this.closest('.annonce-image-block')?.remove()"
                                     class="w-full h-full object-cover rounded-xl border border-gray-200">

                                <button type="button"
                                        title="Supprimer cette image"
                                        onclick="
                                          const block = this.closest('.annonce-image-block');
                                          if (!block) return;
                                          const hidden = block.querySelector('.delete-image-hidden');
                                          if (hidden) hidden.value = '1';
                                          block.classList.add('hidden');
                                        "
                                                 class="absolute top-1 right-1 z-20
                                                     w-5 h-5 rounded-full
                                                     bg-black/60 text-white
                                                     text-[10px] font-bold leading-none
                                                     flex items-center justify-center
                                                     shadow-md
                                                     hover:bg-black/70
                                                     transition-all duration-200">
                                    ✕
                                </button>

                                {{-- delete_images[slot]=0/1 --}}
                                <input type="hidden" name="delete_images[{{ $imgSlot }}]" value="0" class="delete-image-hidden">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <label class="block text-xs font-semibold mb-1">
                Ajouter des photos <span class="text-gray-400">(jusqu'à 5 photos au total)</span>
            </label>
            <p class="text-[11px] text-gray-500 mb-2">
                Formats acceptés : JPG, JPEG, PNG, WEBP. Taille max : 4 Mo par photo.
            </p>

            {{-- Conteneur des nouveaux inputs --}}
            <div id="images_container" class="space-y-2"></div>

            <button type="button" id="add_image_btn"
                    class="mt-2 px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                + Ajouter une photo
            </button>

            {{-- Preview des nouvelles images --}}
            <div id="images_preview" class="mt-3 grid grid-cols-2 md:grid-cols-5 gap-2 justify-center mx-auto max-w-3xl"></div>
        </div>

        {{-- Actions --}}
        <div class="pt-2 flex flex-col md:flex-row gap-3 md:justify-end">
            <a href="{{ route('annonces.my') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-gray-200 text-xs md:text-sm text-gray-600 hover:border-gray-300">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-gray-800 text-white text-xs md:text-sm font-semibold hover:bg-gray-900">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {

    const addBtn = document.getElementById('add_image_btn');
    const imagesContainer = document.getElementById('images_container');
    const imagesPreview = document.getElementById('images_preview');
    const MAX = 5;

    function remainingExistingCount() {
        const blocks = document.querySelectorAll('.annonce-image-block');
        let count = 0;
        blocks.forEach(block => {
            const hidden = block.querySelector('.delete-image-hidden');
            if (hidden && hidden.value === '0' && !block.classList.contains('hidden')) {
                count++;
            }
        });
        return count;
    }

    function newInputsCount() {
        return imagesContainer.querySelectorAll('input[type="file"]').length;
    }

    function canAddMore() {
        return (remainingExistingCount() + newInputsCount()) < MAX;
    }

    function updatePreview() {
        imagesPreview.innerHTML = '';
        const inputs = imagesContainer.querySelectorAll('input[type="file"]');

        inputs.forEach(input => {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative w-40 h-24 overflow-hidden rounded-xl border';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-full object-cover';

                    wrapper.appendChild(img);
                    imagesPreview.appendChild(wrapper);
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    }

    function addNewImageInput() {
        if (!canAddMore()) {
            alert('Maximum 5 photos au total.');
            return;
        }

        const group = document.createElement('div');
        group.className = 'image-input-group flex items-center gap-2';

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'images[]';
        input.accept = 'image/*';
        input.className = 'flex-1 text-xs file:bg-gray-50 file:text-gray-900 file:rounded-lg';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-red-600 font-bold';
        removeBtn.textContent = '✕';

        removeBtn.onclick = () => {
            group.remove();
            updatePreview();
        };

        input.onchange = () => {
            if (!input.files.length) {
                group.remove();
                return;
            }
            updatePreview();
        };

        group.appendChild(input);
        group.appendChild(removeBtn);
        imagesContainer.appendChild(group);

        input.click();
    }

    addBtn.addEventListener('click', addNewImageInput);
});
</script>

@endsection
