@extends('layouts.app')

@section('content')

    <div class="max-w-6xl mx-auto px-4 py-10 md:py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">

            {{-- COLONNE GAUCHE : carte Connexion / Inscription --}}
            <div class="bg-white rounded-3xl shadow-lg px-6 py-7 md:px-8 md:py-8 flex flex-col">
                {{-- Onglets --}}
                <div class="flex mb-6 border-b border-gray-100">
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center text-sm font-semibold py-2 border-b-2
                              border-transparent text-gray-400 hover:text-gray-600">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                       class="flex-1 text-center text-sm font-semibold py-2 border-b-2
                              border-pink-600 text-pink-600">
                        Inscription
                    </a>
                </div>

                {{-- Titre / texte d'intro --}}
                <div class="mb-5">
                    <h1 class="text-2xl md:text-3xl font-bold mb-1">Créer un compte</h1>
                    <p class="text-xs md:text-sm text-gray-500">
                        En créant votre compte Caro, vous pourrez déposer et gérer vos annonces facilement.
                    </p>
                </div>

                {{-- Erreurs de validation --}}
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

                {{-- FORMULAIRE D'INSCRIPTION --}}
                <form method="POST" action="{{ route('register') }}" class="space-y-4 flex-1 flex flex-col">
                    @csrf

                    {{-- Nom --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold mb-1">Nom / PrÃ©nom</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                               class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    </div>
                    {{-- phone --}}
                    <div class="mt-4">
    <label for="phone" class="block text-xs font-semibold mb-1">TÃ©lÃ©phone</label>
    <input id="phone" name="phone" type="text"
           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                  focus:outline-none focus:ring-2 focus:ring-pink-500"
           value="{{ old('phone') }}">
</div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold mb-1">Adresse e-mail</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
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

                    {{-- Confirmation --}}
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold mb-1">
                            Confirmer le mot de passe
                        </label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                               class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    </div>

                    {{-- (optionnel) CGU --}}
                    <div class="text-[11px] md:text-xs text-gray-600 flex items-start gap-2">
                        <input type="checkbox" required
                               class="mt-0.5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        <span>
                            J'accepte les conditions d'utilisation de Caro.
                        </span>
                    </div>

                    {{-- Bouton principal --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2
                                       rounded-full bg-pink-600 text-white text-sm font-semibold
                                       hover:bg-pink-700">
                            CrÃ©er mon compte
                        </button>
                    </div>

                    {{-- Lien Connexion --}}
                    <p class="mt-3 text-[11px] md:text-xs text-gray-500 text-center">
                        Vous avez dÃ©jÃ  un compte ?
                        <a href="{{ route('login') }}" class="text-pink-600 hover:text-pink-700 font-semibold">
                            Se connecter
                        </a>
                    </p>
                </form>
            </div>

            {{-- COLONNE DROITE : image --}}
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
@endsection

