@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Créer un plan PRO</h1>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.plans.store') }}" method="POST" class="bg-white rounded-lg shadow p-8">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">
                    Nom du plan
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                    placeholder="Ex: Pro, Premium, Platinum..." required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Price -->
            <div class="mb-6">
                <label for="price" class="block text-sm font-semibold text-gray-900 mb-2">
                    Prix (DZD)
                </label>
                <input type="number" id="price" name="price" value="{{ old('price') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                    placeholder="3000" min="0" step="100" required>
                @error('price')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Duration -->
            <div class="mb-6">
                <label for="duration_days" class="block text-sm font-semibold text-gray-900 mb-2">
                    Durée (jours)
                </label>
                <input type="number" id="duration_days" name="duration_days" value="{{ old('duration_days', '30') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                    placeholder="30" min="1" required>
                @error('duration_days')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Features -->
            <fieldset class="mb-8 border border-gray-200 rounded-lg p-6">
                <legend class="text-lg font-semibold text-gray-900 mb-4">Fonctionnalités</legend>

                <div class="mb-4">
                    <label for="max_active_ads" class="block text-sm font-semibold text-gray-900 mb-2">
                        Nombre maximum d'annonces actives
                    </label>
                    <input type="number" id="max_active_ads" name="max_active_ads" value="{{ old('max_active_ads', '10') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                        placeholder="10" min="1" required>
                    @error('max_active_ads')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="max_images_per_ad" class="block text-sm font-semibold text-gray-900 mb-2">
                        Nombre maximum d'images par annonce
                    </label>
                    <input type="number" id="max_images_per_ad" name="max_images_per_ad" value="{{ old('max_images_per_ad', '4') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                        placeholder="4" min="1" max="8" required>
                    @error('max_images_per_ad')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="boosts_per_month" class="block text-sm font-semibold text-gray-900 mb-2">
                        Boosts par mois
                    </label>
                    <input type="number" id="boosts_per_month" name="boosts_per_month" value="{{ old('boosts_per_month', '5') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                        placeholder="5" min="0" required>
                    @error('boosts_per_month')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="boost_duration_days" class="block text-sm font-semibold text-gray-900 mb-2">
                        Durée d'un boost (jours)
                    </label>
                    <input type="number" id="boost_duration_days" name="boost_duration_days" value="{{ old('boost_duration_days', '7') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" 
                        placeholder="7" min="1" required>
                    @error('boost_duration_days')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="flex gap-4">
                <a href="{{ route('admin.plans.index') }}" class="flex-1 py-3 px-4 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition text-center">
                    Annuler
                </a>
                <button type="submit" class="flex-1 py-3 px-4 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                    Créer le plan
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
