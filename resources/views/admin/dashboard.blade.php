@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Dashboard Admin</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="font-semibold">Annonces</h2>
            <p class="text-sm text-gray-500">Gérer toutes les annonces</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="font-semibold">Utilisateurs</h2>
            <p class="text-sm text-gray-500">Gérer les comptes</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="font-semibold">Statistiques</h2>
            <p class="text-sm text-gray-500">Vues, annonces populaires</p>
        </div>
    </div>
</div>


            {{-- À activer plus tard quand on crée les pages --}}
            <a href="{{ route('admin.annonces.index') }}"
               class="px-4 py-2 rounded-full bg-pink-600 text-white text-xs font-semibold hover:bg-pink-700 inline-block">
               Gérer les annonces
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="px-4 py-2 rounded-full border border-gray-200 text-xs font-semibold text-gray-700 hover:border-pink-500 hover:text-pink-600 inline-block">
               Gérer les utilisateurs
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Latest ads --}}
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold">Dernières annonces</h2>
                <span class="text-xs text-gray-400">{{ $latestAds->count() }} affichées</span>
            </div>

            <div class="space-y-3">
                @forelse($latestAds as $ad)
                    <div class="flex items-center justify-between gap-3 border border-gray-100 rounded-xl p-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate">
                                <a href="{{ route('annonces.show', $ad) }}" class="hover:text-pink-600">
                                    {{ $ad->titre ?? 'Annonce #'.$ad->id }}
                                </a>
                            </p>
                            <p class="text-[11px] text-gray-500">
                                {{ $ad->marque }} @if($ad->modele) • {{ $ad->modele }} @endif
                                • {{ $ad->created_at?->diffForHumans() }}
                                @if(isset($ad->views)) • {{ $ad->views }} vue{{ $ad->views > 1 ? 's' : '' }} @endif
                            </p>
                            <p class="text-[11px] text-gray-400">
                                Vendeur : {{ $ad->user?->name ?? '—' }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-sm font-extrabold text-pink-600">
                                {{ number_format($ad->prix ?? 0, 0, ',', ' ') }} DA
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">Aucune annonce pour le moment.</p>
                @endforelse
            </div>
        </div>

        {{-- Latest users --}}
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold">Derniers utilisateurs</h2>
                <span class="text-xs text-gray-400">{{ $latestUsers->count() }} affichés</span>
            </div>

            <div class="space-y-3">
                @forelse($latestUsers as $u)
                    <div class="flex items-center justify-between gap-3 border border-gray-100 rounded-xl p-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate">
                                {{ $u->name }}
                                @if($u->is_admin)
                                    <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-pink-50 text-pink-700 font-semibold">
                                        admin
                                    </span>
                                @endif
                            </p>
                            <p class="text-[11px] text-gray-500 truncate">{{ $u->email }}</p>
                            <p class="text-[11px] text-gray-400">Inscrit {{ $u->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">Aucun utilisateur pour le moment.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
