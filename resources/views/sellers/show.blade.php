@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- En-tête profil vendeur --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold">
                    Profil vendeur — {{ $seller->name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Membre depuis {{ optional($seller->created_at)->format('d/m/Y') }}
                    · <span class="font-semibold text-gray-800">{{ $totalAds }}</span> annonce(s)
                </p>
            </div>

            <a href="{{ route('annonces.search') }}"
               class="hidden sm:inline-flex items-center justify-center px-4 py-2 rounded-full border border-gray-200 hover:bg-gray-50 text-sm font-semibold">
                Rechercher sur Caro
            </a>
        </div>
    </div>

    {{-- Liste annonces --}}
    <div class="mt-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-bold">Ses annonces</h2>
            <p class="text-xs text-gray-500">{{ $annonces->total() }} résultat(s)</p>
        </div>

        @if($annonces->count() === 0)
            <div class="bg-white rounded-2xl shadow p-6 text-sm text-gray-600">
                Ce vendeur n’a pas encore publié d’annonces.
            </div>
        @else
            <div class="space-y-4">
                @foreach($annonces as $annonce)
                    @php
                        $img = $annonce->image_path ? asset('storage/'.$annonce->image_path) : null;
                    @endphp

                    <div class="bg-white rounded-2xl shadow hover:shadow-md transition overflow-hidden">
                        <div class="flex flex-col sm:flex-row">
                            {{-- Image gauche (comme capture 1) --}}
                            <a href="{{ route('annonces.show', $annonce) }}"
                               class="block w-full sm:w-[240px] h-[150px] sm:h-[140px] bg-gray-100 overflow-hidden">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $annonce->titre }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                        Pas de photo
                                    </div>
                                @endif
                            </a>

                            {{-- Infos centre --}}
                            <div class="flex-1 p-4">
                                <div class="text-xs text-gray-400">
                                    {{ $annonce->marque }} · {{ $annonce->modele }}
                                </div>

                                <a href="{{ route('annonces.show', $annonce) }}"
                                   class="block font-extrabold text-base leading-tight mt-1 hover:text-pink-600">
                                    {{ $annonce->titre }}
                                </a>

                                <div class="text-xs text-gray-500 mt-2">
                                    @if($annonce->annee) {{ $annonce->annee }} · @endif
                                    @if($annonce->kilometrage) {{ number_format($annonce->kilometrage, 0, ',', ' ') }} km · @endif
                                    @if($annonce->carburant) {{ $annonce->carburant }} · @endif
                                    @if($annonce->ville) {{ $annonce->ville }} @endif
                                </div>
                            </div>

                            {{-- Colonne droite prix + date (comme capture 1) --}}
                            <div class="p-4 sm:p-4 sm:w-[220px] flex sm:flex-col justify-between sm:justify-start sm:items-end gap-3 border-t sm:border-t-0 sm:border-l border-gray-100">
                                <div class="text-right">
                                    <div class="text-pink-600 font-extrabold text-lg">
                                        {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-1">
                                        Créée le {{ optional($annonce->created_at)->format('d/m/Y') }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="{{ route('annonces.show', $annonce) }}"
                                       class="text-xs font-semibold text-pink-600 hover:underline">
                                        Voir le détail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $annonces->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
