# ✅ PLAN DE TEST FINAL - SYSTÈME PRO

## Format de test
```
[ ] Test N : [Description]
    Étapes : [numéroter les actions]
    Résultat attendu : [comportement exact]
    Vérification : [DB / UI / Logs]
    PASS / FAIL : [ ]
```

---

## TEST 1 : Demande Pro (Création subscription PENDING)

**Étapes:**
1. Créer utilisateur test (FREE)
2. Aller à `/pro`
3. Voir plan "PRO - 3 000,00 DZD - 30 jours"
4. Cliquer "S'abonner"
5. URL devient `/pro/subscribe/1`
6. Voir formulaire avec champ "Preuve de paiement"
7. Upload fichier PDF valide (< 5MB)
8. Cliquer "Valider"
9. POST `/pro/subscribe/1`

**Résultat attendu:**
- ✅ Subscription créée en DB
- ✅ subscription.status = 'active'
- ✅ subscription.payment_status = 'pending'
- ✅ subscription.payment_proof_path = chemin fichier
- ✅ subscription.user_id = USER_ID
- ✅ subscription.plan_id = 1
- ✅ Fichier existe: `storage/app/proofs/user_{id}/proof_*.pdf`
- ✅ Redirect `/pro/status`

**Vérification DB:**
```bash
php artisan tinker
# $sub = Subscription::latest()->first()
# echo $sub->status              → 'active'
# echo $sub->payment_status      → 'pending'
# echo $sub->payment_proof_path  → 'proofs/user_X/proof_Y.pdf'
```

**Vérification UI:**
- Page `/pro/status` affiche: "Votre demande est en cours de validation"
- Pas de badge PRO visible
- Bouton "Booster annonce" désactivé

**Vérification Logs:**
```bash
tail -f storage/logs/laravel.log | grep "PENDING\|subscription"
```

[ ] PASS / FAIL : ___________

---

## TEST 2 : Validation Admin - Approuver subscription

**Étapes:**
1. Admin se connecte
2. Va à `/admin/subscriptions`
3. Voir subscription avec status "PENDING"
4. Cliquer sur la ligne ou "Voir détails"
5. Voir `/admin/subscriptions/{id}`
6. Voir bouton "Valider le paiement"
7. Cliquer le bouton
8. PATCH `/admin/subscriptions/{id}/approve`

**Résultat attendu:**
- ✅ subscription.payment_status = 'approved'
- ✅ Message flash: "Paiement validé pour [User Name]"
- ✅ Redirect `/admin/subscriptions`
- ✅ Subscription dans liste montre "Approuvé"

**Vérification DB:**
```bash
php artisan tinker
# $sub = Subscription::find(ID)
# echo $sub->payment_status     → 'approved'
# echo $sub->started_at         → now()
# echo $sub->expires_at         → now() + 30 jours
```

**Vérification Logs:**
```bash
tail storage/logs/laravel.log | grep "✅ ACTIVATION"
# Output: 
# [2026-02-08 ...] local.INFO: ✅ ACTIVATION ABONNEMENT {
#   "user_id": X,
#   "plan": "Pro",
#   "payment_status": "approved"
# }
```

**Vérification UI (utilisateur):**
- Page `/pro/status` affiche: "Votre abonnement est ACTIF"
- Badge "PRO ✓" visible partout

[ ] PASS / FAIL : ___________

---

## TEST 3 : Dépôt date activation + expiration

**Prérequis:** Subscription approuvée du TEST 2

**Étapes:**
1. En DB, vérifier les dates

**Résultat attendu:**
- ✅ started_at ≈ now() (5 min tolerance)
- ✅ expires_at = started_at + 30 jours exactement
- ✅ Plan.duration_days = 30

**Vérification DB:**
```bash
php artisan tinker
# $sub = Subscription::latest()->first()
# $sub->started_at         → '2026-02-08 14:30:00'
# $sub->expires_at         → '2026-03-10 14:30:00' (+ 30j)
# $sub->plan->duration_days → 30

# Vérifier calcul
# $sub->expires_at->diffInDays($sub->started_at) → 30
```

[ ] PASS / FAIL : ___________

---

## TEST 4 : Rejet Admin - Subscription REJECTED

**Étapes:**
1. Créer nouvelle subscription (PENDING)
2. Admin va à `/admin/subscriptions`
3. Clique sur la subscription
4. Va à `/admin/subscriptions/{id}`
5. Voir bouton "Rejeter"
6. Clique le bouton
7. Voir modal/formulaire avec champ "Motif du rejet"
8. Entre: "Preuve de paiement invalide"
9. Clique "Rejeter"
10. PATCH `/admin/subscriptions/{id}/reject`

**Résultat attendu:**
- ✅ subscription.payment_status = 'rejected'
- ✅ subscription.rejection_reason = "Preuve de paiement invalide"
- ✅ Message flash: "Subscription rejetée"
- ✅ Redirect `/admin/subscriptions`

**Vérification DB:**
```bash
php artisan tinker
# $sub = Subscription::find(ID)
# echo $sub->payment_status  → 'rejected'
# echo $sub->rejection_reason → "Preuve de paiement invalide"
```

**Vérification UI (utilisateur):**
- Page `/pro/status` affiche: "Votre paiement a été rejeté"
- Affiche motif: "Preuve de paiement invalide"
- Bouton "Réessayer" visible
- Aucun droit PRO

[ ] PASS / FAIL : ___________

---

## TEST 5 : Quota FREE - max 5 annonces (blocage 6e)

**Étapes:**
1. Créer utilisateur FREE (pas de subscription approuvée)
2. Créer 5 annonces avec POST `/annonces`
3. Admin approuve les 5 (via `/admin/annonces`)
4. Tenter créer 6e annonce avec POST `/annonces`

**Résultat attendu:**
- ✅ 5 annonces créées et actives
- ✅ 6e annonce: validation échoue
- ✅ Message erreur: "Vous avez atteint votre limite de 5 annonces actives"
- ✅ 6e annonce N'EST PAS créée en DB
- ✅ Redirect `/annonces/create` avec erreur

**Vérification DB:**
```bash
php artisan tinker
# Annonce::where('user_id', USER_ID)->where('is_active', true)->count()
# → 5 (pas 6)
```

**Vérification Logs:**
```bash
tail storage/logs/laravel.log | grep "QUOTA ANNONCES DÉPASSÉ"
```

[ ] PASS / FAIL : ___________

---

## TEST 6 : Quota PRO - max 50 annonces

**Étapes:**
1. User PRO créé + approuvé (du TEST 2)
2. Créer 50 annonces avec POST `/annonces`
3. Admin approuve toutes
4. Tenter 51e annonce

**Résultat attendu:**
- ✅ 50 annonces créées et actives
- ✅ 51e : validation échoue
- ✅ Message: "Vous avez atteint votre limite de 50 annonces actives"
- ✅ 51e N'EST PAS créée

**Vérification DB:**
```bash
php artisan tinker
# Annonce::where('user_id', USER_ID).count()
# → 50 (exactement)
```

[ ] PASS / FAIL : ___________

---

## TEST 7 : Boost - Seul propriétaire peut booster

**Étapes:**
1. User A crée annonce (active)
2. User B tente POST `/annonces/{id}/boost`

**Résultat attendu:**
- ✅ Requête échoue
- ✅ Message: "Vous ne pouvez booster que vos propres annonces"
- ✅ Aucun Boost créé en DB

**Vérification DB:**
```bash
php artisan tinker
# Boost::where('annonce_id', ID)->count()
# → 0
```

**Vérification Logs:**
```bash
tail storage/logs/laravel.log | grep "Annonce pas du propriétaire"
```

[ ] PASS / FAIL : ___________

---

## TEST 8 : Boost - Non-PRO interdit

**Étapes:**
1. User FREE avec annonce active
2. POST `/annonces/{id}/boost`

**Résultat attendu:**
- ✅ Requête échoue
- ✅ Message: "Vous devez avoir un abonnement PRO"
- ✅ Aucun Boost créé

**Vérification Logs:**
```bash
tail storage/logs/laravel.log | grep "Utilisateur non PRO"
```

[ ] PASS / FAIL : ___________

---

## TEST 9 : Boost - Création réussie (PRO)

**Étapes:**
1. User PRO approuvé (du TEST 2) avec annonce active
2. POST `/annonces/{id}/boost`

**Résultat attendu:**
- ✅ Boost créé en DB
- ✅ boost.status = 'active'
- ✅ boost.user_id = USER_ID
- ✅ boost.annonce_id = ANNONCE_ID
- ✅ boost.started_at = now()
- ✅ boost.expires_at = now() + 7 jours
- ✅ Message: "Annonce boostée avec succès"

**Vérification DB:**
```bash
php artisan tinker
# $boost = Boost::latest()->first()
# echo $boost->status              → 'active'
# echo $boost->expires_at          → now() + 7j
# echo $boost->annonce->isBoosted() → true
```

**Vérification Logs:**
```bash
tail storage/logs/laravel.log | grep "✅ BOOST AUTORISÉ"
```

[ ] PASS / FAIL : ___________

---

## TEST 10 : Boost - Annonce déjà boostée (erreur)

**Étapes:**
1. Annonce avec Boost actif (du TEST 9)
2. Tenter POST `/annonces/{id}/boost` à nouveau

**Résultat attendu:**
- ✅ Requête échoue
- ✅ Message: "Cette annonce est déjà boostée"
- ✅ Seul 1 Boost en DB

[ ] PASS / FAIL : ___________

---

## TEST 11 : Quota mensuel boost (5/mois)

**Étapes:**
1. User PRO avec 5 annonces
2. Boost les 5 annonces (février 2026)
3. Tenter boost 6e

**Résultat attendu:**
- ✅ 5 boosts créés
- ✅ 6e : échoue
- ✅ Message: "Vous avez atteint votre limite de 5 boosts ce mois-ci"

**Vérification DB:**
```bash
php artisan tinker
# Boost::where('user_id', USER_ID)
#   ->whereBetween('started_at', [now()->startOfMonth(), now()->endOfMonth()])
#   ->count()
# → 5 (pas 6)
```

**Reset mois suivant (MARS):**
- ✅ Peut créer 5 nouveaux boosts en Mars
- ✅ Ancien quota libre

[ ] PASS / FAIL : ___________

---

## TEST 12 : Tri - Annonces boostées en premier

**Étapes:**
1. 3 annonces créées: A (normal), B (boostée), C (normal)
2. Tous approuvés, B boostée
3. Aller à `/recherche`

**Résultat attendu:**
- ✅ Ordre: B (boostée), puis A & C
- ✅ Annonce B a badge "⭐ À la une" ou couleur highlight
- ✅ OrderBy SQL: `status DESC THEN created_at DESC`

**Vérification Vue:**
```html
<!-- Dans search.blade.php -->
@foreach ($annonces as $annonce)
  @if ($annonce->isBoosted())
    <div class="bg-yellow-50 border-2 border-yellow-300">
      <span class="badge badge-warning">⭐ À la une</span>
    </div>
  @endif
@endforeach
```

[ ] PASS / FAIL : ___________

---

## TEST 13 : Expiration Boost (7 jours)

**Étapes:**
1. Boost créé au TEST 9 (expires_at = 2026-02-15)
2. Fake time à 2026-02-16 (ou attendre 7j)
3. Exécuter: `php artisan boosts:expire`

**Résultat attendu:**
- ✅ boost.status = 'expired'
- ✅ Command affiche: "Expired 1 boost(s)"
- ✅ Annonce retourne à tri normal (plus en premier)

**Vérification DB:**
```bash
php artisan tinker
# $boost = Boost::find(ID)
# echo $boost->status             → 'expired'
# echo $boost->annonce->isBoosted() → false
```

[ ] PASS / FAIL : ___________

---

## TEST 14 : Expiration Subscription (30 jours)

**Étapes:**
1. Subscription créée au TEST 2 (expires_at = 2026-03-10)
2. Fake time à 2026-03-11
3. Exécuter: `php artisan subscriptions:expire`

**Résultat attendu:**
- ✅ subscription.status = 'expired'
- ✅ Command affiche: "Expired 1 subscription(s)"
- ✅ User perd badge PRO
- ✅ Quotas reviennent à FREE (5 annonces)

**Vérification DB:**
```bash
php artisan tinker
# $sub = Subscription::find(ID)
# echo $sub->status  → 'expired'

# $user->isPro()     → false (activeSubscription() = null)
# features           → [max_active_ads => 5, ...]
```

**Vérification UI:**
- Page `/pro/status` affiche: "Votre abonnement a expiré"
- Badge PRO disparu

[ ] PASS / FAIL : ___________

---

## TEST 15 : Blocage annonce si > quota FREE après expiration

**Étapes:**
1. User PRO avec 30 annonces (tous actifs)
2. Subscription expire
3. Tenter créer 31e annonce

**Résultat attendu:**
- ✅ Création échoue
- ✅ Message: "Vous avez atteint votre limite de 5 annonces actives"
- ✅ 31e N'EST PAS créée
- ✅ User doit supprimer 25 annonces pour pouvoir créer nouvelles

[ ] PASS / FAIL : ___________

---

## TEST 16 : Confirmation - Paiement MANUEL uniquement

**Vérification du code:**
1. Pas d'intégration Stripe dans les routes
2. Formulaire accepte uniquement upload fichier
3. Status 'pending' jusqu'à approve admin
4. Zero transactions automatiques

**Grep search:**
```bash
grep -r "stripe\|Stripe\|STRIPE" app/ resources/
# → Aucun résultat (OK)
```

**Résultat attendu:**
- ✅ Aucune référence Stripe
- ✅ Processus = Upload → Pending → Admin Approve

[ ] PASS / FAIL : ___________

---

## TEST 17 : Confirmation - Activation jamais automatique

**Vérification:**
```bash
php artisan tinker
# Subscription::factory(10)->create()
# → ALL ont payment_status = 'pending'
# → Aucun ne retourne isPro() = true
```

**Code check:**
```php
// SubscriptionService::getActiveSubscription()
->where('payment_status', 'approved')  // ← MUST BE EXPLICIT
```

**Résultat attendu:**
- ✅ Aucune subscription ne s'active sans approve explicite
- ✅ isPro() retourne false tant que pending

[ ] PASS / FAIL : ___________

---

## TEST 18 : Confirmation - Scheduler d'expiration

**Étapes:**
1. Vérifier `app/Console/Kernel.php` contient:
```php
$schedule->command('subscriptions:expire')->daily();
$schedule->command('boosts:expire')->daily();
```

**Vérification:**
```bash
grep -A 10 "schedule->" app/Console/Kernel.php
```

**Résultat attendu:**
- ✅ Commandes enregistrées dans scheduler
- ✅ Exécution quotidienne

[ ] PASS / FAIL : ___________

---

## RÉSUMÉ FINAL

| Test | Description | Status |
|------|-------------|--------|
| 1 | Demande PRO (PENDING) | [ ] |
| 2 | Validation Admin (APPROVED) | [ ] |
| 3 | Dates activation + expiration | [ ] |
| 4 | Rejet Admin (REJECTED) | [ ] |
| 5 | Quota FREE (max 5) | [ ] |
| 6 | Quota PRO (max 50) | [ ] |
| 7 | Boost (propriétaire uniquement) | [ ] |
| 8 | Boost (Non-PRO interdit) | [ ] |
| 9 | Boost (création réussie) | [ ] |
| 10 | Boost (annonce déjà boostée) | [ ] |
| 11 | Quota mensuel boost (5/mois) | [ ] |
| 12 | Tri boosts (en premier) | [ ] |
| 13 | Expiration boost (7j) | [ ] |
| 14 | Expiration subscription (30j) | [ ] |
| 15 | Blocage après expiration | [ ] |
| 16 | Confirmation: Paiement MANUEL | [ ] |
| 17 | Confirmation: Pas d'activation auto | [ ] |
| 18 | Confirmation: Scheduler actif | [ ] |

**Validation finale:** Tous les tests PASS ? ___________

