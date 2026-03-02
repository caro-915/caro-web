@extends('layouts.app')

@section('seo_title', 'Contactez-nous - ElSayara')
@section('seo_description', 'Besoin d\'aide ou une question ? Contactez l\'équipe ElSayara via notre formulaire de contact. Nous répondons dans les plus brefs délais.')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Contactez-nous</h1>
        <p class="mt-2 text-gray-600">Une question, une suggestion ou besoin d'aide ? Envoyez-nous un message.</p>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 flex items-start gap-3">
            <span class="text-green-500 text-xl">✓</span>
            <div>
                <p class="font-semibold">Message envoyé !</p>
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
        <form action="{{ route('contact.send') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Honeypot anti-spam (caché) --}}
            <div style="position: absolute; left: -9999px;">
                <label for="website">Ne pas remplir si vous êtes humain</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', auth()->user()->name ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-transparent transition @error('name') border-red-500 @enderror"
                       placeholder="Votre nom"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Adresse email <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', auth()->user()->email ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-transparent transition @error('email') border-red-500 @enderror"
                       placeholder="votre@email.com"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Téléphone (optionnel) --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    Téléphone <span class="text-gray-400 text-xs">(optionnel)</span>
                </label>
                <input type="tel" 
                       name="phone" 
                       id="phone" 
                       value="{{ old('phone', auth()->user()->phone ?? '') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-transparent transition @error('phone') border-red-500 @enderror"
                       placeholder="05 XX XX XX XX">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sujet --}}
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                    Sujet <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="subject" 
                       id="subject" 
                       value="{{ old('subject') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-transparent transition @error('subject') border-red-500 @enderror"
                       placeholder="Ex: Question sur une annonce, Problème technique..."
                       required>
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message --}}
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                    Message <span class="text-red-500">*</span>
                </label>
                <textarea name="message" 
                          id="message" 
                          rows="6"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-gray-800 focus:border-transparent transition resize-none @error('message') border-red-500 @enderror"
                          placeholder="Décrivez votre demande en détail..."
                          required>{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-400">Maximum 3000 caractères</p>
            </div>

            {{-- Bouton submit --}}
            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-gray-800 text-white font-semibold py-3 px-6 rounded-xl hover:bg-gray-900 transition flex items-center justify-center gap-2">
                    <span>📨</span>
                    Envoyer le message
                </button>
            </div>
        </form>
    </div>

    {{-- Infos supplémentaires --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📧</span>
                <div>
                    <p class="font-semibold text-gray-900">Email direct</p>
                    <a href="mailto:contact@elsayara.com" class="text-sm text-gray-600 hover:text-gray-800">
                        contact@elsayara.com
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⏱️</span>
                <div>
                    <p class="font-semibold text-gray-900">Temps de réponse</p>
                    <p class="text-sm text-gray-600">Généralement sous 24-48h</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
