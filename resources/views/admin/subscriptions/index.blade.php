@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('admin.dashboard') }}" class="text-pink-600 hover:text-pink-700 font-semibold">← Retour au tableau de bord</a>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Abonnements PRO</h1>
                <p class="text-gray-600 mt-1">Gérez les demandes d'abonnement</p>
            </div>
            <a href="{{ route('admin.plans.index') }}" class="py-2 px-4 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                Gérer les plans
            </a>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="flex space-x-8">
                <button onclick="switchTab('pending')" id="pendingTab" class="py-4 px-1 border-b-2 border-pink-600 text-pink-600 font-semibold focus:outline-none">
                    En attente de vérification
                </button>
                <button onclick="switchTab('approved')" id="approvedTab" class="py-4 px-1 border-b-2 border-transparent text-gray-600 font-semibold hover:text-gray-900 hover:border-gray-300 focus:outline-none">
                    Approuvés
                </button>
            </nav>
        </div>

        <!-- Pending Subscriptions -->
        <div id="pendingContent">
            @if($pendingSubscriptions->count() > 0)
                <div class="space-y-4">
                    @foreach($pendingSubscriptions as $subscription)
                        <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-gray-600 text-sm">Utilisateur</p>
                                    <p class="font-semibold text-gray-900">{{ $subscription->user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $subscription->user->email }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Plan</p>
                                    <p class="font-semibold text-gray-900">{{ $subscription->plan->name }}</p>
                                    <p class="text-sm text-pink-600">{{ number_format($subscription->plan->price, 0, ',', ' ') }} DZD</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Date</p>
                                    <p class="font-semibold text-gray-900">{{ $subscription->created_at->format('d/m/Y') }}</p>
                                    <p class="text-sm text-gray-600">{{ $subscription->created_at->format('H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Statut</p>
                                    <p class="font-semibold text-yellow-900">En attente</p>
                                </div>
                            </div>

                            <!-- Proof Preview (Icon Only) -->
                            <div class="mb-4">
                                @if($subscription->payment_proof_path)
                                    @if(str_ends_with(strtolower($subscription->payment_proof_path), '.pdf'))
                                        <a href="{{ route('admin.subscriptions.proof', $subscription->id) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 font-semibold transition">
                                            📄 Voir le PDF
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . $subscription->payment_proof_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 font-semibold transition">
                                            🖼️ Voir l'image
                                        </a>
                                    @endif
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 pt-4 border-t border-yellow-200">
                                <form action="{{ route('admin.subscriptions.approve', $subscription->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="py-2 px-4 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-900 transition">
                                        ✓ Approuver
                                    </button>
                                </form>

                                <button onclick="openRejectModal({{ $subscription->id }})" class="py-2 px-4 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                                    ✗ Rejeter
                                </button>

                                <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="py-2 px-4 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                                    Détails
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $pendingSubscriptions->links() }}
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <p class="text-gray-600">Aucune demande en attente de vérification</p>
                </div>
            @endif
        </div>

        <!-- Approved Subscriptions -->
        <div id="approvedContent" class="hidden">
            @if($approvedSubscriptions->count() > 0)
                <div class="space-y-4">
                    @foreach($approvedSubscriptions as $subscription)
                        <div class="border border-green-200 bg-green-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-gray-600 text-sm">Utilisateur</p>
                                    <p class="font-semibold text-gray-900">{{ $subscription->user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $subscription->user->email }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Plan</p>
                                    <p class="font-semibold text-gray-900">{{ $subscription->plan->name }}</p>
                                    <p class="text-sm text-pink-600">{{ number_format($subscription->plan->price, 0, ',', ' ') }} DZD</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Date d'expiration</p>
                                    <p class="font-semibold text-gray-900">{{ $subscription->expires_at->format('d/m/Y') }}</p>
                                    @if($subscription->expires_at->isFuture())
                                        <p class="text-sm text-green-600">dans {{ $subscription->expires_at->diffInDays(now()) }} jours</p>
                                    @else
                                        <p class="text-sm text-red-600">Expiré</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm">Statut</p>
                                    <span class="inline-block px-3 py-1 bg-green-200 text-green-900 rounded-full text-sm font-semibold">
                                        {{ $subscription->status === 'active' && $subscription->expires_at->isFuture() ? 'Actif' : 'Expiré' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $approvedSubscriptions->links() }}
                </div>
            @else
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <p class="text-gray-600">Aucun abonnement approuvé</p>
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Rejeter la demande</h2>
        
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="reason" class="block text-sm font-semibold text-gray-900 mb-2">
                    Raison du rejet
                </label>
                <textarea name="reason" id="reason" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" placeholder="Décrivez pourquoi vous rejetez cette demande..." required></textarea>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-2 px-4 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit" class="flex-1 py-2 px-4 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
                    Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentRejectingSubscriptionId = null;

    function switchTab(tab) {
        // Hide all content
        document.getElementById('pendingContent').classList.add('hidden');
        document.getElementById('approvedContent').classList.add('hidden');

        // Remove active state from all tabs
        document.getElementById('pendingTab').classList.remove('border-pink-600', 'text-pink-600');
        document.getElementById('approvedTab').classList.remove('border-pink-600', 'text-pink-600');

        // Show selected content and activate tab
        if (tab === 'pending') {
            document.getElementById('pendingContent').classList.remove('hidden');
            document.getElementById('pendingTab').classList.add('border-pink-600', 'text-pink-600');
        } else {
            document.getElementById('approvedContent').classList.remove('hidden');
            document.getElementById('approvedTab').classList.add('border-pink-600', 'text-pink-600');
        }
    }

    function openRejectModal(subscriptionId) {
        currentRejectingSubscriptionId = subscriptionId;
        const form = document.getElementById('rejectForm');
        form.action = `/admin/subscriptions/${subscriptionId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
</script>
@endsection
