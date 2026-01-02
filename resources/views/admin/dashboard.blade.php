@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Dashboard Admin</h1>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Total Annonces</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['annonces'] ?? 0 }}</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Actives</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">En Attente</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Utilisateurs</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['users'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mb-8 flex justify-center gap-3">
        <a href="{{ route('admin.annonces.index') }}"
           class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-sm font-semibold text-gray-700 hover:border-gray-800 hover:text-gray-800 inline-block">
           Gérer les annonces
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-sm font-semibold text-gray-700 hover:border-gray-800 hover:text-gray-800 inline-block">
           Gérer les utilisateurs
        </a>
    </div>

    {{-- Latest ads --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Dernières annonces</h2>
            <span class="text-sm text-gray-500">{{ $latestAds->count() }} affichées</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left p-3">Annonce</th>
                        <th class="text-left p-3">Vendeur</th>
                        <th class="text-left p-3">Prix</th>
                        <th class="text-center p-3">Vues</th>
                        <th class="text-center p-3">Statut</th>
                        <th class="text-center p-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($latestAds as $ad)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">
                                <div class="font-semibold truncate">{{ $ad->titre ?? 'Annonce #'.$ad->id }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $ad->marque }} @if($ad->modele) • {{ $ad->modele }} @endif • {{ $ad->created_at?->diffForHumans() }}
                                </div>
                            </td>

                            <td class="p-3">
                                <div class="font-medium">{{ $ad->user?->name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $ad->user?->email ?? '' }}</div>
                            </td>

                            <td class="p-3 font-bold text-gray-800">
                                {{ number_format($ad->prix ?? 0, 0, ',', ' ') }} DA
                            </td>

                            <td class="p-3 text-center text-gray-600">
                                {{ $ad->views ?? 0 }}
                            </td>

                            <td class="p-3 text-center">
                                @if($ad->is_active)
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs bg-green-50 text-green-700 font-semibold">✓ Active</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 font-semibold">⧖ Attente</span>
                                @endif
                            </td>

                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <form method="POST" action="{{ route('admin.annonces.toggle', $ad) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-2.5 py-1 rounded-lg border text-xs font-semibold
                                            {{ $ad->is_active ? 'border-gray-200 text-gray-700 hover:border-gray-800 hover:text-gray-800' : 'border-green-500 text-green-600 hover:bg-green-50' }}">
                                            {{ $ad->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.annonces.destroy', $ad) }}" class="inline"
                                          onsubmit="return confirm('Supprimer cette annonce ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-2.5 py-1 rounded-lg border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">
                                Aucune annonce pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
