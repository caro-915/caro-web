<!DOCTYPE html>
<html lang="fr">
    <style>
  html { scroll-behavior: smooth; overflow-y: scroll; }
</style>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO: Dynamic title and meta --}}
    <title>@yield('seo_title', 'ElSayara – Trouvez votre voiture d\'occasion en Algérie')</title>
    <meta name="description" content="@yield('seo_description', 'ElSayara est la plateforme leader pour acheter et vendre des véhicules d\'occasion en Algérie. Des milliers d\'annonces vérifiées de voitures, motos et utilitaires.')">
    <meta name="robots" content="@yield('seo_robots', 'index, follow')">
    <link rel="canonical" href="@yield('seo_canonical', url()->current())">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('seo_title', 'ElSayara – Trouvez votre voiture d\'occasion en Algérie')">
    <meta property="og:description" content="@yield('seo_description', 'ElSayara est la plateforme leader pour acheter et vendre des véhicules d\'occasion en Algérie.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('seo_canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:locale" content="fr_DZ">
    <meta property="og:site_name" content="ElSayara">

    {{-- Additional SEO stack for JSON-LD etc --}}
    @stack('seo')

    {{-- Tailwind + JS compilés par Vite (Breeze) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{ display:none !important; }</style>
    
</head>
<body class="bg-gray-50 text-slate-900 overflow-x-hidden">

    {{-- HEADER ElSayara --}}
    <header class="bg-white shadow-sm fixed w-full top-0 left-0 right-0 z-50">
        <div class="w-full max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 flex items-center justify-between h-14 sm:h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center flex-shrink-0">
                <span class="text-2xl sm:text-3xl font-black bg-gradient-to-r from-gray-800 via-gray-900 to-slate-800 bg-clip-text text-transparent tracking-wider drop-shadow-sm">ELSAYARA</span>
            </a>

            {{-- Nav --}}
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <a href="{{ route('annonces.search') }}" class="hover:text-gray-800 whitespace-nowrap">Annonces</a>
                <a href="{{ route('home') }}#top-annonces" class="hover:text-gray-800 whitespace-nowrap">Top annonces</a>
                <a href="{{ route('home') }}#about" class="hover:text-gray-800 whitespace-nowrap">À propos de nous</a>
                <a href="{{ route('contact.show') }}" class="hover:text-gray-800 whitespace-nowrap">Nous contacter</a>
            </nav>

            {{-- Actions droite --}}
            <div class="flex items-center gap-1 sm:gap-3 flex-shrink-0">

                @auth
                    {{-- Calcul du nombre de messages non lus --}}
                    @php
                        $subscriptionServiceHeader = app(\App\Services\SubscriptionService::class);
                        $activeSubscriptionHeader = $subscriptionServiceHeader->getActiveSubscription(auth()->user());
                        $planIconHeader = null;
                        if ($activeSubscriptionHeader && $activeSubscriptionHeader->plan) {
                            $planNameLower = strtolower((string) $activeSubscriptionHeader->plan->name);
                            if (str_contains($planNameLower, 'premium')) {
                                $planIconHeader = '⚡';
                            } elseif (str_contains($planNameLower, 'pro')) {
                                $planIconHeader = '👑';
                            }
                        }

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
                       class="hidden sm:inline-flex bg-gray-800 text-white text-xs sm:text-sm font-semibold px-2 sm:px-4 py-1.5 sm:py-2 rounded-full hover:bg-gray-900 whitespace-nowrap">
                        Déposer mon annonce
                    </a>

                    {{-- Icône favoris --}}
                    <a href="{{ route('favorites.index') }}"
                       class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-pink-200 hover:bg-pink-50 flex-shrink-0"
                       title="Mes favoris">
                        <span class="text-pink-600 text-base sm:text-lg">♥</span>
                    </a>

                    {{-- Menu utilisateur --}}
                    <div class="relative flex-shrink-0" x-data="{ open:false }">
                        <button
                            @click="open = !open"
                            class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 text-[10px] sm:text-xs md:text-sm font-semibold border border-gray-200 rounded-full hover:bg-gray-50"
                        >
                            <span class="hidden sm:inline">👤</span>
                            @if($planIconHeader)
                                <span class="text-base sm:text-lg">{{ $planIconHeader }}</span>
                            @endif
                            <span class="truncate max-w-[80px] sm:max-w-none">{{ auth()->user()->name }}</span>

                            @if($unreadCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[16px] sm:min-w-[18px] h-[16px] sm:h-[18px] rounded-full bg-pink-600 text-white text-[10px] sm:text-[11px]">
                                    {{ $unreadCount }}
                                </span>
                            @endif

                            <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 flex-shrink-0" viewBox="0 0 10 6" fill="none">
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

                            <a href="{{ route('search.history') }}"
                               class="block px-4 py-2 hover:bg-gray-100">
                                Historique de recherche
                            </a>

                            <a href="{{ route('search.alert.results') }}"
                               class="block px-4 py-2 hover:bg-gray-100">
                                Résultats alertes
                                @php
                                    $alertCount = \App\Models\SearchAlert::where('user_id', auth()->id())
                                        ->where('is_active', true)
                                        ->count();
                                @endphp
                                @if($alertCount > 0)
                                    <span class="inline-block bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full ml-2">{{ $alertCount }}</span>
                                @endif
                                🔔
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
                       class="text-[10px] sm:text-xs md:text-sm text-gray-700 hover:text-gray-800 whitespace-nowrap">
                        Se connecter
                    </a>
                    <a href="{{ route('register') }}"
                       class="hidden sm:inline-flex items-center justify-center text-xs md:text-sm text-gray-700 hover:text-gray-800 whitespace-nowrap">
                        S'inscrire
                    </a>
                    <a href="{{ route('annonces.create') }}"
                       class="bg-gray-800 text-white text-[10px] sm:text-sm font-semibold px-2 sm:px-4 py-1.5 sm:py-2 rounded-full hover:bg-gray-900 whitespace-nowrap">
                        Déposer
                    </a>
                @endauth

            </div>
        </div>
    </header>

    {{-- BANNIÈRE VALIDATION TÉLÉPHONE (Google login sans numéro) --}}
    @auth
        @if(auth()->user()->google_id && empty(auth()->user()->phone))
            <div class="bg-orange-50 border-b border-orange-200 fixed w-full top-14 sm:top-16 left-0 right-0 z-40">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-3">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-4">
                        <div class="flex items-center gap-3">
                            <span class="hidden sm:inline-flex items-center justify-center w-8 h-8 bg-orange-100 rounded-full text-orange-600">
                                👤
                            </span>
                            <p class="text-xs sm:text-sm text-orange-800">
                                <span class="font-semibold">Validez votre compte</span> en ajoutant un numéro de téléphone mobile afin de bénéficier de toutes les fonctionnalités d'ElSayara
                            </p>
                        </div>
                        <a href="{{ route('phone.edit') }}"
                           class="flex-shrink-0 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold px-4 py-1.5 rounded-full transition">
                            VALIDER
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    {{-- CONTENU PAGE --}}
    @php
        $showPhoneBanner = auth()->check() && auth()->user()->google_id && empty(auth()->user()->phone);
    @endphp
   <main class="{{ $showPhoneBanner ? 'pt-28 sm:pt-32' : 'pt-16 sm:pt-18' }} py-6 md:py-8">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-screen">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>
</main>

    {{-- FOOTER --}}
    <footer class="bg-gray-900 text-gray-300 text-xs md:text-sm mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                {{-- Branding --}}
                <div>
                    <a href="{{ route('home') }}" class="text-xl font-black text-white tracking-wider">ELSAYARA</a>
                    <p class="text-[11px] md:text-xs leading-relaxed mt-2 text-gray-400">
                        La plateforme de référence pour l'achat et la vente de véhicules d'occasion en Algérie.
                    </p>
                </div>

                {{-- Navigation rapide --}}
                <div>
                    <h3 class="font-semibold text-white mb-3">Navigation</h3>
                    <ul class="space-y-1">
                        <li><a href="{{ route('annonces.search') }}" class="hover:text-white transition">Rechercher</a></li>
                        <li><a href="{{ route('annonces.create') }}" class="hover:text-white transition">Déposer une annonce</a></li>
                        <li><a href="{{ route('home') }}#top-annonces" class="hover:text-white transition">Top annonces</a></li>
                        <li><a href="{{ route('home') }}#about" class="hover:text-white transition">À propos</a></li>
                    </ul>
                </div>

                {{-- Legal links --}}
                <div>
                    <h3 class="font-semibold text-white mb-3">Informations</h3>
                    <ul class="space-y-1">
                        <li><a href="#" class="hover:text-white transition">Conditions générales</a></li>
                        <li><a href="#" class="hover:text-white transition">Politique de confidentialité</a></li>
                        <li><a href="#" class="hover:text-white transition">Mentions légales</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="font-semibold text-white mb-3">Contact</h3>
                    <ul class="space-y-1 text-[11px] md:text-xs">
                        <li>
                            <a href="{{ route('contact.show') }}" class="hover:text-white transition">
                                📧 {{ config('autodz.contact_email', 'contact@elsayara.com') }}
                            </a>
                        </li>
                        <li>📍 Algérie</li>
                        <li>
                            <a href="{{ route('contact.show') }}" class="hover:text-white transition text-pink-400">Formulaire de contact →</a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-gray-700 pt-6">
                <p class="text-center text-[10px] md:text-xs text-gray-400">
                    © {{ date('Y') }} ElSayara. Tous droits réservés. | Plateforme de vente de véhicules d'occasion en Algérie
                </p>
            </div>
        </div>
    </footer>


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

{{-- CHATBOT ASSISTANT --}}
<div x-data="chatbot()" x-cloak class="fixed bottom-4 right-4 z-50">
    {{-- Bouton flottant --}}
    <button @click="toggle()"
            class="w-14 h-14 bg-pink-600 hover:bg-pink-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300"
            :class="{ 'scale-0': isOpen }"
            title="Aide">
        <span class="text-2xl">💬</span>
    </button>

    {{-- Fenêtre chat --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="absolute bottom-0 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-pink-600 to-pink-500 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">🤖</span>
                <div>
                    <h3 class="font-bold text-sm">Assistant ElSayara</h3>
                    <p class="text-[10px] text-pink-100">En ligne • Réponse instantanée</p>
                </div>
            </div>
            <button @click="toggle()" class="text-white/80 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Messages zone --}}
        <div x-ref="messagesContainer" class="h-72 overflow-y-auto p-4 space-y-3 bg-gray-50">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.type === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.type === 'user' 
                        ? 'bg-pink-600 text-white rounded-2xl rounded-br-md px-4 py-2 max-w-[85%]' 
                        : 'bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-md px-4 py-2 max-w-[85%] shadow-sm'">
                        <p class="text-sm" x-text="msg.text"></p>
                        {{-- Actions buttons --}}
                        <template x-if="msg.actions && msg.actions.length > 0">
                            <div class="mt-2 space-y-1">
                                <template x-for="(action, i) in msg.actions" :key="i">
                                    <a :href="action.url" 
                                       class="block text-xs bg-pink-50 text-pink-700 hover:bg-pink-100 px-3 py-1.5 rounded-lg transition text-center font-medium"
                                       x-text="action.label"></a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            {{-- Typing indicator --}}
            <div x-show="isTyping" class="flex justify-start">
                <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-md px-4 py-2 shadow-sm">
                    <div class="flex space-x-1">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="px-3 py-2 bg-white border-t border-gray-100">
            <div class="flex flex-wrap gap-1">
                <button @click="sendQuick('Déposer une annonce')" class="text-[10px] bg-gray-100 hover:bg-pink-100 text-gray-700 hover:text-pink-700 px-2 py-1 rounded-full transition">🚗 Déposer</button>
                <button @click="sendQuick('Rechercher une voiture')" class="text-[10px] bg-gray-100 hover:bg-pink-100 text-gray-700 hover:text-pink-700 px-2 py-1 rounded-full transition">🔍 Rechercher</button>
                <button @click="sendQuick('Mes favoris')" class="text-[10px] bg-gray-100 hover:bg-pink-100 text-gray-700 hover:text-pink-700 px-2 py-1 rounded-full transition">❤️ Favoris</button>
                <button @click="sendQuick('Mes messages')" class="text-[10px] bg-gray-100 hover:bg-pink-100 text-gray-700 hover:text-pink-700 px-2 py-1 rounded-full transition">💬 Messages</button>
                <button @click="sendQuick('Contact support')" class="text-[10px] bg-gray-100 hover:bg-pink-100 text-gray-700 hover:text-pink-700 px-2 py-1 rounded-full transition">📧 Contact</button>
            </div>
        </div>

        {{-- Input zone --}}
        <div class="p-3 bg-white border-t border-gray-200">
            <form @submit.prevent="send()" class="flex gap-2">
                <input type="text" 
                       x-model="input"
                       @keydown.enter="send()"
                       placeholder="Posez votre question..."
                       class="flex-1 px-4 py-2 text-sm border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                       :disabled="isTyping">
                <button type="submit" 
                        :disabled="!input.trim() || isTyping"
                        class="w-10 h-10 bg-pink-600 hover:bg-pink-700 disabled:bg-gray-300 text-white rounded-full flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function chatbot() {
    return {
        isOpen: false,
        isTyping: false,
        input: '',
        messages: [
            {
                type: 'bot',
                text: 'Bonjour ! 👋 Je suis l\'assistant ElSayara. Comment puis-je vous aider ?',
                actions: []
            }
        ],

        toggle() {
            this.isOpen = !this.isOpen;
        },

        sendQuick(text) {
            this.input = text;
            this.send();
        },

        async send() {
            const text = this.input.trim();
            if (!text || this.isTyping) return;

            // Add user message
            this.messages.push({ type: 'user', text: text, actions: [] });
            this.input = '';
            this.scrollToBottom();

            // Show typing indicator
            this.isTyping = true;

            try {
                const response = await fetch('{{ route("chatbot.ask") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: text })
                });

                const data = await response.json();

                // Simulate typing delay
                await new Promise(resolve => setTimeout(resolve, 500));

                this.messages.push({
                    type: 'bot',
                    text: data.reply,
                    actions: data.actions || []
                });
            } catch (error) {
                this.messages.push({
                    type: 'bot',
                    text: 'Désolé, une erreur est survenue. Veuillez réessayer.',
                    actions: []
                });
            }

            this.isTyping = false;
            this.scrollToBottom();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        }
    }
}
</script>


</body>
</html>
