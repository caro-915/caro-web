@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <div class="mb-8">
            <a href="{{ route('pro.index') }}" class="text-pink-600 hover:text-pink-700 font-semibold">← Retour aux plans</a>
        </div>

        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Abonnement <span class="text-pink-600">{{ $plan->name }}</span>
            </h1>
            <p class="text-gray-600">{{ $plan->duration_days }} jours d'accès à toutes les fonctionnalités PRO</p>
        </div>

        <!-- Plan Details -->
        <div class="bg-pink-50 rounded-lg p-6 mb-8 border border-pink-200">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600 text-sm">Montant à payer</p>
                    <p class="text-2xl font-bold text-pink-600">{{ number_format($plan->price, 0, ',', ' ') }} DZD</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Durée</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $plan->duration_days }} jours</p>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-blue-900 mb-4">Instructions de paiement</h2>
            <ol class="space-y-3 text-blue-900">
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-blue-600 text-white rounded-full text-sm font-semibold mr-3">1</span>
                    <span>Effectuez un transfert bancaire ou Mobile Money de <strong>{{ number_format($plan->price, 0, ',', ' ') }} DZD</strong></span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-blue-600 text-white rounded-full text-sm font-semibold mr-3">2</span>
                    <span>Conservez la preuve de paiement (capture d'écran ou reçu)</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-blue-600 text-white rounded-full text-sm font-semibold mr-3">3</span>
                    <span>Téléchargez la preuve ci-dessous</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 h-6 w-6 flex items-center justify-center bg-blue-600 text-white rounded-full text-sm font-semibold mr-3">4</span>
                    <span>Nous vérifierons dans les 24 heures et activerons votre abonnement</span>
                </li>
            </ol>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('pro.subscribe', $plan->id) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8">
            @csrf

            <!-- Payment Proof Upload -->
            <div class="mb-6">
                <label for="payment_proof" class="block text-sm font-semibold text-gray-900 mb-2">
                    Preuve de paiement
                </label>
                
                <div class="mt-1 relative">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-pink-600 transition cursor-pointer" id="dropZone">
                        <div id="fileInputContainer">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-12l-3.172-3.172a4 4 0 00-5.656 0L28 12M9 20l3.172-3.172a4 4 0 015.656 0L28 28" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <p class="text-gray-700 font-semibold mb-1">Glissez votre fichier ici</p>
                            <p class="text-gray-500 text-sm">ou cliquez pour le sélectionner</p>
                            <p class="text-gray-400 text-xs mt-2">JPG, PNG ou PDF (Max 5 MB)</p>
                        </div>
                        
                        <input 
                            type="file" 
                            id="payment_proof" 
                            name="payment_proof" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            accept="image/jpeg,image/png,application/pdf"
                            required
                        >
                    </div>

                    <div id="selectedFile" class="mt-4 hidden">
                        <p class="text-sm text-gray-600 mb-2">Fichier sélectionné :</p>
                        <div class="flex items-center bg-green-50 border border-green-200 rounded p-3">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            <span id="fileName" class="text-green-900 font-semibold"></span>
                        </div>
                    </div>
                </div>

                @error('payment_proof')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Terms & Conditions -->
            <div class="mb-6">
                <label class="flex items-start">
                    <input type="checkbox" name="accept_terms" class="mt-1 rounded border-gray-300" required>
                    <span class="ml-3 text-sm text-gray-600">
                        Je confirme que :
                        <ul class="list-disc list-inside mt-2 ml-2 space-y-1">
                            <li>Je suis la personne mentionnée sur la preuve de paiement</li>
                            <li>Le montant payé correspond au plan choisi</li>
                            <li>J'ai lu et accepté les conditions d'utilisation</li>
                        </ul>
                    </span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <a href="{{ route('pro.index') }}" class="flex-1 py-3 px-4 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50 transition text-center">
                    Annuler
                </a>
                <button type="submit" class="flex-1 py-3 px-4 bg-pink-600 text-white rounded-lg font-semibold hover:bg-pink-700 transition">
                    Soumettre la preuve de paiement
                </button>
            </div>
        </form>

        <!-- Security Notice -->
        <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-green-900 text-sm">
                <svg class="inline w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
                Votre paiement est sécurisé et confidentiel
            </p>
        </div>

    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('payment_proof');
    const selectedFileDiv = document.getElementById('selectedFile');
    const fileNameSpan = document.getElementById('fileName');
    const fileInputContainer = document.getElementById('fileInputContainer');

    // Click to select
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-pink-600', 'bg-pink-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-pink-600', 'bg-pink-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-pink-600', 'bg-pink-50');
        fileInput.files = e.dataTransfer.files;
        handleFileSelect();
    });

    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileNameSpan.textContent = file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
            fileInputContainer.classList.add('hidden');
            selectedFileDiv.classList.remove('hidden');
        }
    }
</script>
@endsection
