@extends('layouts.app')

@section('content')


    <div class="max-w-6xl mx-auto px-4 py-10 md:py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">

            {{-- COLONNE GAUCHE : carte Connexion / Inscription --}}
            <div class="bg-white rounded-3xl shadow-lg px-6 py-7 md:px-8 md:py-8 flex flex-col">
                {{-- Onglets Connexion / Inscription --}}
                <div class="flex mb-6 border-b border-gray-100">
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center text-sm font-semibold py-2 border-b-2
                              border-pink-600 text-pink-600">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                       class="flex-1 text-center text-sm font-semibold py-2 border-b-2
                              border-transparent text-gray-400 hover:text-gray-600">
                        Inscription
                    </a>
                </div>

                {{-- Titre / texte d'intro --}}
                <div class="mb-5">
                    <h1 class="text-2xl md:text-3xl font-bold mb-1">Bienvenue</h1>
                    <p class="text-xs md:text-sm text-gray-500">
                        Inscrivez-vous ou connectez-vous pour profiter de toutes les fonctionnalités de Caro.
                    </p>
                </div>

                {{-- Messages statut + erreurs --}}
                @if (session('status'))
                    <div class="mb-4 text-xs md:text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

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

                {{-- Bouton Google uniquement --}}
                <div class="space-y-2 mb-4">
                    <a href="{{ route('auth.google') }}"
                       class="w-full border border-gray-200 rounded-full py-2.5 flex items-center justify-center gap-2 hover:bg-gray-50 text-sm font-medium">
                        <span class="text-lg">G</span>
                        <span>Google</span>
                    </a>

                    <div class="flex items-center gap-3 text-[11px] text-gray-400">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span>ou</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>
                </div>

                {{-- FORMULAIRE DE CONNEXION --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4 flex-1 flex flex-col" id="loginForm">
                    @csrf
                    <input type="hidden" name="redirect_to" id="redirect_to" value="">

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold mb-1">Adresse e-mail</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label for="password" class="block text-xs font-semibold mb-1">Mot de passe</label>
                        <input id="password" type="password" name="password" required
                               class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    </div>

                    {{-- Se souvenir + mot de passe oublié --}}
                    <div class="flex items-center justify-between text-[11px] md:text-xs text-gray-600">
                        <label class="inline-flex items-center gap-1">
                            <input type="checkbox" name="remember"
                                   class="rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                            <span>Se souvenir de moi</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-pink-600 hover:text-pink-700 font-medium">
                                Mot de passe oublié
                            </a>
                        @endif
                    </div>

                    {{-- Bouton principal --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2
                                       rounded-full bg-pink-600 text-white text-sm font-semibold
                                       hover:bg-pink-700">
                            Se connecter
                        </button>
                    </div>

                    {{-- Lien inscription (mobile principalement) --}}
                    @if (Route::has('register'))
                        <p class="mt-3 text-[11px] md:text-xs text-gray-500 text-center">
                            Pas encore de compte ?
                            <a href="{{ route('register') }}" class="text-pink-600 hover:text-pink-700 font-semibold">
                                Créer un compte
                            </a>
                        </p>
                    @endif
                </form>
            </div>

            {{-- COLONNE DROITE : image pleine hauteur --}}
            
<div class="hidden md:flex items-center">
    <div class="w-full rounded-3xl overflow-hidden shadow-lg">
        <img
            src="{{ asset('images/auth-hero.png') }}"
            alt="Caro"
            class="w-full h-80 md:h-[520px] object-cover"
        >
    </div>
</div>

        </div>
    </div>

    <script>
        // Check if there's a redirect URL stored in sessionStorage
        document.addEventListener('DOMContentLoaded', function() {
            const redirectUrl = sessionStorage.getItem('redirectAfterLogin');
            if (redirectUrl) {
                document.getElementById('redirect_to').value = redirectUrl;
                // Clear the stored URL after retrieving it
                sessionStorage.removeItem('redirectAfterLogin');
            }
        });
    </script>
@endsection

