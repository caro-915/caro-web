@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-lg mx-auto px-4 text-center">
        <div class="bg-red-50 border border-red-200 rounded-lg p-8">
            <div class="text-5xl mb-4">⚠️</div>
            <h1 class="text-2xl font-bold text-red-800 mb-3">Preuve de paiement introuvable</h1>
            <p class="text-red-700 mb-4">
                Le fichier de preuve pour l'abonnement #{{ $subscription->id }} n'est plus disponible.
                Il a probablement été perdu lors d'un redéploiement du serveur.
            </p>
            <p class="text-red-600 text-sm mb-6">
                Chemin enregistré : <code class="bg-red-100 px-1 rounded">{{ $subscription->payment_proof_path }}</code>
            </p>
            <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-900 transition">
                ← Retour aux abonnements
            </a>
        </div>
    </div>
</div>
@endsection
