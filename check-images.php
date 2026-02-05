#!/usr/bin/env php
<?php
/**
 * Script de vérification des images
 * Teste si les images sont accessibles et affiche les URLs générées
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Annonce;
use Illuminate\Support\Facades\Storage;

echo "=== Vérification des Images Autodz ===\n\n";

// Configuration actuelle
$disk = config('filesystems.default', 'public');
echo "📁 Disque de stockage: $disk\n";
echo "🌐 APP_URL: " . config('app.url') . "\n\n";

// Récupérer quelques annonces avec images
$annonces = Annonce::whereNotNull('image_path')
    ->where('is_active', true)
    ->take(5)
    ->get();

if ($annonces->isEmpty()) {
    echo "⚠️  Aucune annonce avec image trouvée.\n";
    exit(0);
}

echo "🔍 Vérification de {$annonces->count()} annonces:\n\n";

foreach ($annonces as $annonce) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📋 Annonce #{$annonce->id}: {$annonce->titre}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Vérifier chaque slot d'image
    $slots = ['image_path', 'image_path_2', 'image_path_3', 'image_path_4', 'image_path_5'];
    
    foreach ($slots as $slot) {
        if (!empty($annonce->$slot)) {
            $path = $annonce->$slot;
            $cleanPath = ltrim($path, '/');
            $cleanPath = preg_replace('#^storage/#', '', $cleanPath);
            
            // Vérifier si le fichier existe
            $exists = Storage::disk($disk)->exists($cleanPath);
            
            // Générer l'URL
            if ($disk !== 'public' && $disk !== 'local') {
                $url = Storage::disk($disk)->url($cleanPath);
            } else {
                $url = asset('storage/' . $cleanPath);
            }
            
            $status = $exists ? '✅' : '❌';
            echo "$status $slot: $path\n";
            echo "   📍 URL: $url\n";
            
            if (!$exists) {
                echo "   ⚠️  FICHIER INTROUVABLE!\n";
            }
        }
    }
    echo "\n";
}

// Statistiques
$totalImages = Annonce::whereNotNull('image_path')->count();
echo "\n📊 Statistiques:\n";
echo "   Total d'annonces avec images: $totalImages\n";

// Test de connexion S3 si configuré
if ($disk === 's3') {
    echo "\n🔐 Test de connexion S3...\n";
    try {
        $files = Storage::disk('s3')->files('annonces');
        echo "   ✅ Connexion S3 réussie (" . count($files) . " fichiers trouvés)\n";
    } catch (\Exception $e) {
        echo "   ❌ Erreur S3: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Fin de la vérification ===\n";
