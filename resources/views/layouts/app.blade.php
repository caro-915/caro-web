<!DOCTYPE html>
<html lang="fr">
    <style>
  html { scroll-behavior: smooth; overflow-y: scroll; }
</style>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Caro – Trouvez votre voiture d'occasion</title>

    {{-- Tailwind + JS compilés par Vite (Breeze) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>[x-cloak]{ display:none !important; }</style>
    
</head>
<body class="bg-gray-50 text-slate-900">

    {{-- HEADER Caro --}}
    <header class="bg-white shadow-sm">
        <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 min-w-0">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Caro" class="h-10 w-auto">
            </a>

            {{-- Nav --}}
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('annonces.search') }}" class="hover:text-gray-800">Occasion</a>
                <a href="#" class="hover:text-gray-800">Top deals</a>
                <a href="{{ route('home') }}#about" class="hover:text-gray-800">À propos de nous</a>
                <a href="{{ route('home') }}#contact-us" class="hover:text-gray-800">Nous contacter</a>
                <a href="#" class="hover:text-gray-800">Conseils</a>
            </nav>

            {{-- Actions droite --}}
            <div class="flex items-center space-x-3">

                @auth
                    {{-- Calcul du nombre de messages non lus --}}
                    @php
                        $unreadCount = \App\Models\Message::whereHas('conversation', function ($q) {
                                $q->where('buyer_id', auth()->id())
                                  ->orWhere('seller_id', auth()->id());
                            })
                            ->whereNull('read_at')
                            ->where('sender_id', '!=', auth()->id())
                            ->count();
                    @endphp

                    {{-- Bouton Déposer mon annonce --}}
                    <a href="{{ route('annonces.create') }}"
                       class="bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-full hover:bg-gray-900 whitespace-nowrap">
                        Déposer mon annonce
                    </a>

                    {{-- Icône favoris --}}
                    <a href="{{ route('favorites.index') }}"
                       class="flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 hover:bg-gray-50"
                       title="Mes favoris">
                        <span class="text-gray-800 text-lg">♥</span>
                    </a>

                    {{-- Menu utilisateur --}}
                    <div class="relative" x-data="{ open:false }">
                        <button
                            @click="open = !open"
                            class="flex items-center gap-2 px-3 py-2 text-xs md:text-sm font-semibold border border-gray-200 rounded-full hover:bg-gray-50 whitespace-nowrap"
                        >
                            <span>👤 {{ auth()->user()->name }}</span>

                            @if($unreadCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full bg-gray-800 text-white text-[11px]">
                                    {{ $unreadCount }}
                                </span>
                            @endif

                            <svg class="w-3 h-3" viewBox="0 0 10 6" fill="none">
                                <path d="M1 1L5 5L9 1"
                                      stroke="currentColor"
                                      stroke-width="1.4"
                                      stroke-linecap="round"
                                      stroke-linejoin="round" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div
                            x-cloak
                            x-show="open"
                            @click.outside="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 text-sm z-50"
                        >
                            <a href="{{ route('messages.index') }}"
                               class="flex items-center justify-between px-4 py-2 hover:bg-gray-100">
                                <span>Mes messages</span>
                                <span id="unread-badge"
                                    class="inline-flex items-center justify-center min-w-[18px] h-[18px] rounded-full bg-gray-800 text-white text-[11px]
                                            {{ $unreadCount == 0 ? 'hidden' : '' }}">
                                             {{ $unreadCount }}
                                </span>

                            </a>

                            <a href="{{ route('annonces.my') }}"
                               class="block px-4 py-2 hover:bg-gray-100">
                                Mes annonces
                            </a>

                            <a href="{{ route('dashboard') }}"
                               class="block px-4 py-2 hover:bg-gray-100">
                                Tableau de bord
                            </a>
                            @if(auth()->user()->is_admin)
    <a href="{{ route('admin.dashboard') }}"
       class="block px-4 py-2 hover:bg-gray-100 text-gray-800 font-semibold">
        Tableau de bord admin
    </a>
@endif

                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 hover:bg-gray-100">
                                Gérer mon profil
                            </a>

                            {{-- Lien "Se déconnecter" --}}
                            <a href="{{ route('logout') }}"
                               class="block px-4 py-2 hover:bg-gray-100 text-red-500"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Se déconnecter
                            </a>

                            {{-- Formulaire POST caché pour le logout --}}
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Utilisateur non connecté --}}
                    <a href="{{ route('login') }}"
                       class="text-xs md:text-sm text-gray-700 hover:text-gray-800">
                        Se connecter
                    </a>
                    <a href="{{ route('register') }}"
                       class="hidden sm:inline-flex items-center justify-center text-xs md:text-sm text-gray-700 hover:text-gray-800">
                        S'inscrire
                    </a>
                    <a href="{{ route('annonces.create') }}"
                       class="bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-full hover:bg-gray-900 whitespace-nowrap">
                        Déposer mon annonce
                    </a>
                @endauth

            </div>
        </div>
    </header>

    {{-- CONTENU PAGE --}}
   <main class="py-6 md:py-8">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>
</main>


    {{-- ✅ Scripts poussés depuis les vues (ex: polling messages) --}}
    @stack('scripts')

    <script>
setInterval(() => {
    fetch('{{ route('messages.unread-count') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('unread-badge');
        if (!badge) return;

        if (data.count > 0) {
            badge.textContent = data.count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
}, 20000); // toutes les 20 secondes
</script>


</body>
</html>
