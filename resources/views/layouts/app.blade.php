<!DOCTYPE html>
<html lang="fr">
    <style>
  html { scroll-behavior: smooth; overflow-y: scroll; }
</style>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

{{-- CHATBOT ASSISTANT - Version JavaScript pure --}}
<div id="chatbot-container">
    {{-- Bouton flottant robot --}}
    <button id="chatbot-btn" onclick="toggleChatbot()" 
            style="position:fixed;bottom:16px;right:16px;z-index:9999;width:56px;height:56px;background:#db2777;color:white;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(0,0,0,0.2);transition:transform 0.2s;"
            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <span style="font-size:28px;">🤖</span>
    </button>

    {{-- Fenêtre chat (cachée par défaut) --}}
    <div id="chatbot-window" style="display:none;position:fixed;bottom:16px;right:16px;z-index:9999;width:384px;max-width:calc(100vw - 32px);background:white;border-radius:16px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);border:1px solid #e5e7eb;overflow:hidden;">
        {{-- Header --}}
        <div style="background:linear-gradient(to right,#db2777,#ec4899);color:white;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:20px;">🤖</span>
                <div>
                    <div style="font-weight:bold;font-size:14px;">Assistant ElSayara</div>
                    <div style="font-size:10px;opacity:0.8;">En ligne • Réponse instantanée</div>
                </div>
            </div>
            <button onclick="toggleChatbot()" style="background:none;border:none;color:white;cursor:pointer;opacity:0.8;font-size:20px;">✕</button>
        </div>

        {{-- Messages --}}
        <div id="chatbot-messages" style="height:288px;overflow-y:auto;padding:16px;background:#f9fafb;">
            <div style="display:flex;justify-content:flex-start;">
                <div style="background:white;border:1px solid #e5e7eb;border-radius:16px;border-bottom-left-radius:4px;padding:8px 16px;max-width:85%;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <p style="font-size:14px;margin:0;">Bonjour ! 👋 Je suis l'assistant ElSayara. Comment puis-je vous aider ?</p>
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div style="padding:8px 12px;background:white;border-top:1px solid #f3f4f6;">
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                <button onclick="sendQuickMessage('Déposer une annonce')" style="font-size:10px;background:#f3f4f6;border:none;padding:4px 8px;border-radius:12px;cursor:pointer;">🚗 Déposer</button>
                <button onclick="sendQuickMessage('Rechercher une voiture')" style="font-size:10px;background:#f3f4f6;border:none;padding:4px 8px;border-radius:12px;cursor:pointer;">🔍 Rechercher</button>
                <button onclick="sendQuickMessage('Mes favoris')" style="font-size:10px;background:#f3f4f6;border:none;padding:4px 8px;border-radius:12px;cursor:pointer;">❤️ Favoris</button>
                <button onclick="sendQuickMessage('Contact support')" style="font-size:10px;background:#f3f4f6;border:none;padding:4px 8px;border-radius:12px;cursor:pointer;">📧 Contact</button>
            </div>
        </div>

        {{-- Input --}}
        <div style="padding:12px;background:white;border-top:1px solid #e5e7eb;">
            <form onsubmit="sendChatMessage(event)" style="display:flex;gap:8px;">
                <input type="text" id="chatbot-input" placeholder="Posez votre question..." 
                       style="flex:1;padding:8px 16px;font-size:14px;border:1px solid #e5e7eb;border-radius:20px;outline:none;">
                <button type="submit" style="width:40px;height:40px;background:#db2777;color:white;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleChatbot() {
    const btn = document.getElementById('chatbot-btn');
    const win = document.getElementById('chatbot-window');
    if (win.style.display === 'none') {
        win.style.display = 'block';
        btn.style.display = 'none';
        document.getElementById('chatbot-input')?.focus();
    } else {
        win.style.display = 'none';
        btn.style.display = 'flex';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function sendQuickMessage(text) {
    document.getElementById('chatbot-input').value = text;
    sendChatMessage(new Event('submit'));
}

function addQuickReplies(quickReplies) {
    if (!quickReplies || quickReplies.length === 0) return '';
    return '<div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px;">' +
        quickReplies.map(qr => 
            `<button onclick="sendQuickMessage('${escapeHtml(qr)}')" style="font-size:11px;background:#fdf2f8;color:#be185d;border:1px solid #fbcfe8;padding:4px 10px;border-radius:12px;cursor:pointer;">${escapeHtml(qr)}</button>`
        ).join('') + '</div>';
}

async function sendChatMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chatbot-input');
    const text = input.value.trim();
    if (!text) return;

    const messagesDiv = document.getElementById('chatbot-messages');
    
    // Add user message (escaped)
    messagesDiv.innerHTML += `<div style="display:flex;justify-content:flex-end;margin-top:12px;">
        <div style="background:#db2777;color:white;border-radius:16px;border-bottom-right-radius:4px;padding:8px 16px;max-width:85%;">
            <p style="font-size:14px;margin:0;">${escapeHtml(text)}</p>
        </div>
    </div>`;
    
    input.value = '';
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    // Show typing indicator
    const typingId = 'typing-' + Date.now();
    messagesDiv.innerHTML += `<div id="${typingId}" style="display:flex;justify-content:flex-start;margin-top:12px;">
        <div style="background:white;border:1px solid #e5e7eb;border-radius:16px;padding:8px 16px;">
            <span style="display:inline-flex;gap:4px;">
                <span style="width:6px;height:6px;background:#9ca3af;border-radius:50%;animation:bounce 1s infinite;"></span>
                <span style="width:6px;height:6px;background:#9ca3af;border-radius:50%;animation:bounce 1s infinite 0.15s;"></span>
                <span style="width:6px;height:6px;background:#9ca3af;border-radius:50%;animation:bounce 1s infinite 0.3s;"></span>
            </span>
        </div>
    </div>`;
    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    try {
        const response = await fetch('/chatbot', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ message: text })
        });
        const data = await response.json();

        // Remove typing indicator
        document.getElementById(typingId)?.remove();

        // Ensure reply exists (fix undefined bug)
        const reply = data.reply || 'Je peux vous aider avec la recherche, le dépôt d\'annonces, et plus encore !';
        
        // Build actions HTML
        let actionsHtml = '';
        if (data.actions && data.actions.length > 0) {
            actionsHtml = '<div style="margin-top:8px;">' + 
                data.actions.map(a => `<a href="${escapeHtml(a.url || '#')}" style="display:block;font-size:12px;background:#fdf2f8;color:#be185d;padding:6px 12px;border-radius:8px;text-decoration:none;text-align:center;margin-top:4px;border:1px solid #fbcfe8;">${escapeHtml(a.label || 'Action')}</a>`).join('') +
                '</div>';
        }

        // Build quick replies HTML
        const quickRepliesHtml = addQuickReplies(data.quick_replies);

        messagesDiv.innerHTML += `<div style="display:flex;justify-content:flex-start;margin-top:12px;">
            <div style="background:white;border:1px solid #e5e7eb;border-radius:16px;border-bottom-left-radius:4px;padding:8px 16px;max-width:85%;box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                <p style="font-size:14px;margin:0;">${escapeHtml(reply)}</p>
                ${actionsHtml}
                ${quickRepliesHtml}
            </div>
        </div>`;
    } catch (err) {
        document.getElementById(typingId)?.remove();
        messagesDiv.innerHTML += `<div style="display:flex;justify-content:flex-start;margin-top:12px;">
            <div style="background:white;border:1px solid #e5e7eb;border-radius:16px;padding:8px 16px;">
                <p style="font-size:14px;margin:0;">Désolé, une erreur est survenue. Réessayez ou utilisez les boutons ci-dessus.</p>
            </div>
        </div>`;
    }
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}
</script>

<style>
@keyframes bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}
</style>


</body>
</html>
