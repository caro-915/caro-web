@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 md:py-8">

    <div class="flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold">Admin • Utilisateurs</h1>
            <p class="text-sm text-gray-500 mt-1">Gérer les comptes</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:underline">← Dashboard</a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="bg-white rounded-2xl shadow p-4 mb-5 flex gap-3">
        <input type="text" name="q" value="{{ $q }}"
               class="w-full border rounded-xl px-3 py-2 text-sm"
               placeholder="Rechercher (nom, email)">
        <button class="px-4 py-2 rounded-xl bg-pink-600 text-white text-sm font-semibold hover:bg-pink-700">
            Rechercher
        </button>
    </form>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Utilisateur</th>
                        <th class="px-4 py-3 text-left">Annonces</th>
                        <th class="px-4 py-3 text-left">Rôle</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($users as $u)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold">{{ $u->name }}</div>
                                <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                <div class="text-xs text-gray-400">Inscrit {{ $u->created_at?->diffForHumans() }}</div>
                            </td>

                            <td class="px-4 py-3 font-semibold">
                                {{ $u->annonces_count }}
                            </td>

                            <td class="px-4 py-3">
                                @if($u->is_admin)
                                    <span class="text-xs px-2 py-1 rounded-full bg-pink-50 text-pink-700 font-semibold">Admin</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold">User</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                @if($u->is_banned)
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-50 text-red-700 font-semibold">Bloqué</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-700 font-semibold">Actif</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right space-x-3">
                                <form method="POST" action="{{ route('admin.users.toggleAdmin', $u) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-xs font-semibold text-pink-600 hover:text-pink-700"
                                            onclick="return confirm('Changer le rôle admin de cet utilisateur ?');">
                                        {{ $u->is_admin ? 'Retirer admin' : 'Promouvoir admin' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.toggleBan', $u) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button class="text-xs font-semibold {{ $u->is_banned ? 'text-green-600 hover:text-green-700' : 'text-red-600 hover:text-red-700' }}"
                                            onclick="return confirm('Changer le statut de cet utilisateur ?');">
                                        {{ $u->is_banned ? 'Débloquer' : 'Bloquer' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($users->isEmpty())
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                Aucun utilisateur
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

</div>
@endsection
