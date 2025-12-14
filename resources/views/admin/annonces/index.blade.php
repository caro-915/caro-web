@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Admin · Annonces</h1>
            <p class="text-sm text-gray-500">Gérer toutes les annonces (activer/désactiver/supprimer).</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-pink-600">
            ← Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow p-4 mb-5">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-[1fr,220px,140px] gap-3">
            <input type="text"
                   name="q"
                   value="{{ $q }}"
                   class="w-full border rounded-xl px-3 py-2 text-sm"
                   placeholder="Rechercher (titre, marque, modèle, vendeur, email)">

            <select name="status" class="w-full border rounded-xl px-3 py-2 text-sm">
                <option value="all" {{ $status==='all' ? 'selected' : '' }}>Tous</option>
                <option value="active" {{ $status==='active' ? 'selected' : '' }}>Actives</option>
                <option value="inactive" {{ $status==='inactive' ? 'selected' : '' }}>Désactivées</option>
            </select>

            <button class="rounded-xl bg-pink-600 text-white text-sm font-semibold px-4 py-2 hover:bg-pink-700">
                Filtrer
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left p-3">Annonce</th>
                        <th class="text-left p-3">Vendeur</th>
                        <th class="text-left p-3">Prix</th>
                        <th class="text-left p-3">Vues</th>
                        <th class="text-left p-3">Statut</th>
                        <th class="text-right p-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($annonces as $a)
                        @php
                            $views = $a->views ?? 0;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="p-3">
                                <div class="font-semibold text-gray-900">{{ $a->titre }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $a->marque }} • {{ $a->modele }} • {{ optional($a->created_at)->diffForHumans() }}
                                </div>
                                <a href="{{ route('annonces.show', $a->id) }}" class="text-xs text-pink-600 hover:underline">
                                    Voir l’annonce
                                </a>
                            </td>

                            <td class="p-3">
                                <div class="font-semibold">{{ optional($a->user)->name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ optional($a->user)->email ?? '' }}</div>
                            </td>

                            <td class="p-3 font-bold text-pink-600">
                                {{ number_format($a->prix, 0, ',', ' ') }} DA
                            </td>

                            <td class="p-3">
                                {{ $views }}
                            </td>

                            <td class="p-3">
                                @if($a->is_active)
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs bg-green-50 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-50 text-red-700">Désactivée</span>
                                @endif
                            </td>

                            <td class="p-3 text-right space-x-2">
                                <form method="POST" action="{{ route('admin.annonces.toggle', $a) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="px-3 py-1.5 rounded-full border text-xs font-semibold
                                        {{ $a->is_active ? 'border-gray-200 text-gray-700 hover:border-pink-500 hover:text-pink-600' : 'border-pink-500 text-pink-600 hover:bg-pink-50' }}">
                                        {{ $a->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.annonces.destroy', $a) }}" class="inline"
                                      onsubmit="return confirm('Supprimer définitivement cette annonce ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-full border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">
                                Aucune annonce trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $annonces->links() }}
        </div>
    </div>

</div>
@endsection
