@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- Formulaire de réinitialisation --}}
        <div class="bg-white rounded-3xl shadow-lg px-6 py-7 md:px-8 md:py-8">
            
            {{-- Logo Laravel --}}
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.31 0-6-2.69-6-6V9.33l6-3 6 3V14c0 3.31-2.69 6-6 6z"/>
                    </svg>
                </div>
            </div>

            {{-- Titre --}}
            <div class="mb-6 text-center">
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Réinitialiser le mot de passe</h1>
                <p class="text-xs md:text-sm text-gray-500">
                    Choisissez un nouveau mot de passe sécurisé
                </p>
            </div>

            {{-- Messages d'erreur --}}
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

            {{-- FORMULAIRE --}}
            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf

                {{-- Token caché --}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- Email (lecture seule) --}}
                <div>
                    <label for="email" class="block text-xs font-semibold mb-1">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" 
                           required autofocus autocomplete="username"
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm bg-gray-50
                                  focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800"
                           readonly>
                </div>

                {{-- Nouveau mot de passe --}}
                <div>
                    <label for="password" class="block text-xs font-semibold mb-1">Mot de passe</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                  focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800"
                           placeholder="••••••••">
                </div>

                {{-- Confirmation mot de passe --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold mb-1">Confirmez le mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" 
                           required autocomplete="new-password"
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                  focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800"
                           placeholder="••••••••">
                </div>

                {{-- Bouton --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-gray-800 text-white font-semibold py-2.5 rounded-full hover:bg-gray-900 text-sm">
                        Réinitialiser le mot de passe
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection
