@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8">
    <h1 class="text-xl font-bold mb-4">Mes favoris</h1>

    @if ($annonces->isEmpty())
        <p class="text-sm text-gray-500">
            Vous n’avez encore aucune annonce en favori.
        </p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($annonces as $annonce)
                @php
                    $image = $annonce->image_path
                        ? asset('storage/' . $annonce->image_path)
                        : ($annonce->image_url ?? asset('images/placeholder-car.jpg'));

                    $year    = $annonce->annee ?? $annonce->year;
                    $mileage = $annonce->kilometrage ?? $annonce->mileage;
                    $city    = $annonce->ville ?? $annonce->city;
                @endphp

                <div class="bg-white rounded-2xl shadow overflow-hidden hover:shadow-md transition">
                    {{-- Partie cliquable : ouvre la fiche annonce --}}
                    <a href="{{ route('annonces.show', $annonce) }}"
                       class="flex">
                        <img src="{{ $image }}" alt="Photo véhicule"
                             class="w-32 h-24 object-cover">
                        <div class="flex-1 p-3 flex flex-col justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">
                                    {{ $annonce->marque ?? optional($annonce->marque)->name }}
                                    @if($annonce->modele || optional($annonce->modele)->name)
                                        • {{ $annonce->modele ?? optional($annonce->modele)->name }}
                                    @endif
                                </p>
                                <p class="text-sm font-semibold">
                                    {{ $annonce->titre ?? 'Annonce #'.$annonce->id }}
                                </p>
                                <p class="text-[11px] text-gray-400">
                                    @if($year) {{ $year }} • @endif
                                    @if($mileage) {{ number_format($mileage, 0, ',', ' ') }} km • @endif
                                    {{ $city ?? '—' }}
                                </p>
                            </div>
                            <p class="text-sm font-bold text-pink-600">
                                {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                            </p>
                        </div>
                    </a>

                    {{-- Actions (hors lien) --}}
                    <div class="px-3 pb-3">
                        <form action="{{ route('favorites.toggle', $annonce) }}"
                              method="POST"
                              class="inline-block">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 underline">
                                Retirer des favoris
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
