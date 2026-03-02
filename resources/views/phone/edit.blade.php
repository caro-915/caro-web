@extends('layouts.app')

@section('seo_title', 'Ajouter mon numéro de téléphone - ElSayara')
@section('seo_description', 'Complétez votre profil ElSayara en ajoutant votre numéro de téléphone pour accéder à toutes les fonctionnalités.')
@section('seo_robots', 'noindex, nofollow')

@section('content')
<div class="max-w-lg mx-auto py-8">
    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl">📱</span>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">Validez votre compte</h1>
        <p class="mt-2 text-gray-600">Ajoutez votre numéro de téléphone mobile pour accéder à toutes les fonctionnalités d'ElSayara.</p>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 flex items-start gap-3">
            <span class="text-green-500 text-xl">✓</span>
            <div>
                <p class="font-semibold">Numéro enregistré !</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 flex items-start gap-3">
            <span class="text-red-500 text-xl">✕</span>
            <div>
                <p class="font-semibold">Erreur</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Formulaire --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <form action="{{ route('phone.update') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Affichage du compte actuel --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <p class="text-sm text-gray-500">Connecté en tant que</p>
                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                <p class="text-sm text-gray-600">{{ $user->email }}</p>
            </div>

            {{-- Numéro de téléphone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    Numéro de téléphone mobile <span class="text-red-500">*</span>
                </label>
                <input type="tel" 
                       name="phone" 
                       id="phone" 
                       value="{{ old('phone', $user->phone) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-lg @error('phone') border-red-500 @enderror"
                       placeholder="05 XX XX XX XX"
                       pattern="[0-9\s\+\-\.]+"
                       required
                       autofocus>
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Format accepté: numéro algérien (05XX, 06XX, 07XX) ou international (+213...)
                </p>
            </div>

            {{-- Avantages --}}
            <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
                <p class="font-semibold text-orange-800 mb-2">✨ Pourquoi ajouter mon téléphone ?</p>
                <ul class="text-sm text-orange-700 space-y-1">
                    <li>• Les acheteurs peuvent vous contacter directement</li>
                    <li>• Vos annonces inspirent plus confiance</li>
                    <li>• Accès à toutes les fonctionnalités de messagerie</li>
                </ul>
            </div>

            {{-- Boutons --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-orange-500 text-white font-semibold py-3 px-6 rounded-xl hover:bg-orange-600 transition flex items-center justify-center gap-2">
                    <span>✓</span>
                    Valider mon numéro
                </button>
                
                <a href="{{ route('home') }}"
                   class="flex-1 bg-gray-100 text-gray-700 font-semibold py-3 px-6 rounded-xl hover:bg-gray-200 transition text-center">
                    Plus tard
                </a>
            </div>
        </form>
    </div>

    {{-- Note de confidentialité --}}
    <p class="mt-6 text-center text-xs text-gray-500">
        🔒 Votre numéro est protégé et ne sera visible que sur vos annonces si vous l'autorisez.
    </p>
</div>
@endsection
