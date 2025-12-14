@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    {{-- Titre page --}}
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-1">
            Modifier mon annonce
        </h1>
        <p class="text-xs md:text-sm text-gray-500">
            Mettez à jour les informations de votre véhicule.
        </p>
    </div>

    {{-- Messages --}}
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

    {{-- FORMULAIRE --}}
    <form method="POST"
          action="{{ route('annonces.update', $annonce) }}"
          class="bg-white rounded-2xl shadow p-4 md:p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Titre + Prix --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold mb-1">Titre de l'annonce</label>
                <input type="text"
                       name="titre"
                       value="{{ old('titre', $annonce->titre) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">Prix (DA)</label>
                <input type="number"
                       name="prix"
                       value="{{ old('prix', $annonce->prix) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
        </div>

        {{-- Marque / Modèle / Ville --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Marque</label>
                <select name="marque"
                        class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Sélectionnez une marque</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->name }}"
                            @selected(old('marque', $annonce->marque) === $brand->name)>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- documents, couleur, finition --}}

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    {{-- Couleur --}}
    <div>
        <label class="block text-sm font-semibold mb-2">Couleur</label>
        <input type="text" name="couleur"
               value="{{ old('couleur', $annonce->couleur ?? '') }}"
               class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-pink-500 focus:border-pink-500"
               placeholder="Ex: Blanc, Noir, Gris..." />
    </div>

    {{-- Document --}}
    <div>
        <label class="block text-sm font-semibold mb-2">Document</label>
        <select name="document_type"
                class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-pink-500 focus:border-pink-500">
            <option value="">— Choisir —</option>
            <option value="carte_grise" {{ old('document_type', $annonce->document_type ?? '') === 'carte_grise' ? 'selected' : '' }}>Carte grise</option>
            <option value="procuration" {{ old('document_type', $annonce->document_type ?? '') === 'procuration' ? 'selected' : '' }}>Procuration</option>
        </select>
    </div>

    {{-- Finition --}}
    <div>
        <label class="block text-sm font-semibold mb-2">Finition</label>
        <input type="text" name="finition"
               value="{{ old('finition', $annonce->finition ?? '') }}"
               class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-pink-500 focus:border-pink-500"
               placeholder="Ex: Allure, GT Line, Titanium" />
    </div>
</div>



            <div>
                <label class="block text-xs font-semibold mb-1">Modèle</label>
                <select name="modele"
                        class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Sélectionnez un modèle</option>
                    @foreach($models as $model)
                        <option value="{{ $model->name }}"
                            @selected(old('modele', $annonce->modele) === $model->name)>
                            {{ $model->name }} @if($model->brand) ({{ $model->brand->name }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Ville / Wilaya</label>
                <input type="text"
                       name="ville"
                       value="{{ old('ville', $annonce->ville) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>
        </div>

        {{-- Année / Kilométrage / Carburant / Boîte --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold mb-1">Année</label>
                <input type="number"
                       name="annee"
                       value="{{ old('annee', $annonce->annee) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Kilométrage (km)</label>
                <input type="number"
                       name="kilometrage"
                       value="{{ old('kilometrage', $annonce->kilometrage) }}"
                       class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Carburant</label>
                <select name="carburant"
                        class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Sélectionnez</option>
                    @foreach(['Essence','Diesel','Hybride','Électrique'] as $fuel)
                        <option value="{{ $fuel }}"
                            @selected(old('carburant', $annonce->carburant) === $fuel)>
                            {{ $fuel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Boîte de vitesses</label>
                <select name="boite_vitesse"
                        class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="">Sélectionnez</option>
                    @foreach(['Manuelle','Automatique'] as $gear)
                        <option value="{{ $gear }}"
                            @selected(old('boite_vitesse', $annonce->boite_vitesse) === $gear)>
                            {{ $gear }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Type de véhicule --}}
        <div>
            <label class="block text-xs font-semibold mb-1">Type de véhicule</label>
            <input type="text"
                   name="vehicle_type"
                   value="{{ old('vehicle_type', $annonce->vehicle_type) }}"
                   class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
        </div>

        {{-- Affichage téléphone --}}
        <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold mb-2">{Contact et affichage num}</p>
            <label class="inline-flex items-start gap-2 text-xs md:text-sm text-gray-700">
                <input type="checkbox"
                       name="show_phone"
                       value="1"
                       class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500"
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
            <textarea name="description"
                      rows="5"
                      class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">{{ old('description', $annonce->description) }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="pt-2 flex flex-col md:flex-row gap-3 md:justify-end">
            <a href="{{ route('annonces.my') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-gray-200 text-xs md:text-sm text-gray-600 hover:border-gray-300">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-full bg-pink-600 text-white text-xs md:text-sm font-semibold hover:bg-pink-700">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection
