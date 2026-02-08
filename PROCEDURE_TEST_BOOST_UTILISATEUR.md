# 🚗 PROCÉDURE DE TEST BOOST - UTILISATEUR LAMBDA

## ⚠️ FLUX OFFICIEL (100% MANUEL - SANS CONTOURNEMENT TECHNIQUE)

Vous allez tester l'**intégrité complète** du système de boost PRO avec le flux réel "paiement manuel".

---

## **PHASE 1️⃣ : COMPTE FREE (Aucun droit PRO)**

### Étape 1.1 : Créer un compte utilisateur
```
URL: http://localhost/register
```

**Formulaire à remplir:**
- Name: `Test User`
- Email: `testuser@autodz.test` (n'importe quel email unique)
- Password: `password123`
- Phone: `0555123456`
- Cocher "I agree to the terms" si nécessaire

**Cliquer:** Bouton "Register"

**✅ Résultat attendu:**
- Page de vérification email (ou redirection vers dashboard)
- Vous êtes connecté en tant que **FREE USER**
- Status: `isPro = NO`

---

### Étape 1.2 : Créer une annonce
```
URL: http://localhost/annonces/create
```

**Formulaire (remplir les champs obligatoires):**
- Titre: `Renault Clio 2020`
- Prix: `1500000`
- Marque: `Renault`
- Modèle: `Clio`
- Carburant: `Essence`
- Boîte de vitesses: `Manuelle`
- Type de véhicule: `Voiture`
- Condition (neuf?): `Non`

**Cliquer:** Bouton "Publier l'annonce"

**✅ Résultat attendu:**
- Annonce créée avec statut `is_active = false` (en attente approbation admin)
- Message: "Votre annonce a été soumise pour approbation"
- Page de l'annonce affichée

---

### Étape 1.3 : Vérifier que BOOST est IMPOSSIBLE
```
URL: http://localhost/annonces/{ID_ANNONCE}
```

**Où `{ID_ANNONCE}` = numéro de votre annonce (ex: /annonces/15)**

**Rechercher sur la page:**
- ❌ **Il N'Y A PAS de bouton "Booster cette annonce"**
- ❌ **Il N'Y A PAS de bouton "Activer PRO"**

**À la place, vous voyez:**
- Un panneau "Conseils Caro" (à droite)
- Boutons: "Appeler le vendeur", "Ajouter aux favoris"
- **AUCUN bouton de boost visible**

**✅ Résultat attendu:**
- Les utilisateurs FREE n'ont pas accès au boost
- C'est **NORMAL**, c'est ce qu'on teste!

---

## **PHASE 2️⃣ : DEMANDE PRO (Subscription PENDING)**

### Étape 2.1 : Accéder au page PRO
```
URL: http://localhost/pro
```

**Page affichée:**
- Titre: "Plans PRO Autodz"
- Description: "Augmentez la visibilité de vos annonces"
- **Un plan PRO proposé** avec tarif (ex: "Plan PRO - X DA")

---

### Étape 2.2 : S'abonner au plan PRO
**Cliquer:** Bouton "S'abonner" ou "Subscribe" sous le plan PRO

```
URL: http://localhost/pro/subscribe/1
```

**Formulaire:**
- Titre: "Abonnement PRO - Mensuel"
- **Zone de téléchargement:** "Téléchargez une preuve de paiement"

**Actions:**
1. Sélectionner un fichier image quelconque (JPG, PNG) de votre ordinateur
   - Peut être: screenshot, photo, n'importe quelle image
   - Max 5 MB
2. **Cliquer:** Bouton "Soumettre l'abonnement"

**✅ Résultat attendu:**
- Message: "Votre demande d'abonnement a été soumise. Elle sera examinée par nos administrateurs."
- Page redirect vers `/pro/status`

---

### Étape 2.3 : Vérifier status PENDING
```
URL: http://localhost/pro/status
```

**Affichage:**
- Titre: "Statut de votre abonnement"
- Status: **`⏳ EN ATTENTE (PENDING)`** - badge jaune/gris
- Aucune date d'activation
- Message: "Veuillez patienter pendant l'examen par l'administrateur"

**Tentative de boost maintenant:**
- Aller à votre annonce: `/annonces/{ID}`
- ❌ **Toujours AUCUN bouton de boost**
- ✅ C'est CORRECT car subscription = PENDING (pas encore approuvée)

---

## **PHASE 3️⃣ : VALIDATION ADMIN (Approbation manuelle)**

### Étape 3.1 : Créer/utiliser un compte ADMIN
```
URL: http://localhost/login
```

**Option A - Compte ADMIN existant:**
- Email: `admin@autodz.test` (ou votre compte admin)
- Password: `password123`
- Cliquer: "Se connecter"

**Option B - Promouvoir votre compte en ADMIN (développeur uniquement):**
```bash
php artisan tinker
$user = \App\Models\User::where('email', 'testuser@autodz.test')->first();
$user->is_admin = true;
$user->save();
exit
```
*(Puis reconnectez-vous)*

---

### Étape 3.2 : Approuver la subscription
```
URL: http://localhost/admin/subscriptions
```

**Page affichée:**
- Liste de **toutes les subscriptions**
- Colonnes: User, Status, Proof, Action

**Rechercher:**
- La subscription de `testuser@autodz.test`
- Status: **`PENDING`** (jaune)

**Cliquer:** Bouton "Approver" ou "Valider" sur cette ligne

---

### Étape 3.3 : Confirmer l'approbation
**Pop-up ou formulaire:**
- Message: "Êtes-vous sûr d'approuver cette subscription?"
- Cliquer: "Confirmer" ou "Approuver"

**✅ Résultat attendu:**
- Subscription status change: **`APPROVED`** (vert)
- Dates affichées:
  - Started: 2026-02-08 (date d'aujourd'hui)
  - Expires: 2026-03-10 (30 jours plus tard)

---

## **PHASE 4️⃣ : TEST BOOST RÉEL (User PRO)**

### Étape 4.1 : Se reconnecter en tant qu'utilisateur normal
```
URL: http://localhost/logout
```
**Puis:**
```
URL: http://localhost/login
- Email: testuser@autodz.test
- Password: password123
```

---

### Étape 4.2 : Vérifier status PRO
```
URL: http://localhost/pro/status
```

**Affichage:**
- Status: **`✅ ACTIF (APPROVED)`** - badge vert
- Dates affichées:
  - Actif depuis: 2026-02-08 13:29:21
  - Expire le: 2026-03-10 13:29:21
- Message: "Votre abonnement PRO est actif!"

---

### Étape 4.3 : Ouvrir votre annonce
```
URL: http://localhost/annonces/{ID_ANNONCE}
```

**Vérifications:**
- Vous voyez votre annonce "Renault Clio 2020"
- Panel de droite: "Conseils Caro"

---

### Étape 4.4 : Chercher le bouton BOOST 🎯

**❌ IMPORTANT:** Le bouton **N'EXISTE PAS ENCORE** dans le template!

Il faut l'ajouter manuellement dans la vue.

**Pour tester le flux complet**, ajoutez ce bouton temporairement à [resources/views/annonces/show.blade.php](resources/views/annonces/show.blade.php#L290):

```blade
{{-- BOOST BUTTON (à ajouter après les autres boutons) --}}
@auth
    @if(auth()->id() === $annonce->user_id)
        {{-- Own annonce - show boost button if PRO --}}
        @php
            $subscriptionService = app(\App\Services\SubscriptionService::class);
            $isPro = $subscriptionService->userIsPro(auth()->user());
        @endphp
        
        @if($isPro)
            <form method="POST" action="{{ route('annonces.boost', $annonce) }}" class="mt-2">
                @csrf
                <button type="submit"
                        class="w-full py-2 rounded-full bg-pink-600 text-white text-xs font-semibold hover:bg-pink-700">
                    ⭐ Booster cette annonce (7 jours)
                </button>
            </form>
        @else
            <button type="button" disabled
                    class="w-full py-2 rounded-full bg-gray-200 text-gray-500 text-xs font-semibold cursor-not-allowed mt-2">
                ⭐ Booster (PRO requis)
            </button>
        @endif
    @endif
@endauth
```

---

### Étape 4.5 : CLIQUER SUR BOOSTER

**Vous voyez:**
- Bouton rose/pink: **"⭐ Booster cette annonce (7 jours)"**

**Cliquer:** Bouton "Booster"

**✅ Résultat attendu:**
- Message success: "Votre annonce a été boostée pour 7 jours !"
- Page rechargée
- Boost enregistré en base de données

---

## **PHASE 5️⃣ : VÉRIFICATION VISUELLE DU TRI BOOST**

### Étape 5.1 : Aller à la recherche
```
URL: http://localhost/recherche
```

**Affichage:**
- Liste d'annonces triées par **"Boostées d'abord"**

**Ordre attendu:**
1. ⭐ **Votre annonce boostée** (Renault Clio 2020) - EN HAUT
2. Autres annonces non boostées
3. ...

**Signe visuel:** Votre annonce boostée doit apparaître **EN PREMIER** dans les résultats

---

## **PHASE 6️⃣ : TEST D'ERREUR (FREE essaie de booster)**

### Créer un 2e utilisateur FREE
```
URL: http://localhost/register
- Email: freeuser@autodz.test
- Name: Free User
- Password: password123
```

### Créer une annonce avec ce compte FREE
```
URL: http://localhost/annonces/create
- (remplir formulaire comme phase 1)
```

### Essayer de booster SANS subscription PRO
```
URL: http://localhost/annonces/{ID_ANNONCE}
```

**❌ Résultat attendu:**
- Aucun bouton "Booster" visible
- OU bouton grisé: "⭐ Booster (PRO requis)"
- Message d'erreur impossible via UI

**Tentative directe (développeur):**
```bash
curl -X POST http://localhost/annonces/123/boost \
  -H "Authorization: Bearer {TOKEN}" \
  -d ""
```

**❌ Réponse attendue:**
```json
{
  "error": "Vous devez avoir un abonnement PRO actif pour booster une annonce."
}
```

---

## **CHECKLIST FINALE**

- [ ] ✅ Phase 1: Compte FREE créé, pas de boost visible
- [ ] ✅ Phase 2: Subscription PENDING créée, toujours pas de boost
- [ ] ✅ Phase 3: Admin approuve, subscription = APPROVED
- [ ] ✅ Phase 4: Bouton boost visible pour PRO user
- [ ] ✅ Phase 4: Boost réussi (7 jours)
- [ ] ✅ Phase 5: Annonce boostée remonte dans recherche
- [ ] ✅ Phase 6: FREE user ne peut pas booster

---

## **INFORMATIONS TECHNIQUES**

| Élément | Valeur |
|---------|--------|
| **Route boost** | `POST /annonces/{id}/boost` |
| **Controller** | `BoostController@store` | 
| **View** | `resources/views/annonces/show.blade.php` |
| **Service** | `BoostService@canBoost()`, `boostAnnonce()` |
| **Durée boost** | 7 jours (`addDays(7)`) |
| **Condition 1** | User = PRO actif (`isPro() = true`) |
| **Condition 2** | User = propriétaire annonce |
| **Condition 3** | Annonce n'est pas déjà boostée |
| **Table** | `boosts` (id, user_id, annonce_id, started_at, expires_at, status) |
| **Tri recherche** | `LEFT JOIN boosts` + `ORDER BY CASE WHEN boosts.id IS NOT NULL THEN 0 ELSE 1 END` |

---

## **MESSAGES D'ERREUR POSSIBLES**

| Situation | Message |
|-----------|---------|
| FREE user tente boost | "Vous devez avoir un abonnement PRO actif pour booster une annonce." |
| User non-propriétaire tente boost | "Vous ne pouvez booster que vos propres annonces." |
| Annonce déjà boostée | "Cette annonce est déjà boostée." |
| Quota mensuel atteint | "Vous avez atteint votre quota de boosts par mois." |

---

## **POINTS CLÉS DU FLUX MANUEL**

1. **Aucune automation** → Admin approuve MANUELLEMENT chaque demande
2. **Pas de paiement réel** → Preuve de paiement = simple upload image
3. **Status PENDING obligatoire** → Aucun droit PRO tant que PENDING
4. **Dates définies à l'approbation** → Pas de dates si PENDING
5. **Boost = privilège PRO** → Imposible pour FREE users

---

## 🎯 RÉSUMÉ 

**C'est un vrai test client:**
- Pas de script auto
- Pas de Tinker (sauf admin setup)
- Pas de contournement middleware
- Flux 100% réel = "comment un client paierait et démarrerait"

**Vous validez:**
✅ Créer compte FREE  
✅ Soumettre subscription (PENDING)  
✅ Admin approuve (APPROVED)  
✅ Boost fonctionne (7 jours)  
✅ Annonce remonte dans recherche  

Bon test! 🚗💪
