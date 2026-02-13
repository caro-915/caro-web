# Tests de validation des corrections BUG 1, 2, 3

## BUG 1 - Moto : boite_vitesse optionnel

### Test 1.1 : Création annonce Moto sans boite_vitesse
**Étapes :**
1. Connexion utilisateur
2. Aller sur `/annonces/create`
3. Sélectionner "Type de véhicule" = Moto
4. Remplir les champs obligatoires (titre, prix, marque, carburant)
5. Laisser "Boîte de vitesses" vide (champ grisé)
6. Upload 1+ images
7. Soumettre le formulaire

**Résultat attendu :**
- ✅ Annonce créée avec succès
- ✅ `boite_vitesse` = "N/A" dans la DB
- ✅ Aucune erreur de validation

**Commande SQL de vérification :**
```sql
SELECT id, titre, vehicle_type, boite_vitesse FROM annonces WHERE vehicle_type = 'Moto' ORDER BY id DESC LIMIT 5;
```

### Test 1.2 : Création annonce Voiture sans boite_vitesse
**Étapes :**
1. Connexion utilisateur
2. Aller sur `/annonces/create`
3. Sélectionner "Type de véhicule" = Voiture
4. Remplir les champs obligatoires SAUF boite_vitesse
5. Soumettre le formulaire

**Résultat attendu :**
- ❌ Erreur de validation : "La boîte de vitesses est obligatoire."
- ✅ Formulaire rechargé avec erreur en rouge

### Test 1.3 : API - Création Moto sans boite_vitesse
**Requête POST `/api/annonces` :**
```json
{
  "titre": "Yamaha MT-07 2023",
  "prix": 5000000,
  "marque": "Yamaha",
  "vehicle_type": "Moto",
  "carburant": "Essence",
  "condition": "non"
}
```

**Résultat attendu :**
- ✅ Status 201 Created
- ✅ `boite_vitesse` = "N/A" dans la réponse JSON

---

## BUG 2 - Logique plans (PRO vs Premium)

### Test 2.1 : Compte gratuit (pas d'abonnement)
**Étapes :**
1. Créer compte nouveau (sans abonnement)
2. Aller sur `/annonces/create`
3. Vérifier le texte d'intro : "jusqu'à X photos"
4. Tenter d'uploader 5 images

**Résultat attendu :**
- ✅ Texte : "jusqu'à 4 photos"
- ✅ Quota annonces : 5 max
- ❌ Erreur si upload > 4 images

**Commande de vérification :**
```bash
php artisan tinker
>>> $user = User::find(X); // remplacer X par user_id
>>> $service = app(\App\Services\SubscriptionService::class);
>>> $features = $service->getFeatures($user);
>>> dd($features);
```

**Résultat attendu :**
```php
[
  "max_active_ads" => 5,
  "max_images_per_ad" => 4,
  "boosts_per_month" => 0,
  "boost_duration_days" => 7,
]
```

### Test 2.2 : Compte Premium (1 semaine, boost)
**Étapes :**
1. Créer abonnement Premium via admin ou seeder
2. User se connecte
3. Aller sur `/annonces/create`
4. Vérifier texte et limites
5. Tenter upload 5 images

**Résultat attendu :**
- ✅ Texte : "jusqu'à 4 photos" (MÊME limite que gratuit)
- ✅ Quota annonces : 5 max (MÊME limite que gratuit)
- ❌ Erreur si upload > 4 images
- ✅ Boost actif 7 jours

**Commande de vérification :**
```bash
php artisan tinker
>>> $user = User::find(X); // User avec abonnement Premium
>>> $service = app(\App\Services\SubscriptionService::class);
>>> $features = $service->getFeatures($user);
>>> dd($features);
```

**Résultat attendu :**
```php
[
  "max_active_ads" => 5,
  "max_images_per_ad" => 4,  // ← IMPORTANT : pas 8 !
  "boosts_per_month" => 3,
  "boost_duration_days" => 7,
]
```

### Test 2.3 : Compte PRO (1 mois, boost)
**Étapes :**
1. Créer abonnement PRO via admin
2. User se connecte
3. Aller sur `/annonces/create`
4. Vérifier texte et limites
5. Tenter upload 8 images

**Résultat attendu :**
- ✅ Texte : "jusqu'à 8 photos"
- ✅ Quota annonces : 10 max
- ✅ Upload 8 images OK
- ✅ Boost actif 30 jours

**Commande de vérification :**
```bash
php artisan tinker
>>> $user = User::find(X); // User avec abonnement Pro
>>> $service = app(\App\Services\SubscriptionService::class);
>>> $features = $service->getFeatures($user);
>>> dd($features);
```

**Résultat attendu :**
```php
[
  "max_active_ads" => 10,
  "max_images_per_ad" => 8,  // ← PRO = 8 images
  "boosts_per_month" => 5,
  "boost_duration_days" => 7,
]
```

### Test 2.4 : Admin modifie plan Premium (nouvelles limites)
**Étapes :**
1. Admin va sur interface admin plans
2. Modifie plan Premium : max_images_per_ad = 6 (exemple)
3. User Premium crée nouvelle annonce
4. Vérifier limite appliquée

**Résultat attendu :**
- ✅ Texte : "jusqu'à 6 photos" (nouvelle limite)
- ✅ Upload 6 images OK
- ✅ Pas besoin de modifier le code source

---

## BUG 3 - Watermark non appliqué sur image 1

### Test 3.1 : Upload 3 images (vérifier watermark toutes images)
**Étapes :**
1. Créer annonce avec 3 images
2. Attendre traitement async (10-30 secondes)
3. Vérifier logs : `storage/logs/laravel.log`
4. Télécharger les 3 images depuis la page annonce
5. Ouvrir chaque image et vérifier watermark "ELSAYARA"

**Résultat attendu :**
- ✅ Log : "🎨 ProcessAnnonceImages: Début traitement" avec 3 paths
- ✅ Log : "🖼️ Traitement image" pour index 0, 1, 2
- ✅ Log : "✅ Image traitée avec succès" pour chaque image
- ✅ Watermark visible sur image 1 (image_path / index 0)
- ✅ Watermark visible sur image 2 (image_path_2 / index 1)
- ✅ Watermark visible sur image 3 (image_path_3 / index 2)

**Commande log :**
```bash
php artisan tinker
>>> tail -n 100 storage/logs/laravel.log | grep "ProcessAnnonceImages"
```

Ou dans PowerShell :
```powershell
Get-Content storage\logs\laravel.log -Tail 100 | Select-String "ProcessAnnonceImages"
```

### Test 3.2 : Upload 1 seule image (edge case)
**Étapes :**
1. Créer annonce avec 1 seule image
2. Attendre traitement
3. Vérifier logs
4. Télécharger l'image et vérifier watermark

**Résultat attendu :**
- ✅ Log : "🎨 ProcessAnnonceImages: Début traitement" avec 1 path
- ✅ Log : "🖼️ Traitement image" pour index 0
- ✅ Log : "✅ Image traitée avec succès"
- ✅ Watermark "ELSAYARA" visible (texte blanc, 20% opacité, centré, 65% largeur)

### Test 3.3 : Update annonce (ajouter nouvelles images)
**Étapes :**
1. Éditer une annonce existante avec 2 images
2. Ajouter 2 nouvelles images via formulaire edit
3. Soumettre
4. Vérifier que les 2 nouvelles images ont watermark

**Résultat attendu :**
- ✅ Les 2 nouvelles images ont watermark (traité inline dans update)
- ✅ Les 2 anciennes images gardent leur watermark (pas re-traité)

---

## Tests automatisés

### Test unitaire : SubscriptionService.getFeatures()
```bash
php artisan test --filter=SubscriptionServiceTest
```

**Créer le test si nécessaire :**
`tests/Unit/SubscriptionServiceTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_gets_default_limits()
    {
        $user = User::factory()->create();
        $service = new SubscriptionService();
        
        $features = $service->getFeatures($user);
        
        $this->assertEquals(5, $features['max_active_ads']);
        $this->assertEquals(4, $features['max_images_per_ad']);
    }
    
    public function test_premium_user_gets_premium_limits()
    {
        $user = User::factory()->create();
        $plan = Plan::create([
            'name' => 'Premium',
            'price' => 1500,
            'duration_days' => 7,
            'features' => [
                'max_active_ads' => 5,
                'max_images_per_ad' => 4,
                'boosts_per_month' => 3,
            ],
        ]);
        
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(7),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);
        
        $service = new SubscriptionService();
        $features = $service->getFeatures($user);
        
        $this->assertEquals(5, $features['max_active_ads']);
        $this->assertEquals(4, $features['max_images_per_ad']); // Premium = 4 images
        $this->assertEquals(3, $features['boosts_per_month']);
    }
    
    public function test_pro_user_gets_pro_limits()
    {
        $user = User::factory()->create();
        $plan = Plan::create([
            'name' => 'Pro',
            'price' => 3000,
            'duration_days' => 30,
            'features' => [
                'max_active_ads' => 10,
                'max_images_per_ad' => 8,
                'boosts_per_month' => 5,
            ],
        ]);
        
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);
        
        $service = new SubscriptionService();
        $features = $service->getFeatures($user);
        
        $this->assertEquals(10, $features['max_active_ads']);
        $this->assertEquals(8, $features['max_images_per_ad']); // PRO = 8 images
        $this->assertEquals(5, $features['boosts_per_month']);
    }
}
```

### Test feature : Création annonce Moto
```bash
php artisan test --filter=test_create_annonce_moto_without_boite_vitesse
```

**Créer le test si nécessaire :**
`tests/Feature/AnnonceCreationTest.php`

```php
public function test_create_annonce_moto_without_boite_vitesse()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->post('/annonces', [
        'titre' => 'Yamaha MT-07',
        'prix' => 5000000,
        'marque' => 'Yamaha',
        'modele' => 'MT-07',
        'vehicle_type' => 'Moto',
        'carburant' => 'Essence',
        'condition' => 'non',
        // boite_vitesse non fourni
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $annonce = Annonce::latest()->first();
    $this->assertEquals('N/A', $annonce->boite_vitesse);
    $this->assertEquals('Moto', $annonce->vehicle_type);
}

public function test_create_annonce_voiture_requires_boite_vitesse()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->post('/annonces', [
        'titre' => 'Renault Clio',
        'prix' => 2000000,
        'marque' => 'Renault',
        'modele' => 'Clio',
        'vehicle_type' => 'Voiture',
        'carburant' => 'Essence',
        'condition' => 'non',
        // boite_vitesse non fourni
    ]);
    
    $response->assertSessionHasErrors('boite_vitesse');
}
```

---

## Commandes de diagnostic rapide

### Vérifier plans en DB :
```bash
php artisan tinker
>>> Plan::all(['id', 'name', 'price', 'duration_days', 'features']);
```

### Vérifier abonnements actifs :
```bash
php artisan tinker
>>> Subscription::where('status', 'active')->where('payment_status', 'approved')->with('plan', 'user')->get();
```

### Re-seed plans (si nécessaire) :
```bash
php artisan db:seed --class=PlanSeeder
```

### Vérifier queue processing (pour watermark async) :
```bash
php artisan queue:work --once
```

Ou en continu :
```bash
php artisan queue:listen
```

### Nettoyer cache :
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Résumé des attendus

| Test | Avant | Après |
|------|-------|-------|
| **Moto sans boite_vitesse** | ❌ Erreur validation | ✅ Créé avec boite_vitesse="N/A" |
| **Voiture sans boite_vitesse** | ❌ Erreur validation | ❌ Erreur validation (inchangé) |
| **Premium max_images** | ❌ 8 images (bug) | ✅ 4 images |
| **PRO max_images** | ✅ 8 images | ✅ 8 images (inchangé) |
| **Watermark image 1** | ❌ Pas de watermark | ✅ Watermark présent |
| **Watermark images 2-8** | ✅ Watermark présent | ✅ Watermark présent (inchangé) |

---

## Checklist finale

- [x] Migration `boite_vitesse` nullable exécutée
- [x] Validation conditionnelle `required_if:vehicle_type,Voiture`
- [x] Valeur par défaut "N/A" pour Moto
- [x] Suppression hardcoded `$isPro ? 8 : 4`
- [x] Logique 100% basée sur `$features['max_images_per_ad']`
- [x] Logs debug dans `ProcessAnnonceImages`
- [x] Tests manuels documentés
- [x] Tests automatisés créés (optionnel mais recommandé)
