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
                         class="object-cover" style="width: 10cm; height: 5cm;">

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
                    <div class="w-60 px-4 py-3 flex flex-col items-end justify-between text-right border-l border-gray-100">
                        <div>
                            <p class="text-sm md:text-base font-extrabold text-pink-600">
                                {{ number_format($annonce->prix, 0, ',', ' ') }} DA
                            </p>
                            
                            <p class="text-[11px] text-gray-400">
                                Créée le {{ $annonce->created_at->format('d/m/Y') }}
                                <span class="mx-1">•</span>
                                {{ $annonce->views ?? 0 }} vues
                                </p>

                                @if(($annonce->views ?? 0) >= 50)
                        <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-[11px] bg-orange-50 text-orange-700 border border-orange-200">
                                    🔥 Annonce populaire
                        </span>
                                @endif
                        </div>

                        <div class="mt-3 flex flex-col gap-2 w-full items-end">
    <a href="{{ route('annonces.show', $annonce) }}"
       class="w-full text-center px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:border-gray-800 hover:text-gray-800">
        Voir le détail
    </a>

    <a href="{{ route('annonces.edit', $annonce) }}"
       class="w-full text-center px-3 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:border-gray-800 hover:text-gray-800">
        Éditer
    </a>

    <button type="button"
            onclick="openDeleteModal({{ $annonce->id }}, '{{ addslashes($annonce->titre) }}')"
            class="w-full text-center px-3 py-2 rounded-xl border border-red-200 text-xs font-semibold text-red-600 hover:bg-red-50">
        Supprimer
    </button>
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

{{-- Modal de confirmation de suppression --}}
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-xl font-bold mb-4">Supprimer l'annonce</h3>
        <p class="text-sm text-gray-600 mb-4" id="deleteAnnonceTitle"></p>
        
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            
            <div class="mb-4">
                <p class="text-sm font-semibold mb-3">Avez-vous vendu ce véhicule ?</p>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="was_sold" value="oui" class="text-gray-800 focus:ring-gray-800" required>
                        <span class="text-sm">Oui</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="was_sold" value="non" class="text-gray-800 focus:ring-gray-800" required>
                        <span class="text-sm">Non</span>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2 rounded-full border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 rounded-full bg-red-600 text-white text-sm font-semibold hover:bg-red-700">
                    Supprimer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(annonceId, annonceTitle) {
    document.getElementById('deleteAnnonceTitle').textContent = annonceTitle;
    document.getElementById('deleteForm').action = '/annonces/' + annonceId;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection
