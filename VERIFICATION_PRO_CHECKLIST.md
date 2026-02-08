# 📋 CHECKLIST DE RECETTE FONCTIONNELLE - SYSTÈME PRO

## STATUS GLOBAL
- ✅ Migrations créées (plans, subscriptions, boosts)
- ✅ Modèles + Relations créées
- ✅ Services (SubscriptionService, BoostService) créées
- ✅ Middleware 'pro' créé et enregistré
- ✅ Routes PRO + Boost + Admin créées
- ✅ Contrôleurs créés
- ✅ Logs temporaires ajoutés
- ⏳ Validation quotas annonces - À VÉRIFIER
- ⏳ Tri des annonces boostées - À VÉRIFIER
- ⏳ Affichage badges PRO - À VÉRIFIER

---

## A. PARCOURS UTILISATEUR PRO

### A1. Demande PRO → Création subscription PENDING
**Étapes:**
1. Utilisateur clique `/pro`
2. Voit plan "PRO - 3000 DZD - 30 jours"
3. Clique "S'abonner"
4. Va à `/pro/subscribe/{plan_id}`
5. Upload preuve paiement (jpg/png/pdf, max 5MB)
6. Valide le formulaire
7. POST `/pro/subscribe/{plan_id}`

**Résultat attendu:**
- ✅ Subscription créée en DB
- ✅ Status: PENDING
- ✅ Payment_status: PENDING
- ✅ Fichier stocké dans `storage/app/proofs/`
- ✅ Pas d'activation immédiate
- ✅ Redirection vers `/pro/status`

**Comment vérifier (DB):**
```bash
php artisan tinker
# Subscription::where('user_id', USER_ID)->latest()->first()
# → Vérifier: status = 'active', payment_status = 'pending', payment_proof_path existe
```

**Comment vérifier (UI):**
- Page status montre "Votre demande est en cours de validation"
- Pas de badge PRO affiché

---

### A2. Upload reçu stocké correctement
**Vérification:**
1. Après upload, vérifier le fichier existe:
```bash
php artisan tinker
# Storage::disk('local')->exists($path)
```

2. Vérifier path dans subscription:
```bash
# Subscription::find(ID)->payment_proof_path
# → Ex: "proofs/user_123/proof_xyz.pdf"
```

---

### A3. Affichage page statut "en attente"
**Étapes:**
1. Après créer subscription, aller à `/pro/status`

**Résultat attendu:**
- ✅ Titre: "Status de votre abonnement"
- ✅ Affiche: Preuve paiement: EN ATTENTE DE VALIDATION
- ✅ Pas de badge PRO
- ✅ Pas d'accès aux boosts
- ✅ Quota reste FREE (5 annonces)

---

## B. VALIDATION ADMIN

### B1. Approve → Passage ACTIVE
**Étapes:**
1. Admin va à `/admin/subscriptions`
2. Voir subscription PENDING avec user + plan
3. Clique "Valider le paiement"
4. POST `/admin/subscriptions/{id}/approve`

**Résultat attendu:**
- ✅ DB: subscription.payment_status = 'approved'
- ✅ Log: "✅ ACTIVATION ABONNEMENT" avec dates
- ✅ Message flash: "Paiement validé"
- ✅ Redirect `/admin/subscriptions`

**Comment vérifier (DB):**
```bash
php artisan tinker
# Subscription::find(ID)->payment_status
# → 'approved'
```

---

### B2. Calcul correct des dates (start + expiration)
**Résultat attendu:**
- ✅ started_at = now() (quand approuvé)
- ✅ expires_at = now() + 30 jours
- ✅ Status = 'active'

**Comment vérifier:**
```bash
php artisan tinker
# $sub = Subscription::find(ID)
# $sub->started_at → affiche maintenant
# $sub->expires_at → affiche date + 30j
# $sub->plan->duration_days → 30
```

---

### B3. Expiration ancienne subscription si existait
**Scenario:**
1. User a sub1 active (expires_at = demain)
2. Crée sub2 (pending)
3. Admin approuve sub2

**Résultat attendu:**
- ✅ sub1.status = 'expired' (auto-expiration)
- ✅ sub2.status = 'active'
- ✅ Seule sub2 active retournée par activeSubscription()

**Comment vérifier:**
```bash
php artisan tinker
# $user->subscriptions()->get()
# → Une seule avec payment_status='approved' et expires_at > now()
```

---

### B4. Badge PRO visible
**Étapes:**
1. Après approuve, aller à `/annonces/create`
2. Vérifier UI

**Résultat attendu:**
- ✅ Badge "PRO ✓" affiché
- ✅ Visible dans `/mes-annonces`
- ✅ Visible sur profile vendeur

---

### B5. Droits débloqués
**Résultat attendu:**
- ✅ Peut créer jusqu'à 50 annonces
- ✅ Peut créer 5 boosts/mois
- ✅ Peut booster ses annonces
- ✅ Téléphone visible si show_phone=true

---

### B6. Rejet admin
**Étapes:**
1. Admin va `/admin/subscriptions/{id}`
2. Voit subscription PENDING
3. Clique "Rejeter"
4. Entre raison: "Preuve invalide"
5. POST `/admin/subscriptions/{id}/reject`

**Résultat attendu:**
- ✅ payment_status = 'rejected'
- ✅ rejection_reason = "Preuve invalide"
- ✅ Log: "❌ PAIEMENT REJETÉ"

**Page status:**
- ✅ Affiche: "Votre paiement a été rejeté"
- ✅ Montre raison
- ✅ Bouton "Réessayer"
- ✅ Aucun droit PRO

---

## C. LIMITES FREE vs PRO

### C1. FREE : max 5 annonces → blocage à la 6e
**Étapes:**
1. User FREE crée 5 annonces (approve all)
2. Tente créer 6e
3. POST `/annonces`

**Résultat attendu:**
- ✅ Validation échoue
- ✅ Message: "Vous avez atteint votre limite de 5 annonces"
- ✅ Redirect avec erreur
- ✅ 6e annonce N'EST PAS créée
- ✅ Log: "❌ QUOTA ANNONCES DÉPASSÉ"

**Comment vérifier (DB):**
```bash
php artisan tinker
# Annonce::where('user_id', USER_ID)->where('is_active', true)->count()
# → Doit être 5 max
```

---

### C2. FREE : boost interdit
**Étapes:**
1. User FREE avec annonce active
2. Va à `/annonces/{id}`
3. Cherche bouton boost

**Résultat attendu:**
- ✅ Bouton boost inexistant ou désactivé
- ✅ POST `/annonces/{id}/boost` → 403 Forbidden

---

### C3. FREE : Pas de boost = annonce normale
**Résultat attendu:**
- ✅ Apparait dans recherche normalement
- ✅ Pas d'ordre spécial
- ✅ Pas de badge "À la une"

---

### C4. PRO : 50 annonces autorisées
**Étapes:**
1. User PRO approuvé
2. Crée 50 annonces (approve all)
3. Tente 51e

**Résultat attendu:**
- ✅ 50 créées avec succès
- ✅ 51e bloquée avec message: "Vous avez atteint votre limite de 50 annonces"

---

### C5. PRO : boosts autorisés selon quota
**Étapes:**
1. User PRO avec 2 annonces actives
2. Boost annonce 1 → OK
3. Boost annonce 2 → OK
4. Boost annonce 3 → FAIL (3 > 5 par mois)

**Résultat attendu:**
- ✅ 2 boosts créés
- ✅ 3e échoue avec message: "Vous avez atteint votre limite de 5 boosts ce mois-ci"
- ✅ Log: "✅ BOOST AUTORISÉ" pour les 2 premiers
- ✅ Log: "❌ BOOST NON AUTORISÉ : Quota mensuel" pour le 3e

---

### C6. PRO : téléphone visible
**Étapes:**
1. User PRO crée annonce avec show_phone=true
2. Admin approuve
3. Autre user voit annonce

**Résultat attendu:**
- ✅ Téléphone visible dans annonce

**Comparaison FREE:**
- ✅ FREE : téléphone masqué (si show_phone=true, affiche "Contact vendeur" button)

---

## D. BOOST

### D1. Seul propriétaire peut booster
**Étapes:**
1. User A crée annonce
2. User B tente `/annonces/{id}/boost`

**Résultat attendu:**
- ✅ POST échoue
- ✅ Message: "Vous ne pouvez booster que vos propres annonces"
- ✅ Log: "❌ BOOST NON AUTORISÉ : Annonce pas du propriétaire"

---

### D2. Non-PRO → erreur
**Étapes:**
1. User FREE avec annonce
2. POST `/annonces/{id}/boost`

**Résultat attendu:**
- ✅ Échoue
- ✅ Message: "Vous devez avoir un abonnement PRO"
- ✅ Log: "❌ BOOST NON AUTORISÉ : Utilisateur non PRO"

---

### D3. Respect quota mensuel
**Étapes:**
1. User PRO
2. 1er Février: Boost 5 annonces
3. 1er Mars: Doit pouvoir booster 5 nouvelles

**Résultat attendu:**
- ✅ Février: 5 boosts OK, 6e échoue
- ✅ Mars: Compteur reset, 5 nouveaux OK

**Comment vérifier (DB):**
```bash
php artisan tinker
# Boost::where('user_id', USER_ID)
#       ->whereBetween('started_at', [now()->startOfMonth(), now()->endOfMonth()])
#       ->count()
# → 5 (ou moins)
```

---

### D4. Tri prioritaire des annonces boostées
**Étapes:**
1. Créer 3 annonces (normale, boostée, normale)
2. Approuver toutes
3. Booster la 2e
4. Aller à `/recherche`

**Résultat attendu:**
- ✅ Annonce boostée apparait EN PREMIER
- ✅ Badge "⭐ À la une" visible
- ✅ Couleur highlight (ex: fond jaune clair)

**How to verify (DB):**
```bash
php artisan tinker
# $boostAnnonce = Annonce::find(ID_BOOSTÉE)
# $boostAnnonce->isBoosted() → true
# $boostAnnonce->activeBoost() → Boost object
```

---

### D5. Expiration du boost
**Étapes:**
1. User PRO boost annonce → expires_at = 7 jours
2. Attendre 7 jours (ou fake)
3. Exécuter: `php artisan boosts:expire`

**Résultat attendu:**
- ✅ boost.status = 'expired'
- ✅ Annonce revient à tri normal
- ✅ Badge disparait
- ✅ Log: "Expired X boost(s)"

---

## E. EXPIRATION ABONNEMENT

### E1. Commande subscriptions:expire
**Étapes:**
1. User PRO avec sub expires_at = hier
2. Exécuter: `php artisan subscriptions:expire`

**Résultat attendu:**
- ✅ subscription.status = 'expired'
- ✅ Log: "Expired X subscription(s)"

---

### E2. Perte badge + droits
**Étapes:**
1. User PRO avec badge affiché
2. Sub expire (auto ou command)
3. Aller à `/annonces/create`

**Résultat attendu:**
- ✅ Badge PRO disparait
- ✅ Quota revient à 5

---

### E3. Blocage nouvelles annonces si > quota FREE
**Étapes:**
1. User PRO avec 30 annonces actives
2. Sub expire
3. Tente créer 31e

**Résultat attendu:**
- ✅ Bloqué: "Vous avez atteint votre limite de 5 annonces"
- ✅ Doit supprimer 25 annonces pour créer une nouvelle

---

## CONFIRMATIONS OBLIGATOIRES

### ❓ Le paiement est bien MANUEL uniquement ?
**Réponse:** 
- ✅ OUI, aucune intégration Stripe
- ✅ Utilisateur upload preuve paiement (jpg/png/pdf)
- ✅ Admin valide manuellement
- ✅ Abonnement s'active APRÈS validation, pas avant

### ❓ Aucune activation auto sans validation admin ?
**Réponse:**
- ✅ OUI, GARANTIE
- ✅ Au store(): payment_status = 'pending'
- ✅ Reste PENDING jusqu'à approveSubscription()
- ✅ Seul admin peut approuver via `/admin/subscriptions/{id}/approve`

### ❓ Quota annonces appliqué partout ?
**Réponse:**
- ✅ À VÉRIFIER: Dans AnnonceController::store() - À IMPLÉMENTER
- ✅ À VÉRIFIER: Dans API /api/annonces - À VÉRIFIER
- ✅ À VÉRIFIER: Dans edit (augmentation) - À IMPLÉMENTER

### ❓ Boost prioritaire dans recherche ?
**Réponse:**
- ✅ À VÉRIFIER: Code de tri dans AnnonceController::search() - À IMPLÉMENTER
- ✅ À VÉRIFIER: Badge affiché dans vue - À IMPLÉMENTER

### ❓ Scheduler d'expiration actif ?
**Réponse:**
- ⏳ À VÉRIFIER: Commandes créées (subscriptions:expire, boosts:expire)
- ⏳ À VÉRIFIER: Scheduler configuré dans app/Console/Kernel.php

---

## RÉSUMÉ FINAL

| Aspect | Status |
|--------|--------|
| Migrations (plans, subscriptions, boosts) | ✅ |
| Modèles + Relations | ✅ |
| Services (Subscription, Boost) | ✅ |
| Middleware Pro | ✅ |
| Routes PRO + Boost + Admin | ✅ |
| Contrôleurs CRUD | ⏳ À VÉRIFIER |
| Validation quotas annonces FREE | ❌ À IMPLÉMENTER |
| Validation quotas annonces PRO | ❌ À IMPLÉMENTER |
| Tri boosts dans recherche | ❌ À IMPLÉMENTER |
| Badge boost affiché | ❌ À IMPLÉMENTER |
| Scheduler d'expiration | ⏳ À VÉRIFIER |
| Logs temporaires | ✅ |
| Tests unitaires | ✅ |

