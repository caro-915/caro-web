@extends('layouts.app')

@section('content')
    @php
        // Build an array of up to 4 image URLs from the stored paths
        $mainImage = $images[0] ?? asset('images/placeholder-car.jpg');


        // Normalize some fields (support both annee/year, kilometrage/mileage)
        $year       = $annonce->annee ?? $annonce->year;
        $mileage    = $annonce->kilometrage ?? $annonce->mileage;
        $fuel       = $annonce->carburant ?? $annonce->fuel_type;
        $gearbox    = $annonce->boite_vitesse ?? $annonce->gearbox;
        $city       = $annonce->ville ?? $annonce->city;
        $brandName  = $annonce->marque ?? optional($annonce->marque)->name;
        $modelName  = $annonce->modele ?? optional($annonce->modele)->name;

        // Extra fields
        $couleur = $annonce->couleur ?? null;
        $documentType = $annonce->document_type ?? null;
        $finition = $annonce->finition ?? null;
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-6 md:py-8">
        {{-- Breadcrumb / back link --}}
        <div class="mb-4 text-xs md:text-sm text-gray-500 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:underline">Accueil</a>
            <span>/</span>
            <a href="{{ route('annonces.search') }}" class="hover:underline">Voitures d'occasion</a>
            <span>/</span>
            <span class="text-gray-800">{{ $brandName }} {{ $modelName }}</span>
        </div>

        {{-- Top section: title + price --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-2 mb-4">
            <div>
                {{-- Main title: brand + model + main info --}}
                <h1 class="text-2xl md:text-3xl font-bold">
                    {{ $annonce->titre ?? trim($brandName . ' ' . $modelName) }}
                </h1>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    {{ $brandName }} @if($modelName) • {{ $modelName }} @endif
                    @if($year) • {{ $year }} @endif
                    @if($mileage) • {{ number_format($mileage, 0, ',', ' ') }} km @endif
                    @if($city) • {{ strtoupper($city) }} @endif
                </p>
            </div>

            {{-- Price block --}}
            <div class="text-right">
                <p class="text-xl md:text-3xl font-extrabold text-pink-600">
                    {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Prix affiché par le vendeur
                </p>
            </div>
        </div>

        {{-- Main layout: big image + right info panel --}}
        <div class="grid grid-cols-1 lg:grid-cols-[2fr,1fr] gap-6 mb-8">
            {{-- LEFT: main image slider + description --}}
            <div class="space-y-4">
                {{-- Main image slider --}}
                <div>
                    <div class="relative bg-white rounded-3xl shadow-lg overflow-hidden">
                    {{-- Main displayed image with zoom effect --}}
                    <img id="main_car_image"
                         src="{{ $mainImage }}"
                         alt="Photo véhicule"
                            class="w-full h-80 md:h-[28rem] object-cover transition-all duration-200 cursor-zoom-in hover:scale-[1.02]"
                         onclick="openLightbox(0)"
                         title="Cliquez pour agrandir">

                    {{-- Left arrow --}}
                    <button type="button"
                            id="prev_image_btn"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 md:w-9 md:h-9 rounded-full bg-white/90 shadow flex items-center justify-center text-gray-700 text-lg hover:bg-white">
                        ‹
                    </button>

                    {{-- Right arrow --}}
                    <button type="button"
                            id="next_image_btn"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 md:w-9 md:h-9 rounded-full bg-white/90 shadow flex items-center justify-center text-gray-700 text-lg hover:bg-white">
                        ›
                    </button>

                        {{-- Counter bottom-left --}}
                        <div id="image_counter"
                             class="absolute bottom-3 left-3 bg-white/90 text-[11px] md:text-xs px-2 py-1 rounded-full shadow">
                            1 / {{ count($images) }}
                        </div>
                    </div>
                </div>

                {{-- Key specs --}}
                <div class="bg-white rounded-2xl shadow p-4 md:p-5">
                    <h2 class="text-sm md:text-base font-semibold mb-3">Caractéristiques principales</h2>

                    <dl class="grid grid-cols-2 md:grid-cols-3 gap-y-3 gap-x-6 text-xs md:text-sm">
                        
                        <div>
                            <dt class="text-gray-400">Année</dt>
                            <dd class="text-gray-800 font-semibold">{{ $year ?? '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-400">Kilométrage</dt>
                            <dd class="text-gray-800 font-semibold">
                                @if($mileage)
                                    {{ number_format($mileage, 0, ',', ' ') }} km
                                @else
                                    —
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-400">Carburant</dt>
                            <dd class="text-gray-800 font-semibold">{{ $fuel ?? '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-400">Boîte de vitesses</dt>
                            <dd class="text-gray-800 font-semibold">{{ $gearbox ?? '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-gray-400">Ville / Wilaya</dt>
                            <dd class="text-gray-800 font-semibold">{{ $city ?? '—' }}</dd>
                        </div>

                        {{-- Véhicule neuf ? --}}
@if(($annonce->condition ?? 'non') === 'oui')
<div>
    <dt class="text-gray-400">Véhicule neuf ?</dt>
    <dd class="text-gray-800 font-semibold">
        Oui
    </dd>
</div>
@endif

{{-- Couleur --}}
<div>
    <dt class="text-gray-400">Couleur</dt>
    <dd class="text-gray-800 font-semibold">
        {{ $annonce->couleur ?: '—' }}
    </dd>
</div>

{{-- Document --}}
<div>
    <dt class="text-gray-400">Document</dt>
    <dd class="text-gray-800 font-semibold">
        @php
            $doc = $annonce->document_type;
        @endphp
        {{ $doc === 'carte_grise' ? 'Carte grise' : ($doc === 'procuration' ? 'Procuration' : '—') }}
    </dd>
</div>

{{-- Finition --}}
<div>
    <dt class="text-gray-400">Finition</dt>
    <dd class="text-gray-800 font-semibold">
        {{ $annonce->finition ?: '—' }}
    </dd>
</div>


                        {{-- ✅ Nouveaux champs --}}
                      
                    </dl>
                </div>

                {{-- Description --}}
                <div class="bg-white rounded-2xl shadow p-4 md:p-5">
                    <h2 class="text-sm md:text-base font-semibold mb-3">Description du véhicule</h2>
                    <p class="text-xs md:text-sm text-gray-700 whitespace-pre-line">
                        {{ $annonce->description ?: 'Aucune description détaillée fournie par le vendeur.' }}
                    </p>
                </div>
            </div>

            {{-- RIGHT: seller / actions panel --}}
            <aside class="space-y-4">
                {{-- Contact / actions --}}
                <div class="bg-white rounded-2xl shadow p-4 md:p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Vendeur</p>

                            <a href="{{ route('seller.show', $annonce->user) }}"
                               class="text-base md:text-lg font-extrabold hover:text-pink-600">
                                {{ $annonce->user->name }}
                            </a>

                           <p class="text-[11px] text-gray-400 mt-1">
    Annonce publiée {{ optional($annonce->created_at)->diffForHumans() }}
    <span class="mx-1">•</span>
    <span>{{ $annonce->views ?? 0 }} vues</span>

    @if(($annonce->views ?? 0) >= 50)
        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                     bg-orange-50 text-orange-700 border border-orange-200">
            🔥 Annonce populaire
        </span>
    @endif
</p>
                        </div>
                    </div>

                    <a href="{{ route('seller.show', $annonce->user) }}"
                       class="text-sm font-semibold text-pink-600 hover:underline">
                        Voir toutes les annonces de {{ $annonce->user->name }}
                    </a>

                    @php
                        $seller = $annonce->user;
                    @endphp

                    @if($seller && $seller->phone && ($annonce->show_phone ?? true))
                        <div x-data="{ showPhone: false }" class="space-y-3 mb-4 mt-4">
                            <button
                                @click="showPhone = true; if(window.innerWidth < 800) window.location.href='tel:{{ $seller->phone }}';"
                                class="w-full inline-flex items-center justify-center px-4 py-3
                                       rounded-full bg-pink-600 text-white text-sm font-semibold
                                       hover:bg-pink-700 transition"
                            >
                                Appeler le vendeur
                            </button>

                            <p x-show="showPhone" x-cloak class="text-center text-lg font-bold text-gray-900">
                                {{ $seller->phone }}
                            </p>
                        </div>
                    @endif

                    <div class="space-y-2 mt-4">
                        {{-- Bouton Envoyer un message --}}
                        @auth
                            @if(auth()->id() === $annonce->user_id)
                                {{-- Own annonce - grayed out button --}}
                                <button type="button" disabled
                                        class="w-full py-2 rounded-full border border-gray-200 text-xs font-semibold text-gray-400 cursor-not-allowed bg-gray-50">
                                    C'est votre annonce
                                </button>
                            @else
                                {{-- Can send message --}}
                                <form method="POST" action="{{ route('messages.start', $annonce) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-2 rounded-full border border-gray-200 text-xs font-semibold text-gray-700 hover:border-pink-500 hover:text-pink-600">
                                        Envoyer un message
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               onclick="event.preventDefault(); sessionStorage.setItem('redirectAfterLogin', '{{ url()->current() }}'); window.location.href='{{ route('login') }}';"
                               class="w-full inline-flex items-center justify-center py-2 rounded-full border border-gray-200 text-xs font-semibold text-gray-700 hover:border-pink-500 hover:text-pink-600">
                                Envoyer un message
                            </a>
                        @endauth

                        {{-- Favoris --}}
                        @auth
                            @php
                                $isFavorited = $annonce->favorites()
                                    ->where('user_id', auth()->id())
                                    ->exists();
                            @endphp

                            <form method="POST" action="{{ route('favorites.toggle', $annonce) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2 rounded-full border text-xs font-semibold
                                               {{ $isFavorited
                                                    ? 'border-pink-500 text-pink-600 hover:bg-pink-50'
                                                    : 'border-gray-200 text-gray-700 hover:border-pink-500 hover:text-pink-600' }}">
                                    {{ $isFavorited ? 'Retirer des favoris' : 'Ajouter aux favoris' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}"
                               class="w-full py-2 rounded-full border border-gray-200 text-xs text-gray-500 hover:border-gray-300 text-center inline-block">
                                Ajouter aux favoris
                            </a>
                        @endauth
                    </div>
                </div>

                {{-- Info bloc / conseils --}}
                <div class="bg-white rounded-2xl shadow p-4 text-xs text-gray-600 space-y-2">
                    <p class="font-semibold text-gray-800">Conseils Caro</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Vérifiez l’historique du véhicule.</li>
                        <li>Essayez la voiture sur route.</li>
                        <li>Comparez avec d’autres annonces similaires.</li>
                    </ul>
                </div>
            </aside>
        </div>

        {{-- Similar ads --}}
        @if($similarAds->count())
            <section class="mb-4">
                <h2 class="text-lg font-semibold mb-3">Annonces similaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($similarAds as $sim)
                        @php
                            $simImage = $sim->image_path
                                ? asset('storage/' . $sim->image_path)
                                : ($sim->image_url ?? asset('images/placeholder-car.jpg'));

                            $simYear    = $sim->annee ?? $sim->year;
                            $simMileage = $sim->kilometrage ?? $sim->mileage;
                            $simCity    = $sim->ville ?? $sim->city;
                        @endphp

                        <a href="{{ route('annonces.show', $sim->id) }}"
                           class="bg-white rounded-2xl shadow flex overflow-hidden hover:shadow-md transition">
                            <img src="{{ $simImage }}" alt="Photo similaire"
                                 class="w-32 h-24 object-cover">
                            <div class="flex-1 p-3 flex flex-col justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">
                                        {{ $sim->marque ?? optional($sim->marque)->name }}
                                        @if($sim->modele || optional($sim->modele)->name)
                                            • {{ $sim->modele ?? optional($sim->modele)->name }}
                                        @endif
                                    </p>
                                    <p class="text-sm font-semibold">
                                        {{ $sim->titre ?? 'Annonce #' . $sim->id }}
                                    </p>
                                    <p class="text-[11px] text-gray-400">
                                        @if($simYear) {{ $simYear }} • @endif
                                        @if($simMileage) {{ number_format($simMileage, 0, ',', ' ') }} km • @endif
                                        {{ $simCity ?? '—' }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-pink-600">
                                    {{ number_format($sim->prix, 0, ',', ' ') }} DA
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Lightbox modal stylé --}}
    <div id="lightbox" class="hidden fixed inset-0 bg-black/95 backdrop-blur-sm flex items-center justify-center transition-all duration-300" style="z-index: 9999;">
        <div class="relative w-full h-full">
            <!-- Image centrée + contrôles overlay liés à l'image -->
            <div class="flex items-center justify-center p-6 w-full h-full">
                <div class="relative inline-block w-[92vw] h-[88vh]">
                    <!-- Bouton fermer stylé -->
                    <button onclick="closeLightbox()" 
                            class="absolute -top-12 right-0 md:top-2 md:right-2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-pink-600 text-white text-xl md:text-2xl font-bold hover:bg-pink-700 hover:scale-110 transition-all duration-200 flex items-center justify-center shadow-2xl"
                            style="z-index: 10000;">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    
                    <img id="lightbox_image" 
                         src="" 
                         alt="Photo agrandie" 
                         class="max-w-full max-h-full w-full h-full object-contain rounded-xl md:rounded-2xl shadow-2xl transition-all duration-300">

                    <!-- Bouton précédent stylé -->
                    <button id="lightbox_prev_btn" onclick="prevLightboxImage(event)" 
                            class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 w-12 h-12 md:w-14 md:h-14 rounded-full bg-white/95 hover:bg-pink-600 text-gray-800 hover:text-white text-3xl md:text-4xl font-bold transition-all duration-200 flex items-center justify-center shadow-2xl hover:scale-110"
                            style="z-index: 10000;">
                        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Bouton suivant stylé -->
                    <button id="lightbox_next_btn" onclick="nextLightboxImage(event)" 
                            class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 w-12 h-12 md:w-14 md:h-14 rounded-full bg-white/95 hover:bg-pink-600 text-gray-800 hover:text-white text-3xl md:text-4xl font-bold transition-all duration-200 flex items-center justify-center shadow-2xl hover:scale-110"
                            style="z-index: 10000;">
                        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Compteur stylé avec icône -->
                    <div id="lightbox_counter" 
                         class="absolute bottom-2 md:bottom-4 left-1/2 -translate-x-1/2 bg-pink-600 text-white px-4 md:px-5 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-bold shadow-2xl flex items-center gap-2"
                         style="z-index: 10000;">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                        <span>1 / 1</span>
                    </div>
                </div>
            </div>
    </div>

    {{-- Style CSS pour animations lightbox --}}
    <style>
        #lightbox {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        #lightbox_image {
            transform: scale(0.95);
            transition: opacity 0.15s ease-in-out, transform 0.3s ease-in-out;
        }
        
        /* Curseur zoom-in personnalisé */
        .cursor-zoom-in {
            cursor: zoom-in;
        }
        
        /* Animation hover sur l'image principale */
        #main_car_image {
            position: relative;
        }
        
        #main_car_image::after {
            content: '🔍';
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(236, 72, 153, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        #main_car_image:hover::after {
            opacity: 1;
        }
    </style>

    {{-- Simple image slider --}}
    <script>
        const images = @json($images);
        let currentIndex = 0;
        let lightboxIndex = 0;

        document.addEventListener('DOMContentLoaded', () => {
            const mainImg  = document.getElementById('main_car_image');
            const prevBtn  = document.getElementById('prev_image_btn');
            const nextBtn  = document.getElementById('next_image_btn');
            const counter  = document.getElementById('image_counter');

            if (!mainImg || !images.length) return;

            function updateImage() {
                mainImg.src = images[currentIndex];
                mainImg.setAttribute('onclick', `openLightbox(${currentIndex})`);
                if (counter) {
                    counter.textContent = (currentIndex + 1) + ' / ' + images.length;
                }
            }

            if (images.length <= 1) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                if (counter) counter.style.display = 'none';
                return;
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    updateImage();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentIndex = (currentIndex + 1) % images.length;
                    updateImage();
                });
            }
        });

        function openLightbox(index) {
            lightboxIndex = index;
            updateLightbox();
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightbox_image');
            
            // Animation d'ouverture
            lightbox.classList.remove('hidden');
            setTimeout(() => {
                lightbox.style.opacity = '1';
                lightboxImage.style.transform = 'scale(1)';
            }, 10);
            
            document.body.style.overflow = 'hidden';

            // Masquer les boutons si une seule image
            const prevBtn = document.getElementById('lightbox_prev_btn');
            const nextBtn = document.getElementById('lightbox_next_btn');
            if (images.length <= 1) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
            } else {
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'flex';
            }
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightbox_image');
            
            // Animation de fermeture
            lightbox.style.opacity = '0';
            lightboxImage.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                lightbox.classList.add('hidden');
                document.body.style.overflow = '';
            }, 200);
        }

        function updateLightbox() {
            const lightboxImg = document.getElementById('lightbox_image');
            const lightboxCounter = document.getElementById('lightbox_counter');
            const counterSpan = lightboxCounter?.querySelector('span');
            
            if (lightboxImg && images[lightboxIndex]) {
                // Animation de transition
                lightboxImg.style.opacity = '0.5';
                lightboxImg.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    lightboxImg.src = images[lightboxIndex];
                    lightboxImg.style.opacity = '1';
                    lightboxImg.style.transform = 'scale(1)';
                }, 150);
            }
            
            if (counterSpan) {
                counterSpan.textContent = (lightboxIndex + 1) + ' / ' + images.length;
            }
        }

        function prevLightboxImage(e) {
            if (e) e.stopPropagation();
            lightboxIndex = (lightboxIndex - 1 + images.length) % images.length;
            updateLightbox();
        }

        function nextLightboxImage(e) {
            if (e) e.stopPropagation();
            lightboxIndex = (lightboxIndex + 1) % images.length;
            updateLightbox();
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            const lightbox = document.getElementById('lightbox');
            if (!lightbox.classList.contains('hidden')) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') prevLightboxImage();
                if (e.key === 'ArrowRight') nextLightboxImage();
            }
        });

        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;

        document.getElementById('lightbox').addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.getElementById('lightbox').addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchEndX < touchStartX - 50) nextLightboxImage();
            if (touchEndX > touchStartX + 50) prevLightboxImage();
        }
    </script>
@endsection
