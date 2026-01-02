@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold">Gérer mon profil</h1>
        <p class="text-sm text-gray-500">Mettez à jour vos informations de compte et sécurité.</p>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="mb-4 text-sm bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3">
            Vos informations ont été mises à jour.
        </div>
    @endif

    <div class="space-y-4">
        {{-- Informations du profil --}}
        <div class="bg-white rounded-xl shadow p-4">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Mot de passe --}}
        <div class="bg-white rounded-xl shadow p-4">
            @include('profile.partials.update-password-form')
        </div>

        {{-- Suppression du compte --}}
        <div class="bg-white rounded-xl shadow p-4">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
