@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8">

    <h1 class="text-2xl md:text-3xl font-bold mb-6">Mes annonces</h1>

    @if(session('success'))
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if($annonces->isEmpty())
        <p class="text-sm text-gray-500">Vous n’avez encore publié aucune annonce.</p>
    @else
        <div class="space-y-3">
            @foreach($annonces as $annonce)
                @php
                    $image = $annonce->image_path
                        ? asset('storage/'.$annonce->image_path)
                        : ($annonce->image_url ?? asset('images/placeholder-car.jpg'));

                    $year    = $annonce->annee ?? $annonce->year;
                    $mileage = $annonce->kilometrage ?? $annonce->mileage;
                    $city    = $annonce->ville ?? $annonce->city;
                    $fuel    = $annonce->carburant ?? $annonce->fuel_type;
                @endphp

                {{-- Carte type "liste annonces" horizontale --}}
                <div class="bg-white rounded-2xl shadow flex overflow-hidden">
                    {{-- Image à gauche --}}
                    <img src="{{ $image }}" alt="Photo"
                         class="w-56 h-40 object-cover">

                    {{-- Infos centre --}}
                    <div class="flex-1 px-4 py-3 flex flex-col justify-between">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">
                                {{ $annonce->marque }}
                                @if($annonce->modele) • {{ $annonce->modele }} @endif
                            </p>
                            <p class="text-base font-semibold">
                                {{ $annonce->titre ?? 'Annonce #'.$annonce->id }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-1">
                                @if($year) {{ $year }} • @endif
                                @if($mileage) {{ number_format($mileage, 0, ',', ' ') }} km • @endif
                                @if($fuel) {{ $fuel }} • @endif
                                {{ $city ?? '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Bloc droite : prix + actions --}}
                    <div class="w-52 px-4 py-3 flex flex-col items-end justify-between text-right border-l border-gray-100">
                        <div>
                            <p class="text-sm md:text-base font-extrabold text-pink-600">
                                {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                            </p>
                            
                            <p class="text-[11px] text-gray-400">
                                Créée le {{ $annonce->created_at->format('d/m/Y') }}
                                <span class="mx-1">•</span>
                                {{ $annonce->views ?? 0 }} vues

                                @if(($annonce->views ?? 0) >= 50)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px]
                                    bg-orange-50 text-orange-700 border border-orange-200">
                                    🔥 Populaire
                                 </span>
                                @endif
                                </p>



                                @if(($annonce->views ?? 0) >= 50)
                        <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-[11px] bg-orange-50 text-orange-700 border border-orange-200">
                                    🔥 Annonce populaire
                        </span>
                                @endif
                        </div>

                        <div class="mt-2 text-[11px] flex flex-col items-end space-y-1">
                            <a href="{{ route('annonces.show', $annonce) }}"
                               class="text-pink-600 hover:text-pink-700">
                                Voir le détail
                            </a>

                            <a href="{{ route('annonces.edit', $annonce) }}"
   class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 hover:text-pink-600">
   <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
     <path d="M13.586 3.586a2 2 0 012.828 2.828l-9.5 9.5a1 1 0 01-.39.242l-3 1a1 1 0 01-1.265-1.265l1-3a1 1 0 01.242-.39l9.5-9.5z"/>
   </svg>
   Éditer l’annonce
</a>

                            <form method="POST" action="{{ route('annonces.destroy', $annonce) }}"
      onsubmit="return confirm('Supprimer cette annonce ?');">
  @csrf @method('DELETE')
  <button type="submit"
          class="inline-flex items-center gap-2 text-xs font-semibold text-red-600 hover:text-red-700">
    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M8.5 3a1 1 0 00-1 1v1H5a1 1 0 100 2h.5l.7 9.1A2 2 0 008.2 18h3.6a2 2 0 002-1.9l.7-9.1H15a1 1 0 100-2h-2.5V4a1 1 0 00-1-1h-3zM9.5 5h1V4h-1v1z" clip-rule="evenodd"/>
    </svg>
    Supprimer l’annonce
  </button>
</form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination, si tu veux la garder --}}
        <div class="mt-4">
            {{ $annonces->links() }}
        </div>
    @endif
</div>
@endsection
