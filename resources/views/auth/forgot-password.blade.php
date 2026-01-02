@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        {{-- Formulaire mot de passe oublié --}}
        <div class="bg-white rounded-3xl shadow-lg px-6 py-7 md:px-8 md:py-8 flex flex-col">
            
            {{-- Titre --}}
            <div class="mb-5">
                <h1 class="text-2xl md:text-3xl font-bold mb-1">Mot de passe oublié ?</h1>
                <p class="text-xs md:text-sm text-gray-500">
                    Pas de souci. Indiquez-nous simplement votre adresse e-mail et nous vous enverrons un lien de réinitialisation de mot de passe qui vous permettra d'en choisir un nouveau.
                </p>
            </div>

            {{-- Message de succès --}}
            @if (session('status'))
                <div class="mb-4 text-xs md:text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
                    <p class="font-semibold mb-2">✅ Nous vous avons envoyé par courriel le lien de réinitialisation de votre mot de passe !</p>
                    
                    @if(config('mail.default') === 'log')
                        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-yellow-800 font-semibold mb-1">⚠️ Mode développement :</p>
                            <p class="text-yellow-700 text-xs">
                                Le mail n'est pas envoyé réellement. Le lien de réinitialisation se trouve dans : 
                                <code class="bg-yellow-100 px-1 py-0.5 rounded">storage/logs/laravel.log</code>
                            </p>
                            <p class="text-yellow-700 text-xs mt-1">
                                Recherchez "reset-password" dans le fichier de log pour trouver votre lien.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

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
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4 flex-1 flex flex-col">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold mb-1">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border rounded-lg px-3 py-2 text-xs md:text-sm
                                  focus:outline-none focus:ring-2 focus:ring-gray-800 focus:border-gray-800"
                           placeholder="votre@email.com">
                </div>

                {{-- Bouton envoi --}}
                <div class="mt-auto">
                    <button type="submit"
                            class="w-full bg-gray-800 text-white font-semibold py-2.5 rounded-full hover:bg-gray-900 text-sm">
                        Envoyer le lien de réinitialisation
                    </button>
                </div>

                {{-- Retour connexion --}}
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-gray-800">
                        ← Retour à la connexion
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
