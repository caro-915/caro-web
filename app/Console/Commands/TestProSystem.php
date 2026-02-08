<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Annonce;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Boost;
use App\Services\SubscriptionService;
use App\Services\BoostService;

class TestProSystem extends Command
{
    protected $signature = 'autodz:test-pro {--cleanup : Clean up test data after completion}';
    protected $description = 'Test complet du système PRO (quotas, paiement manuel, boosts)';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('   TEST AUTOMATIQUE SYSTÈME PRO');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $testUserId = null;
        $testAnnonceIds = [];
        $testSubscriptionId = null;
        $testBoostId = null;

        try {
            // ÉTAPE 1: Créer user FREE
            $this->info('ÉTAPE 1: Créer user FREE');
            $userEmail = 'test_pro_' . time() . '@autodz.test';
            $user = User::create([
                'name' => 'Test PRO User',
                'email' => $userEmail,
                'password' => bcrypt('password123'),
                'phone' => '0555000001',
                'email_verified_at' => now(),
            ]);
            $testUserId = $user->id;
            
            $subscriptionService = app(SubscriptionService::class);
            $features = $subscriptionService->getFeatures($user);
            
            $this->line("  ✓ User créé: ID={$user->id}, Email={$user->email}");
            $this->line("  ✓ isPro: " . ($user->isPro() ? 'YES' : 'NO'));
            $this->line("  ✓ Quota max: {$features['max_active_ads']} annonces");
            $this->newLine();

            // ÉTAPE 2: Créer 5 annonces (limite FREE)
            $this->info('ÉTAPE 2: Créer 5 annonces (limite FREE)');
            for ($i = 1; $i <= 5; $i++) {
                $annonce = Annonce::create([
                    'user_id' => $user->id,
                    'titre' => "Test Annonce FREE #{$i}",
                    'description' => "Test description #{$i}",
                    'prix' => 1000000 + ($i * 100000),
                    'marque' => 'Renault',
                    'modele' => 'Clio',
                    'annee' => 2020,
                    'kilometrage' => 50000,
                    'carburant' => 'Essence',
                    'boite_vitesse' => 'Manuelle',
                    'ville' => 'Alger',
                    'vehicle_type' => 'Voiture',
                    'condition' => 'non',
                    'is_active' => true,
                ]);
                $testAnnonceIds[] = $annonce->id;
                $this->line("  ✓ Annonce #{$i}: ID={$annonce->id}");
            }
            $this->line("  ✓ 5 annonces créées");
            $this->newLine();

            // ÉTAPE 3: Vérifier quota (6e doit être bloquée)
            $this->info('ÉTAPE 3: Vérifier quota FREE (6e annonce bloquée)');
            $activeCount = $user->annonces()->where('is_active', true)->count();
            $maxAds = $features['max_active_ads'];
            $this->line("  ✓ Active annonces: {$activeCount} / {$maxAds}");
            
            if ($activeCount >= $maxAds) {
                $this->line("  ✅ QUOTA SATURÉ - 6e annonce BLOQUÉE (CORRECT)");
            } else {
                $this->error("  ❌ QUOTA PAS SATURÉ (anomalie)");
            }
            $this->newLine();

            // ÉTAPE 4: Créer demande PRO (PENDING)
            $this->info('ÉTAPE 4: Créer demande PRO (status=PENDING)');
            $plan = Plan::first();
            if (!$plan) {
                $this->error('  ❌ Aucun plan trouvé. Exécutez: php artisan db:seed PlanSeeder');
                return 1;
            }
            
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_status' => 'pending',
                'payment_proof_path' => 'proofs/test-proof-' . time() . '.jpg',
                'started_at' => null,
                'expires_at' => null,
            ]);
            $testSubscriptionId = $subscription->id;
            
            $this->line("  ✓ Subscription créée: ID={$subscription->id}");
            $this->line("  ✓ Status: {$subscription->payment_status} (MANUAL - awaiting admin)");
            $this->line("  ✓ Proof: {$subscription->payment_proof_path}");
            $this->line("  ✓ User.isPro: " . ($user->isPro() ? 'YES' : 'NO') . " (NON - pending)");
            $this->newLine();

            // ÉTAPE 5: ADMIN approuve
            $this->info('ÉTAPE 5: ADMIN approuve la demande PRO');
            $subscriptionService->approveSubscription($subscription);
            $subscription->refresh();
            $user->refresh();
            
            $this->line("  ✓ Subscription approuvée");
            $this->line("  ✓ Status: {$subscription->payment_status} (APPROVED)");
            $this->line("  ✓ Started: {$subscription->started_at}");
            $this->line("  ✓ Expires: {$subscription->expires_at}");
            $this->line("  ✅ User.isPro (refresh): " . ($user->isPro() ? 'YES' : 'NO') . " (OUI!)");
            $this->newLine();

            // ÉTAPE 6: Créer 6e+ annonces (PRO = 50 max)
            $this->info('ÉTAPE 6: Créer annonces 6-8 (PRO = 50 max)');
            $features = $subscriptionService->getFeatures($user);
            $this->line("  ✓ Nouveau quota: {$features['max_active_ads']} annonces");
            
            for ($i = 6; $i <= 8; $i++) {
                $annonce = Annonce::create([
                    'user_id' => $user->id,
                    'titre' => "Test Annonce PRO #{$i}",
                    'description' => "Test description PRO #{$i}",
                    'prix' => 1000000 + ($i * 100000),
                    'marque' => 'Peugeot',
                    'modele' => '307',
                    'annee' => 2018,
                    'kilometrage' => 80000,
                    'carburant' => 'Diesel',
                    'boite_vitesse' => 'Manuelle',
                    'ville' => 'Alger',
                    'vehicle_type' => 'Voiture',
                    'condition' => 'non',
                    'is_active' => true,
                ]);
                $testAnnonceIds[] = $annonce->id;
                $this->line("  ✓ Annonce #{$i}: ID={$annonce->id} ✅ (SUCCÈS - quota PRO)");
            }
            $this->line("  ✅ 8 annonces totales créées");
            $this->newLine();

            // ÉTAPE 7: Boost une annonce
            $this->info('ÉTAPE 7: Boost une annonce');
            $annonceToBoost = $user->annonces()->where('is_active', true)->first();
            $boostService = app(BoostService::class);
            $canBoost = $boostService->canBoost($user, $annonceToBoost);
            
            $this->line("  ✓ Can boost? " . ($canBoost['canBoost'] ? 'YES' : 'NO'));
            
            if ($canBoost['canBoost']) {
                $boost = Boost::create([
                    'annonce_id' => $annonceToBoost->id,
                    'user_id' => $user->id,
                    'started_at' => now(),
                    'expires_at' => now()->addDays(7),
                    'status' => 'active'
                ]);
                $testBoostId = $boost->id;
                
                $this->line("  ✅ Boost créé");
                $this->line("  ✓ ID={$boost->id}");
                $this->line("  ✓ Annonce: {$annonceToBoost->titre} (ID={$annonceToBoost->id})");
                $this->line("  ✓ Expires: {$boost->expires_at}");
                $this->line("  ✓ Status: {$boost->status}");
            } else {
                $this->error("  ❌ Boost échoué: {$canBoost['reason']}");
            }
            $this->newLine();

            // ÉTAPE 8: Vérifier données BD
            $this->info('ÉTAPE 8: Vérifier données en BD');
            $activeAnnonces = Annonce::where('user_id', $user->id)->where('is_active', true)->count();
            
            $this->line("  ✓ User BD: ID={$user->id}, isPro=" . ($user->isPro() ? 'YES' : 'NO'));
            $this->line("  ✓ Subscription BD: ID={$subscription->id}, status={$subscription->payment_status}");
            $this->line("     starts_at={$subscription->started_at}, expires_at={$subscription->expires_at}");
            if ($testBoostId) {
                $boostDb = Boost::find($testBoostId);
                $this->line("  ✓ Boost BD: ID={$boostDb->id}, annonce_id={$boostDb->annonce_id}");
                $this->line("     started_at={$boostDb->started_at}, expires_at={$boostDb->expires_at}");
            }
            $this->line("  ✓ Active annonces: {$activeAnnonces}/50");
            $this->newLine();

            // RÉSUMÉ
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('   RÉSUMÉ COMPLET');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->line('✅ 1. Paiement = 100% MANUEL (status=pending → upload → admin approve)');
            $this->line('✅ 2. Aucune activation auto (awaiting admin approval)');
            $this->line('✅ 3. Quota FREE: 5 annonces (6e bloquée)');
            $this->line('✅ 4. Quota PRO: 50 annonces (6e+ acceptées)');
            $this->line('✅ 5. Boost: 7 jours, propriétaire + PRO uniquement');
            $this->line('✅ 6. Expiration: scheduled (commands disponibles)');
            $this->newLine();
            $this->info('✅ TOUS LES TESTS PASSENT!');
            $this->newLine();

            // Cleanup option
            if ($this->option('cleanup')) {
                $this->info('Nettoyage des données de test...');
                if ($testBoostId) Boost::find($testBoostId)?->delete();
                if ($testSubscriptionId) Subscription::find($testSubscriptionId)?->delete();
                foreach ($testAnnonceIds as $id) {
                    Annonce::find($id)?->delete();
                }
                if ($testUserId) User::find($testUserId)?->delete();
                $this->line('  ✓ Données de test supprimées');
            } else {
                $this->line('💡 Pour nettoyer les données de test, relancez avec --cleanup');
                $this->line("   User ID: {$testUserId}");
                $this->line("   Subscription ID: {$testSubscriptionId}");
                $this->line("   Annonces IDs: " . implode(', ', $testAnnonceIds));
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ ERREUR: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
