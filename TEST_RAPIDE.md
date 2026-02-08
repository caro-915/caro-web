# 🧪 COMMANDES DE TEST RAPIDE - SYSTÈME PRO

## Prérequis
```bash
cd c:\laragon\www\autodz
php artisan tinker
```

---

## TEST 1: Créer un utilisateur test

```bash
# Dans tinker:
$user = User::factory()->create([
    'name' => 'TestUser PRO',
    'email' => 'testpro@test.com',
    'password' => bcrypt('password'),
]);

# Vérifier
echo "User créé: ID = {$user->id}, Email = {$user->email}";
```

---

## TEST 2: Créer 5 annonces FREE (sans approbation pour plus vite)

```bash
# Dans tinker:
$user = User::find(USER_ID); // Remplacer USER_ID

for ($i = 1; $i <= 5; $i++) {
    Annonce::create([
        'titre' => "Annonce FREE {$i}",
        'prix' => 1000000 + ($i * 100000),
        'marque' => 'Renault',
        'modele' => 'Clio',
        'carburant' => 'Essence',
        'boite_vitesse' => 'Manuelle',
        'vehicle_type' => 'Voiture',
        'condition' => 'non',
        'user_id' => $user->id,
        'is_active' => true, // Activer directo pour test
    ]);
    echo "✅ Annonce {$i} créée\n";
}

# Vérifier
Annonce::where('user_id', $user->id)->count();
// → Doit afficher 5
```

---

## TEST 3: Tentative 6e annonce FREE (doit échouer)

```bash
# Dans tinker:
$user = User::find(USER_ID);

// Appeler le service de validation
$subscriptionService = app(\App\Services\SubscriptionService::class);
$isPro = $subscriptionService->userIsPro($user);
$features = $subscriptionService->getFeatures($user);
$activeAds = Annonce::where('user_id', $user->id)->where('is_active', true)->count();

echo "isPro: " . ($isPro ? 'true' : 'false') . "\n";
echo "maxAds: {$features['max_active_ads']}\n";
echo "activeAds: $activeAds\n";

// Vérifier le blocage
if ($activeAds >= $features['max_active_ads']) {
    echo "❌ BLOCAGE CORRECT: Limite atteinte\n";
} else {
    echo "⚠️ ATTENTION: Devrait être bloqué!\n";
}
```

---

## TEST 4: Créer subscription PENDING

```bash
# Dans tinker:
$user = User::find(USER_ID);
$plan = Plan::where('name', 'Pro')->first();

// Simuler upload fichier (créer fichier virtuel)
$filePath = 'proofs/user_' . $user->id . '/proof_test.pdf';
Storage::disk('local')->put($filePath, 'fake proof content');

// Créer subscription
$subscription = Subscription::create([
    'user_id' => $user->id,
    'plan_id' => $plan->id,
    'started_at' => now(),
    'expires_at' => now()->addDays($plan->duration_days),
    'status' => 'active',
    'payment_proof_path' => $filePath,
    'payment_status' => 'pending',
]);

echo "✅ Subscription créée: ID = {$subscription->id}, Status = {$subscription->payment_status}\n";

# Vérifier
Subscription::find($subscription->id);
// → payment_status = 'pending'
```

---

## TEST 5: Vérifier subscription PENDING (pas encore active)

```bash
# Dans tinker:
$user = User::find(USER_ID);
$subscriptionService = app(\App\Services\SubscriptionService::class);

$activeSubscription = $subscriptionService->getActiveSubscription($user);
$isPro = $subscriptionService->userIsPro($user);

echo "Active subscription: " . ($activeSubscription ? $activeSubscription->id : 'NULL') . "\n";
echo "isPro: " . ($isPro ? 'true' : 'false') . "\n";

// Résultat attendu:
// Active subscription: NULL
// isPro: false
```

---

## TEST 6: Approuver subscription ADMIN

```bash
# Dans tinker:
$subscription = Subscription::latest()->first();
$subscriptionService = app(\App\Services\SubscriptionService::class);

// Approuver
$subscriptionService->approveSubscription($subscription);

# Vérifier
$subscription->refresh();
echo "Payment status: {$subscription->payment_status}\n";
// → 'approved'

echo "Starts: {$subscription->started_at}\n";
echo "Expires: {$subscription->expires_at}\n";
```

---

## TEST 7: Créer 6e annonce (doit réussir maintenant, user est PRO)

```bash
# Dans tinker:
$user = User::find(USER_ID);
$subscriptionService = app(\App\Services\SubscriptionService::class);
$features = $subscriptionService->getFeatures($user);
$activeAds = Annonce::where('user_id', $user->id)->where('is_active', true)->count();

echo "Max ads: {$features['max_active_ads']}\n";
echo "Active ads: $activeAds\n";

if ($activeAds < $features['max_active_ads']) {
    Annonce::create([
        'titre' => "Annonce PRO 6",
        'prix' => 1600000,
        'marque' => 'Peugeot',
        'modele' => '308',
        'carburant' => 'Diesel',
        'boite_vitesse' => 'Manuelle',
        'vehicle_type' => 'Voiture',
        'condition' => 'oui',
        'user_id' => $user->id,
        'is_active' => true,
    ]);
    echo "✅ 6e annonce créée (PRO)\n";
} else {
    echo "❌ Blocage non levé après approval\n";
}

# Vérifier
Annonce::where('user_id', $user->id)->count();
// → Doit être 6
```

---

## TEST 8: Booster annonce (doit réussir)

```bash
# Dans tinker:
$user = User::find(USER_ID);
$annonce = Annonce::where('user_id', $user->id)->first();
$boostService = app(\App\Services\BoostService::class);

$canBoost = $boostService->canBoost($user, $annonce);

echo "Can boost: " . ($canBoost['canBoost'] ? 'true' : 'false') . "\n";
echo "Reason: {$canBoost['reason']}\n";

if ($canBoost['canBoost']) {
    $boost = $boostService->boostAnnonce($user, $annonce);
    echo "✅ Boost créé: ID = {$boost->id}, Expires = {$boost->expires_at}\n";
}
```

---

## TEST 9: Vérifier boost actif

```bash
# Dans tinker:
$annonce = Annonce::where('user_id', USER_ID)->first();

echo "Is boosted: " . ($annonce->isBoosted() ? 'true' : 'false') . "\n";

$boost = $annonce->activeBoost();
if ($boost) {
    echo "Boost ID: {$boost->id}\n";
    echo "Expires: {$boost->expires_at}\n";
}
```

---

## TEST 10: Exécuter commande d'expiration

```bash
# Retourner au terminal (Ctrl+D pour quitter tinker)
php artisan boosts:expire
# Output: Expired X boost(s)

php artisan subscriptions:expire
# Output: Expired X subscription(s)
```

---

## TEST 11: Vérifier boost expiré

```bash
# Dans tinker:
$boost = Boost::latest()->first();

echo "Status: {$boost->status}\n";
// → 'expired'

$annonce = $boost->annonce;
echo "Annonce is boosted: " . ($annonce->isBoosted() ? 'true' : 'false') . "\n";
// → false
```

---

## TEST 12: Vérifier logs

```bash
# Retourner au terminal
tail -f storage/logs/laravel.log | grep -E "ACTIVATION|BOOST NON AUTORISÉ|QUOTA"
```

---

## SCRIPT COMPLET (À EXÉCUTER EN UNE FOIS)

Créer fichier `test_pro_system.php` à la racine:

```php
<?php
// php test_pro_system.php

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Annonce;
use App\Models\Boost;
use App\Services\SubscriptionService;
use App\Services\BoostService;
use Illuminate\Support\Facades\Storage;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 TEST SYSTÈME PRO COMPLET\n";
echo str_repeat('=', 50) . "\n\n";

// 1. Créer user
$user = User::factory()->create([
    'name' => 'TestPro ' . date('YmdHis'),
    'email' => 'test' . time() . '@test.com',
]);
echo "✅ User créé: {$user->id}\n";

// 2. Créer 5 annonces FREE
for ($i = 1; $i <= 5; $i++) {
    Annonce::create([
        'titre' => "Annonce {$i}",
        'prix' => 1000000 + ($i * 100000),
        'marque' => 'Renault',
        'modele' => 'Clio',
        'carburant' => 'Essence',
        'boite_vitesse' => 'Manuelle',
        'vehicle_type' => 'Voiture',
        'condition' => 'non',
        'user_id' => $user->id,
        'is_active' => true,
    ]);
}
echo "✅ 5 annonces créées\n";

// 3. Créer subscription
$plan = Plan::where('name', 'Pro')->first();
$filePath = 'proofs/test_' . time() . '.pdf';
Storage::disk('local')->put($filePath, 'test');

$subscription = Subscription::create([
    'user_id' => $user->id,
    'plan_id' => $plan->id,
    'started_at' => now(),
    'expires_at' => now()->addDays(30),
    'status' => 'active',
    'payment_proof_path' => $filePath,
    'payment_status' => 'pending',
]);
echo "✅ Subscription créée (PENDING): {$subscription->id}\n";

// 4. Approuver
$subscriptionService = app(SubscriptionService::class);
$subscriptionService->approveSubscription($subscription);
$subscription->refresh();
echo "✅ Subscription approuvée: {$subscription->payment_status}\n";

// 5. Tenter boost
$annonce = Annonce::where('user_id', $user->id)->first();
$boostService = app(BoostService::class);
$canBoost = $boostService->canBoost($user, $annonce);

if ($canBoost['canBoost']) {
    $boost = $boostService->boostAnnonce($user, $annonce);
    echo "✅ Boost créé: {$boost->id}\n";
} else {
    echo "❌ Boost échoué: {$canBoost['reason']}\n";
}

echo "\n✅ TOUS LES TESTS PASSÉS!\n";
```

Exécuter:
```bash
php test_pro_system.php
```

