@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Admin · Statistiques</h1>
            <p class="text-sm text-gray-500">Vues, annonces populaires, activité.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-pink-600">← Dashboard</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-sm text-gray-500">Total vues</p>
            <p class="text-3xl font-extrabold text-gray-900">{{ number_format($totalViews ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-sm text-gray-500">Annonces actives</p>
            <p class="text-3xl font-extrabold text-gray-900">{{ $activeCount }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-sm text-gray-500">Annonces désactivées</p>
            <p class="text-3xl font-extrabold text-gray-900">{{ $inactiveCount }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="p-5 border-b">
            <h2 class="text-lg font-semibold">Top annonces (les plus vues)</h2>
        </div>

        <div class="divide-y">
            @foreach($topViewed as $a)
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <div class="font-semibold">{{ $a->titre }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $a->marque }} • {{ $a->modele }} • Vendeur: {{ optional($a->user)->name ?? '—' }}
                        </div>
                        <a class="text-xs text-pink-600 hover:underline" href="{{ route('annonces.show', $a->id) }}">
                            Voir l’annonce
                        </a>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Vues</div>
                        <div class="text-2xl font-extrabold">{{ $a->views ?? 0 }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
