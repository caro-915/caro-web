@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Admin · Annonces</h1>
            <p class="text-sm text-gray-500">Gérer toutes les annonces (approuver/rejeter/supprimer).</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800">
            ← Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 text-sm bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <form method="GET" action="{{ route('admin.annonces.index') }}" class="space-y-3">
            {{-- Recherche --}}
            <div class="flex gap-2">
                <input type="text"
                       name="q"
                       value="{{ $filters['q'] ?? '' }}"
                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                       placeholder="Rechercher (titre, marque, modèle, vendeur, email)">
                <button type="submit" class="rounded-lg bg-gray-800 text-white text-sm font-semibold px-6 py-2 hover:bg-gray-900 shrink-0">
                    Rechercher
                </button>
            </div>

            {{-- Filtres avancés --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                {{-- Statut --}}
                <div>
                    <label class="block text-[10px] font-semibold text-gray-600 mb-1">STATUT</label>
                    <select name="status" id="status-filter" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-gray-800">
                        <option value="all" {{ ($status ?? 'all')==='all' ? 'selected' : '' }}>Tous</option>
                        <option value="pending" {{ ($status ?? '')==='pending' ? 'selected' : '' }}>⧖ En attente</option>
                        <option value="active" {{ ($status ?? '')==='active' ? 'selected' : '' }}>✓ Activées</option>
                        <option value="inactive" {{ ($status ?? '')==='inactive' ? 'selected' : '' }}>✗ Désactivées</option>
                    </select>
                </div>

                {{-- Marque --}}
                <div>
                    <label class="block text-[10px] font-semibold text-gray-600 mb-1">MARQUE</label>
                    <select name="marque" id="marque-filter" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-gray-800">
                        <option value="">Toutes</option>
                        @if(isset($marques))
                            @foreach($marques as $m)
                                <option value="{{ $m }}" {{ ($marque ?? '')===$m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Carburant --}}
                <div>
                    <label class="block text-[10px] font-semibold text-gray-600 mb-1">CARBURANT</label>
                    <select name="carburant" id="carburant-filter" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-gray-800">
                        <option value="">Tous</option>
                        <option value="Essence" {{ ($carburant ?? '')==='Essence' ? 'selected' : '' }}>Essence</option>
                        <option value="Diesel" {{ ($carburant ?? '')==='Diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="Hybride" {{ ($carburant ?? '')==='Hybride' ? 'selected' : '' }}>Hybride</option>
                        <option value="Électrique" {{ ($carburant ?? '')==='Électrique' ? 'selected' : '' }}>Électrique</option>
                        <option value="GPL" {{ ($carburant ?? '')==='GPL' ? 'selected' : '' }}>GPL</option>
                    </select>
                </div>

                {{-- Tri --}}
                <div>
                    <label class="block text-[10px] font-semibold text-gray-600 mb-1">TRIER PAR</label>
                    <select name="sort" id="sort-filter" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-gray-800">
                        <option value="recent" {{ ($sort ?? 'recent')==='recent' ? 'selected' : '' }}>Plus récentes</option>
                        <option value="oldest" {{ ($sort ?? '')==='oldest' ? 'selected' : '' }}>Plus anciennes</option>
                        <option value="price_high" {{ ($sort ?? '')==='price_high' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="price_low" {{ ($sort ?? '')==='price_low' ? 'selected' : '' }}>Prix croissant</option>
                    </select>
                </div>
            </div>

            {{-- Bouton reset --}}
            @if(($q ?? '') || ($status ?? 'all') !== 'all' || ($marque ?? '') || ($carburant ?? '') || ($sort ?? 'recent') !== 'recent')
                <div class="flex justify-end">
                    <a href="{{ route('admin.annonces.index') }}" class="text-xs text-gray-600 hover:text-gray-800 underline">
                        Réinitialiser les filtres
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="py-2 px-3 w-10">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300">
                        </th>
                        <th class="text-left py-2 px-3">Annonce</th>
                        <th class="text-left py-2 px-3">Vendeur</th>
                        <th class="text-left py-2 px-3">Prix</th>
                        <th class="text-center py-2 px-3">Vues</th>
                        <th class="text-center py-2 px-3">Statut</th>
                        <th class="text-center py-2 px-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($annonces as $a)
                        @php
                            $views = $a->views ?? 0;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3">
                                <input type="checkbox" name="selected_ids[]" value="{{ $a->id }}" class="annonce-checkbox rounded border-gray-300">
                            </td>

                            <td class="py-2 px-3">
                                <div class="font-semibold text-gray-900 text-xs">{{ Str::limit($a->titre, 40) }}</div>
                                <div class="text-[10px] text-gray-500">
                                    {{ $a->marque }} • {{ $a->modele }} • {{ optional($a->created_at)->diffForHumans() }}
                                </div>
                                <a href="{{ route('annonces.show', $a->id) }}" target="_blank" class="text-[10px] text-gray-800 hover:underline">
                                    Voir →
                                </a>
                            </td>

                            <td class="py-2 px-3">
                                <div class="font-semibold text-xs">{{ Str::limit(optional($a->user)->name ?? '—', 20) }}</div>
                                <div class="text-[10px] text-gray-500">{{ Str::limit(optional($a->user)->email ?? '', 25) }}</div>
                            </td>

                            <td class="py-2 px-3 font-bold text-gray-800 text-xs whitespace-nowrap">
                                {{ number_format($a->prix, 0, ',', ' ') }} DA
                            </td>

                            <td class="py-2 px-3 text-center text-xs text-gray-600">
                                {{ $views }}
                            </td>

                            <td class="py-2 px-3 text-center">
                                @if($a->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] bg-green-50 text-green-700 font-semibold">✓ Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] bg-yellow-50 text-yellow-700 font-semibold">⧖ Attente</span>
                                @endif
                            </td>

                            <td class="py-2 px-3">
                                <div class="flex items-center justify-center gap-1">
                                    <form method="POST" action="{{ route('admin.annonces.toggle', $a) }}" id="toggle-form-{{ $a->id }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <button type="submit" form="toggle-form-{{ $a->id }}" class="px-2 py-1 rounded-lg border text-[10px] font-semibold
                                        {{ $a->is_active ? 'border-gray-200 text-gray-700 hover:border-gray-800 hover:text-gray-800' : 'border-green-500 text-green-600 hover:bg-green-50' }}">
                                        {{ $a->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>

                                    <form method="POST" action="{{ route('admin.annonces.destroy', $a) }}" id="delete-form-{{ $a->id }}" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement cette annonce ?')">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="submit" form="delete-form-{{ $a->id }}" class="px-2 py-1 rounded-lg border border-red-200 text-red-600 text-[10px] font-semibold hover:bg-red-50">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($annonces->isEmpty())
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500 text-sm">
                                Aucune annonce trouvée.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                <span id="selected-count">0</span> sélectionnée(s) • 
                {{ $annonces->total() }} résultat(s)
            </div>

            <div id="bulk-actions" class="hidden flex gap-2">
                <select id="bulk-action-select" class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-gray-800">
                    <option value="">— Action groupée —</option>
                    <option value="approve">Activer</option>
                    <option value="reject">Désactiver</option>
                    <option value="delete">Supprimer</option>
                </select>
                <button type="button" id="bulk-submit-btn" class="px-4 py-1 rounded-lg bg-gray-800 text-white text-xs font-semibold hover:bg-gray-900">
                    Appliquer
                </button>
            </div>

            {{ $annonces->links() }}
        </div>
    </div>

    {{-- Formulaire caché pour les actions groupées --}}
    <form method="POST" action="{{ route('admin.annonces.bulkAction') }}" id="bulk-form" style="display: none;">
        @csrf
        <input type="hidden" name="bulk_action" id="bulk-action-input">
        <div id="bulk-ids-container"></div>
    </form>

    <script>
document.addEventListener('DOMContentLoaded', () => {

    // Bulk actions
    const selectAllCheckbox = document.getElementById('select-all');
    const annonceCheckboxes = document.querySelectorAll('.annonce-checkbox');
    const selectedCount = document.getElementById('selected-count');
    const bulkActions = document.getElementById('bulk-actions');
    const bulkForm = document.getElementById('bulk-form');
    const bulkActionSelect = document.getElementById('bulk-action-select');
    const bulkSubmitBtn = document.getElementById('bulk-submit-btn');
    const bulkActionInput = document.getElementById('bulk-action-input');
    const bulkIdsContainer = document.getElementById('bulk-ids-container');

    function updateUI() {
        const checked = document.querySelectorAll('.annonce-checkbox:checked').length;
        selectedCount.textContent = checked;
        bulkActions.classList.toggle('hidden', checked === 0);
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', () => {
            annonceCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateUI();
        });
    }

    annonceCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = Array.from(annonceCheckboxes).every(c => c.checked);
            }
            updateUI();
        });
    });

    if (bulkSubmitBtn) {
        bulkSubmitBtn.addEventListener('click', (e) => {
            const action = bulkActionSelect.value;
            if (!action) {
                alert('Veuillez choisir une action.');
                return;
            }

            const checkedBoxes = document.querySelectorAll('.annonce-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Veuillez sélectionner au moins une annonce.');
                return;
            }

            if (action === 'delete' && !confirm('Supprimer définitivement les annonces sélectionnées et toutes leurs images ?')) {
                return;
            }

            // Ajouter l'action au formulaire
            bulkActionInput.value = action;

            // Ajouter les IDs sélectionnés
            bulkIdsContainer.innerHTML = '';
            checkedBoxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = cb.value;
                bulkIdsContainer.appendChild(input);
            });

            // Soumettre le formulaire
            bulkForm.submit();
        });
    }
});
    </script>

</div>
@endsection
