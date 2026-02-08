# 🚀 Système PRO - Documentation

## Vue d'ensemble

Le système PRO permet aux utilisateurs de s'abonner à un plan premium pour accéder à des fonctionnalités avancées comme l'augmentation des annonces actives et les boosts.

## Architecture

### Modèles

#### Plan
- `name` : Nom du plan (Ex: "Pro")
- `price` : Prix en DZD
- `duration_days` : Durée d'abonnement en jours
- `features` : JSON avec fonctionnalités
  - `max_active_ads` : Nombre maximum d'annonces actives
  - `boosts_per_month` : Nombre de boosts par mois
  - `boost_duration_days` : Durée d'un boost en jours
- `is_active` : Booléen pour activer/désactiver le plan

#### Subscription
- `user_id` : FK vers User
- `plan_id` : FK vers Plan
- `started_at` : Date de début
- `expires_at` : Date d'expiration
- `status` : 'active' | 'expired' | 'cancelled'
- `payment_proof_path` : Chemin du fichier de preuve de paiement
- `payment_status` : 'pending' | 'approved' | 'rejected'
- `rejection_reason` : Raison du rejet (optionnel)

#### Boost
- `annonce_id` : FK vers Annonce
- `user_id` : FK vers User
- `started_at` : Date de début du boost
- `expires_at` : Date d'expiration du boost
- `status` : 'active' | 'expired'

### Services

#### SubscriptionService
Gère le cycle de vie des abonnements.

**Méthodes principales :**
- `getActiveSubscription(User)` : Récupère l'abonnement actif de l'utilisateur
- `userIsPro(User)` : Vérifie si l'utilisateur est PRO
- `getFeatures(User)` : Retourne les fonctionnalités (PRO ou gratuit)
- `createSubscription(User, Plan, paymentProofPath)` : Crée un nouvel abonnement
- `approveSubscription(Subscription)` : Approuve un paiement
- `rejectSubscription(Subscription, reason)` : Rejette un paiement
- `expireOldSubscriptions()` : Expire les abonnements expirés

#### BoostService
Gère les boosts d'annonces.

**Méthodes principales :**
- `canBoost(User, Annonce)` : Vérifie si l'utilisateur peut booster
- `boostAnnonce(User, Annonce)` : Crée un boost
- `countBoostsThisMonth(User)` : Compte les boosts du mois actuel
- `expireOldBoosts()` : Expire les boosts expirés

### Routes

#### Utilisateur
```
GET  /pro                           → Page marketing PRO
GET  /pro/subscribe/{plan}          → Formulaire d'abonnement
POST /pro/subscribe/{plan}          → Soumettre une preuve de paiement
GET  /pro/status                    → Statut de l'abonnement
POST /annonces/{annonce}/boost      → Booster une annonce
```

#### Admin
```
GET  /admin/plans                   → Liste des plans
POST /admin/plans                   → Créer un plan
GET  /admin/plans/{plan}/edit       → Éditer un plan
PUT  /admin/plans/{plan}            → Mettre à jour un plan
DELETE /admin/plans/{plan}          → Supprimer un plan

GET  /admin/subscriptions           → Liste des abonnements (en attente + approuvés)
GET  /admin/subscriptions/{sub}     → Détails d'un abonnement
PATCH /admin/subscriptions/{sub}/approve → Approuver un paiement
PATCH /admin/subscriptions/{sub}/reject  → Rejeter un paiement
```

### Middleware

`EnsureUserIsPro` : Vérifie que l'utilisateur a un abonnement PRO actif.

Utilisation :
```php
Route::middleware('pro')->group(function () {
    // Routes réservées aux utilisateurs PRO
});
```

## Plan PAR DÉFAUT

Un plan "Pro" est automatiquement créé au premier déploiement :

- **Nom :** Pro
- **Prix :** 3000 DZD
- **Durée :** 30 jours
- **Max annonces :** 50
- **Boosts/mois :** 5
- **Durée boost :** 7 jours

## Flux de paiement (Manuel)

1. L'utilisateur accède à `/pro`
2. Sélectionne un plan et clique sur "S'abonner"
3. Reçoit les instructions de paiement
4. Effectue un transfert bancaire ou Mobile Money
5. Télécharge la preuve de paiement
6. Statut passe à "pending"
7. Admin vérifie et approuve dans `/admin/subscriptions`
8. Statut passe à "approved" et `payment_status` = "approved"
9. L'abonnement est maintenant actif

En cas de rejet, l'utilisateur peut réessayer avec une autre preuve.

## Boosts

### Comment activer un boost ?

Seuls les utilisateurs PRO avec un abonnement actif et approuvé peuvent booster.

1. Aller à `/mes-annonces`
2. Cliquer sur "Booster" pour une annonce
3. Système vérifie :
   - Utilisateur est PRO ✓
   - Annonce appartient à l'utilisateur ✓
   - Annonce est active ✓
   - Pas déjà boostée ✓
   - Limite mensuelle non atteinte ✓
4. Si OK → Boost créé pour 7 jours
5. Annonce remontée dans les résultats de recherche

### Logique de recherche

Les annonces boostées apparaissent en haut des résultats de recherche (après tri par pertinence).

```php
// Dans AnnonceController::search()
$query->orderBy(function($query) {
    return $query->from('boosts')
        ->where('boosts.annonce_id', 'annonces.id')
        ->where('status', 'active')
        ->where('expires_at', '>', now());
}, 'desc') // Afficher les boostées en premier
->orderBy('created_at', 'desc');
```

## Commandes Artisan

```bash
# Expirer les abonnements dépassés
php artisan subscriptions:expire

# Expirer les boosts dépassés
php artisan boosts:expire
```

## Scheduler

Dans `bootstrap/providers.php` (ou `Console/Kernel.php`) :

```php
$schedule->command('subscriptions:expire')->daily();
$schedule->command('boosts:expire')->daily();
```

## Quotas

### Utilisateur Gratuit
- **Max annonces actives :** 5
- **Boosts disponibles :** 0

### Utilisateur PRO
- **Max annonces actives :** 50 (configurable par plan)
- **Boosts disponibles :** 5/mois (configurable par plan)
- **Durée boost :** 7 jours (configurable par plan)

La vérification des quotas se fait dans :
- `AnnonceController::store()` - Vérifier limite max_active_ads
- `BoostService::canBoost()` - Vérifier limite boosts_per_month

## Admin

### Gestion des plans

Créez, modifiez ou supprimez des plans depuis `/admin/plans`.

Chaque plan peut être désactivé sans supprimer les abonnements existants.

### Gestion des abonnements

Approuvez ou rejetez les demandes d'abonnement depuis `/admin/subscriptions`.

- **Tab "En attente"** : Affiche les paiements en attente de vérification
- **Tab "Approuvés"** : Affiche les abonnements actifs

## Sécurité

- Les preuves de paiement sont stockées dans `storage/app/public/payment_proofs/`
- Les administrateurs peuvent voir les images uploadées
- Les utilisateurs ne peuvent accéder qu'à leur propre statut
- Validation stricte des fichiers (jpg, png, pdf, max 5MB)

## Tests

```bash
php artisan test tests/Feature/ProFeatureTest.php
```

Couvre :
- ✓ Utilisateur gratuit limité à 5 annonces
- ✓ Utilisateur non-PRO ne peut pas booster
- ✓ Utilisateur PRO peut booster
- ✓ Utilisateur PRO ne peut pas booster une annonce inactive
- ✓ Service de subscription retourne les bonnes fonctionnalités
- ✓ Utilisateur gratuit a des fonctionnalités limitées
- ✓ Les abonnements expirent correctement
- ✓ Accès à la page PRO quand authentifié
- ✓ Voir le statut de son abonnement

## À faire (Futur)

- [ ] Ajouter paiement online (Stripe, PayPal, etc)
- [ ] Email de confirmation d'abonnement
- [ ] Email d'expiration 7 jours avant
- [ ] Renouvellement automatique
- [ ] Statistiques d'utilisation (boosts utilisés, annonces actives, etc)
- [ ] Historique des paiements
- [ ] Intégration Slack/Webhook pour notification approbation
- [ ] Plan d'essai gratuit 7 jours
- [ ] Coupon de réduction
