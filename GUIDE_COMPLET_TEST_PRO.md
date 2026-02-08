# 🧪 GUIDE COMPLET - TESTER TOUTES LES FONCTIONNALITÉS PRO EN LOCAL

## 📌 PRÉ-REQUIS

### 1. Serveur Laravel actif
```bash
cd c:\laragon\www\autodz
php artisan serve
# OU via Laragon: clic sur "Start All"
# URL: http://localhost:8000
```

### 2. Base de données à jour
```bash
php artisan migrate --fresh --seed
# Cree les tables + seeders (plans, brands, models)
```

### 3. Queue en mode sync (développement)
```php
// Vérifier dans .env:
QUEUE_CONNECTION=sync
# Les jobs s'exécutent immédiatement (pas d'attente)
```

### 4. Vérifier routes/scheduler
```bash
# Afficher routes PRO
php artisan route:list | Select-String "pro|boost|subscription"

# Afficher scheduler
php artisan schedule:list
# Doit montrer: subscriptions:expire et boosts:expire à 03:00
```

---

## 🎯 SCÉNARIO COMPLET DE TEST

### **ÉTAPE 1: CRÉER COMPTE FREE + TESTER QUOTA (5 annonces max)**

#### 1.1 Créer utilisateur FREE
```
URL: http://localhost:8000/register
- Email: freelancer@autodz.test
- Name: Free User Test
- Password: password123
- Phone: 0555111222
```

**Résultat attendu:** Email verification (vérifier inbox ou skipper si dev)

#### 1.2 Créer 5 annonces
```
URL: http://localhost:8000/annonces/create
```

**Remplir pour chaque annonce:**
```
Titre: Renault Clio 202X (varier les années/prix)
Prix: 1500000
Marque: Renault
Modèle: Clio
Carburant: Essence
Boîte: Manuelle
Véhicule: Voiture
Neuf: Non
```

**Répéter 5 fois**, cliquer "Publier" à chaque fois.

**✅ Résultat attendu:**
- 5 annonces créées avec `is_active = false` (en attente admin)
- Message: "Votre annonce a été soumise pour approbation"

#### 1.3 Vérifier QUOTA BLOQUÉ à la 6e annonce
```
URL: http://localhost:8000/annonces/create
```

**Remplir le formulaire pour 6e annonce, cliquer "Publier"**

**❌ Résultat attendu:**
```
Message d'erreur rouge:
"Vous avez atteint votre limite de 5 annonces actives. 
Passez à PRO pour publier jusqu'à 50 annonces !"
```

**Vérification en DB:**
```bash
php artisan tinker
$user = User::where('email', 'freelancer@autodz.test')->first();
$user->annonces()->count()  # Doit retourner 5
$user->isPro()              # Doit retourner false
exit
```

---

### **ÉTAPE 2: APPROUVER LES ANNONCES (pour qu'elles deviennent actives)**

#### 2.1 Créer compte ADMIN
```bash
php artisan tinker
$admin = User::create([
    'name' => 'Admin Test',
    'email' => 'admin@autodz.test',
    'password' => bcrypt('password123'),
    'is_admin' => true,
    'email_verified_at' => now(),
]);
exit
```

#### 2.2 Se connecter en ADMIN
```
URL: http://localhost:8000/login
- Email: admin@autodz.test
- Password: password123
```

#### 2.3 Approuver les annonces
```
URL: http://localhost:8000/admin/annonces
```

**Voir la liste de toutes les annonces pending.**

**Pour chaque annonce de "Free User Test":**
- Cliquer bouton "Approuver" / "Toggle"
- Status doit changer à `active` (vert)

**OU via Tinker (plus rapide):**
```bash
php artisan tinker
Annonce::where('user_id', User::where('email', 'freelancer@autodz.test')->first()->id)
    ->update(['is_active' => true]);
exit
```

**✅ Résultat attendu:** 5 annonces maintenant `is_active = true`

---

### **ÉTAPE 3: TESTER SYSTÈME PRO (Subscription PENDING → APPROVED)**

#### 3.1 Se reconnecter en FREE USER
```
URL: http://localhost:8000/logout
URL: http://localhost:8000/login
- Email: freelancer@autodz.test
- Password: password123
```

#### 3.2 Accéder à la page PRO
```
URL: http://localhost:8000/pro
```

**Affichage attendu:**
- Titre: "Plans PRO Autodz"
- Au moins 1 plan PRO visible
- Bouton "S'abonner" ou "Subscribe"

#### 3.3 S'abonner au plan PRO
```
Cliquer: "S'abonner" sous le plan
URL: http://localhost:8000/pro/subscribe/1
```

**Formulaire:**
- Sélectionner une image (JPG, PNG, PDF max 5MB)
- Cliquer: "Soumettre l'abonnement"

**✅ Résultat attendu:**
```
Message: "Votre demande d'abonnement a été soumise.
Elle sera examinée par nos administrateurs."

Redirection vers: /pro/status
Status: ⏳ EN ATTENTE (PENDING)
```

**Vérification en DB:**
```bash
php artisan tinker
$user = User::where('email', 'freelancer@autodz.test')->first();
$sub = $user->subscriptions()->latest()->first();
echo "Status: " . $sub->payment_status;        # pending
echo "Started: " . $sub->started_at;           # null
echo "Expires: " . $sub->expires_at;           # null
$user->isPro()                                  # false (PENDING ≠ PRO)
exit
```

#### 3.4 ADMIN approuve la subscription
```
Déconnexion + reconnexion avec admin@autodz.test
URL: http://localhost:8000/admin/subscriptions
```

**Voir la liste des subscriptions:**
- Trouver "Free User Test" avec status `PENDING`
- Cliquer: "Approuver" / "Approve"

**OU via Tinker (plus rapide):**
```bash
php artisan tinker
$subscriptionService = app(App\Services\SubscriptionService::class);
$sub = Subscription::where('payment_status', 'pending')->latest()->first();
$subscriptionService->approveSubscription($sub);
echo "✅ Subscription approuvée!";
exit
```

**✅ Résultat attendu:**
```
Status: ✅ APPROUVÉ (APPROVED) - badge vert
Started: 2026-02-08 14:30:00 (date d'aujourd'hui)
Expires: 2026-03-10 14:30:00 (30 jours après)
```

#### 3.5 Vérifier que user est maintenant PRO
```bash
php artisan tinker
$user = User::where('email', 'freelancer@autodz.test')->first();
$user->isPro()                              # TRUE ✅
$user->subscriptions()->first()->payment_status  # approved
exit
```

**OU via interface:**
```
URL: http://localhost:8000/pro/status (connecté en FREE USER)
Status: ✅ ACTIF (APPROVED) - badge vert
```

---

### **ÉTAPE 4: TESTER CRÉATION D'ANNONCES PRO (50 max au lieu de 5)**

#### 4.1 Créer 2 annonces supplémentaires en tant que PRO
```
URL: http://localhost:8000/annonces/create
```

**Créer 2 nouvelles annonces (même process qu'avant)**

**✅ Résultat attendu:**
- Annonces 6 et 7 créées ✅
- **Pas de message d'erreur** (quota PRO = 50)
- Les 2 annonces s'ajoutent aux 5 existantes

**Vérification:**
```bash
php artisan tinker
$user = User::where('email', 'freelancer@autodz.test')->first();
$user->annonces()->count()  # Doit montrer 7
$user->isPro()              # TRUE
exit
```

---

### **ÉTAPE 5: TESTER BOOST (Propriétaire PRO)**

#### 5.1 Ajouter le bouton BOOST à la vue (temporaire)

D'abord, ouvrir [resources/views/annonces/show.blade.php](resources/views/annonces/show.blade.php) et localiser la section des boutons (autour ligne 280-290).

Ajouter ce code après les autres boutons (avant `@endauth`):

```blade
{{-- BOOST BUTTON --}}
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
                        class="w-full py-2 rounded-full bg-pink-600 text-white text-xs font-semibold hover:bg-pink-700 transition">
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

#### 5.2 Approuver les 2 nouvelles annonces
```
Admin panel: /admin/annonces
→ Approuver annonces #6 et #7
```

#### 5.3 Accéder à une annonce et cliquer BOOST
```
Se reconnecter en FREE USER (si déconnecté)
URL: http://localhost:8000/annonces/1
(ou numéro d'une de vos annonces)
```

**Vous voyez:**
- ✅ Bouton rose: **"⭐ Booster cette annonce (7 jours)"**

**Cliquer le bouton**

**✅ Résultat attendu:**
```
Message vert: "Votre annonce a été boostée pour 7 jours !"
Page rechargée
Boost enregistré en BD
```

**Vérification en DB:**
```bash
php artisan tinker
$boost = Boost::latest()->first();
echo "Annonce: " . $boost->annonce_id;
echo "User: " . $boost->user_id;
echo "Started: " . $boost->started_at;        # Aujourd'hui
echo "Expires: " . $boost->expires_at;        # +7 jours
echo "Status: " . $boost->status;             # active
exit
```

---

### **ÉTAPE 6: TESTER TRI PRIORITAIRE (Boostées d'abord)**

#### 6.1 Aller à la recherche
```
URL: http://localhost:8000/recherche
```

**Affichage:**
- **PREMIÈRE annonce = celle que vous venez de booster** ⭐
- Les autres annonces après

**Vérification visuelle:** Votre annonce boostée doit être EN HAUT de la liste

#### 6.2 Trier par prix / année (le boost reste prioritaire)
```
Sélectionner un tri: "Prix ↑", "Année ↓", etc.
```

**✅ Résultat attendu:**
- L'annonce boostée reste EN PREMIER
- Les autres annonces triées après par le critère choisi

**Logique:** Boost = priorité absolue, puis tri secondaire s'applique

---

### **ÉTAPE 7: TESTER EXPIRATION (Boost + Subscription)**

#### 7.1 Tester expiration BOOST (7 jours → expiration)
```bash
# Forcer l'expiration du boost (dev only):
php artisan tinker
$boost = Boost::latest()->first();
$boost->update(['expires_at' => now()->subMinutes(1)]);  # Expiration passée
$boost->status = 'expired';
$boost->save();
exit
```

**Vérifier que le boost n'apparaît plus au premier rang:**
```
URL: http://localhost:8000/recherche
→ Annonce boostée doit être APRÈS les autres
```

#### 7.2 Tester expiration SUBSCRIPTION (30 jours → expiration)
```bash
php artisan tinker
$sub = Subscription::latest()->first();
$sub->update(['expires_at' => now()->subMinutes(1)]);  # Expiration passée
$sub->save();
exit
```

**Vérifier que user n'est plus PRO:**
```bash
php artisan tinker
$user = User::where('email', 'freelancer@autodz.test')->first();
$user->refresh();
$user->isPro()  # FALSE (subscription expirée)
exit
```

**OU via interface:**
```
URL: http://localhost:8000/pro/status
Status: ❌ EXPIRÉ (ou pas visible)
```

---

### **ÉTAPE 8: TESTER FREE USER NE PEUT PAS BOOSTER**

#### 8.1 Créer 2e compte FREE (sans PRO)
```
URL: http://localhost:8000/register
- Email: freeuser2@autodz.test
- Name: Free User 2
```

#### 8.2 Créer annonce
```
URL: http://localhost:8000/annonces/create
(créer 1 annonce)
```

#### 8.3 Approuver l'annonce (admin)
```
Admin: /admin/annonces → Approuver
```

#### 8.4 Tenter de booster en tant que FREE
```
URL: http://localhost:8000/annonces/{ID}
```

**Vous voyez:**
- ❌ **Bouton grisé:** "⭐ Booster (PRO requis)"
- Pas possible de cliquer

**✅ Résultat attendu:** FREE user ne peut jamais booster ✅

---

## 📋 CHECKLIST FINALE

```
QUOTA FREE:
  ☐ 5e annonce créée ✅
  ☐ 6e annonce bloquée ❌
  
SUBSCRIPTION:
  ☐ Demande créée (PENDING)
  ☐ Status montre "EN ATTENTE"
  ☐ Admin approuve
  ☐ Status change à "APPROUVÉ"
  ☐ Dates affichées (started + expires)
  ☐ user.isPro() = true ✅
  
QUOTA PRO:
  ☐ 6e annonce créée après approbation ✅
  ☐ 7e annonce créée ✅
  ☐ Peut créer jusqu'à 50
  
BOOST:
  ☐ Bouton visible pour PRO user
  ☐ Annonce boostée (7 jours)
  ☐ Message success affiché
  ☐ Enregistrement en BD (boosts table)
  
TRI:
  ☐ Annonce boostée EN PREMIER dans recherche
  ☐ Reste prioritaire même en changeant le tri
  
EXPIRATION:
  ☐ Boost expiré → annonce redescend
  ☐ Subscription expirée → isPro() = false
  
SÉCURITÉ:
  ☐ FREE user ne voit pas bouton boost
  ☐ FREE user ne peut pas booster (même force)
  ☐ User ne peut booster que ses propres annonces
```

---

## 🛠️ COMMANDES UTILES

### Reset complet (dev)
```bash
php artisan migrate:fresh --seed
# Recrée toutes les tables
```

### Voir logs en temps réel
```bash
# Ouvrir 2e terminal
php artisan queue:listen
# Ou en Windows:
Get-Content storage\logs\laravel.log -Wait -Tail 20
```

### Tester une commande d'expiration
```bash
php artisan subscriptions:expire
php artisan boosts:expire
```

### Vérifier les données
```bash
php artisan tinker

# Users
User::all();
User::where('email', 'freelancer@autodz.test')->first();

# Subscriptions
Subscription::with('user')->get();
Subscription::where('payment_status', 'pending')->get();

# Boosts
Boost::with(['annonce', 'user'])->get();
Boost::where('status', 'active')->get();

# Annonces
Annonce::where('user_id', 1)->get();
Annonce::where('is_active', true)->count();

exit
```

### Afficher routes PRO
```bash
php artisan route:list | Select-String "pro|boost|subscription"
```

### Afficher scheduler
```bash
php artisan schedule:list
```

---

## 🚀 RÉSUMÉ EN 10 ÉTAPES

1. ✅ `php artisan migrate --fresh --seed`
2. ✅ Créer compte FREE
3. ✅ Créer 5 annonces
4. ✅ 6e annonce bloquée
5. ✅ Approuver 5 annonces (admin)
6. ✅ Créer subscription PRO (PENDING)
7. ✅ Approuver subscription (APPROVED)
8. ✅ Tester boost (7 jours)
9. ✅ Vérifier tri recherche (boostées en premier)
10. ✅ Tester expiration

**Durée totale:** ~20 minutes ⏱️

Bon test! 🎯
