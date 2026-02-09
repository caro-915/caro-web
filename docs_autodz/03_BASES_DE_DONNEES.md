# 💾 Base de Données - Documentation Complète

**Importance:** 🔴 CRITIQUE  
**Audience:** Développeurs, DevOps, DBAs  
**Dernière mise à jour:** Février 2026


---

## 📊 Vue d'ensemble du schéma

```
USERS (Authentification)
  ├── ANNONCES (Listings)
  │    ├── FAVORITES (User ⭐ Annonce)
  │    ├── MESSAGES (Direct messages)
  │    │    └── CONVERSATIONS (Threads)
  │    └── BOOSTS (PRO: Featured)
  ├── SUBSCRIPTIONS (PRO: Paiements)
  │    └── PLANS (PRO: Tier pricing)
  └── CONVERSATIONS (Buyer/Seller)
```


---

## 🗂️ Toutes les Tables

| Numéro | Table | Rôle | Statut |
|---|---|---|---|
| 1 | `users` | Utilisateurs (FREE/PRO/ADMIN) | ✅ Core |
| 2 | `annonces` | Listings véhicules | ✅ Core |
| 3 | `car_brands` | Marques automobiles | ✅ Lookup |
| 4 | `car_models` | Modèles automobiles | ✅ Lookup |
| 5 | `favorites` | Favoris de l'utilisateur | ✅ Feature |
| 6 | `conversations` | Threads de messages | ✅ Feature |
| 7 | `messages` | Messages individuels | ✅ Feature |
| 8 | `plans` | Tiers d'abonnement (PRO) | ✅ PRO System |
| 9 | `subscriptions` | Abonnements actifs (PRO) | ✅ PRO System |
| 10 | `boosts` | Boosts annonce (PRO) | ✅ PRO System |
| 11 | `annonce_deletions` | Log des annonces supprimées | ✅ Audit |
| 12 | `migrations` | Historique migrations | ✅ Laravel |
| 13 | `jobs` | Queue asynchrone | ✅ Queue |
| 14 | `job_batches` | Batch jobs | ✅ Queue |
| 15 | `cache` | Cache entries | ✅ Cache |
| 16 | `sessions` | Sessions utilisateur | ✅ Session |

**Total:** 16 tables (12 métier + 4 framework)


---

## 📋 Détail de Chaque Table

### 1. 👤 TABLE: `users`

**Fichier migration:** `database/migrations/0001_01_01_000000_create_users_table.php`

**Rôle:** Stocker tous les utilisateurs (visiteurs enregistrés)

#### Colonnes

| Colonne | Type | Null | Unique | Default | Index | Rôle |
|---------|------|------|--------|---------|-------|------|
| `id` | BIGINT | No | Yes | AUTO_INCREMENT | PK | Primary key |
| `name` | VARCHAR(255) | No | No | NULL | No | Nom d'affichage |
| `email` | VARCHAR(255) | No | Yes | NULL | Yes | Email unique |
| `email_verified_at` | TIMESTAMP | Yes | No | NULL | No | Email vérifié? |
| `password` | VARCHAR(255) | No | No | NULL | No | Hash bcrypt |
| `remember_token` | VARCHAR(100) | Yes | No | NULL | No | Token "Se souvenir" |
| `phone` | VARCHAR(20) | Yes | No | NULL | No | Numéro téléphone |
| `google_id` | VARCHAR(255) | Yes | No | NULL | No | OAuth Google ID |
| `avatar` | VARCHAR(255) | Yes | No | NULL | No | URL/chemin avatar |
| `is_admin` | BOOLEAN | No | No | 0 | No | Est administrateur? |
| `is_banned` | BOOLEAN | No | No | 0 | No | Compte banni? |
| `created_at` | TIMESTAMP | No | No | CURRENT_TIMESTAMP | No | Date création |
| `updated_at` | TIMESTAMP | No | No | CURRENT_TIMESTAMP | No | Date modification |

#### Relations

```
User hasMany Annonce
User hasMany Subscription
User hasMany Boost
User hasMany Favorite
User hasMany Conversation (as buyer_id)
User hasMany Conversation (as seller_id)
User belongsToMany Annonce (via favorites)
```

#### Statuts Utilisateur

```
┌──────────────────────────────────────────┐
│ Status utilisateur (logique)             │
├──────────────────────────────────────────┤
│                                           │
│ 1. VISITEUR (non inscrit)                 │
│    ├─ email_verified_at = NULL            │
│    ├─ Peut voir annonces publiques        │
│    └─ Pas de création annonce             │
│                                           │
│ 2. USER NON VÉRIFIÉ (après signup)        │
│    ├─ email_verified_at = NULL            │
│    ├─ A reçu email de vérification        │
│    └─ Attend clic sur lien email          │
│                                           │
│ 3. USER FREE (vérifié, sans PRO)          │
│    ├─ email_verified_at = 2026-02-01     │
│    ├─ is_admin = 0, is_banned = 0       │
│    ├─ Quota 5 annonces                   │
│    ├─ Pas de boosts                      │
│    └─ Peut messages, favoris              │
│                                           │
│ 4. USER PRO (abonné actif)                │
│    ├─ email_verified_at = 2026-02-01     │
│    ├─ subscriptions.payment_status = 'approved'
│    ├─ subscriptions.expires_at > now()   │
│    ├─ Quota 10 annonces                  │
│    ├─ 5 boosts/mois                      │
│    └─ isPro() = true                     │
│                                           │
│ 5. USER ADMIN (gestionnaire)              │
│    ├─ is_admin = 1                        │
│    ├─ Accès /admin/*                     │
│    ├─ Approuver annonces                 │
│    ├─ Valider paiements PRO              │
│    └─ Gérer utilisateurs & bans          │
│                                           │
│ 6. USER BANNI (privé de droits)           │
│    ├─ is_banned = 1                       │
│    ├─ Peut pas créer annonces             │
│    ├─ Peut pas envoyer messages           │
│    ├─ Se fait auto-logout                 │
│    └─ Voir page erreur "Compte banni"     │
└──────────────────────────────────────────┘
```

#### Méthodes Model utiles

```php
// app/Models/User.php

public function isPro(): bool {
    // TRUE si user a une subscription active approuvée et valide
    return $this->activeSubscription() !== null;
}

public function activeSubscription(): ?Subscription {
    // Récupère la subscription active (la plus récente)
    return $this->subscriptions()
        ->where('status', 'active')
        ->where('payment_status', 'approved')
        ->where('expires_at', '>', now())
        ->latest()
        ->first();
}

public function annonces() {
    // Les annonces de cet utilisateur (toutes)
    return $this->hasMany(Annonce::class);
}

public function boosts() {
    // Les boosts créés par cet utilisateur
    return $this->hasMany(Boost::class);
}
```

#### Exemples de requêtes

```sql
-- Tous les users NON bannés
SELECT * FROM users WHERE is_banned = 0;

-- Tous les admins
SELECT * FROM users WHERE is_admin = 1;

-- Users avec email vérifié
SELECT * FROM users WHERE email_verified_at IS NOT NULL;

-- Users inscrits depuis 30 jours
SELECT * FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

**Taille DB (estimation):** 1-2 KB par user


---

### 2. 📰 TABLE: `annonces`

**Fichier migration:** `database/migrations/2025_12_08_140327_create_annonces_table.php` + extensions

**Rôle:** Stocker les listings de véhicules (cœur du business)

#### Colonnes

| Colonne | Type | Null | Unique | Default | Index | Rôle |
|---------|------|------|--------|---------|-------|------|
| `id` | BIGINT | No | Yes | AUTO_INCREMENT | PK | Primary key |
| `user_id` | BIGINT | No | No | NULL | FK,Index | Propriétaire |
| `titre` | VARCHAR(255) | No | No | NULL | Index | Titre annonce |
| `description` | LONGTEXT | Yes | No | NULL | No | Description complète |
| `prix` | INTEGER | No | No | NULL | **fulltext** | Prix en DZD |
| `marque` | VARCHAR(100) | No | No | NULL | Index | Marque véh. (Renault, etc.) |
| `modele` | VARCHAR(100) | Yes | No | NULL | Index | Modèle (Clio, etc.) |
| `annee` | INTEGER | Yes | No | NULL | Index | Année fabrication |
| `kilometrage` | INTEGER | Yes | No | NULL | No | KM au compteur |
| `carburant` | VARCHAR(50) | No | No | NULL | Index | Essence/Diesel/Électrique |
| `boite_vitesse` | VARCHAR(50) | No | No | NULL | Index | Manuelle/Auto |
| `ville` | VARCHAR(100) | Yes | No | NULL | Index | Wilaya (Alger, Oran) |
| `vehicle_type` | VARCHAR(50) | No | No | NULL | Index | Voiture/Moto/Utilitaire |
| `condition` | VARCHAR(10) | No | No | "non" | No | Neuf (oui) ou Occas (non) |
| `couleur` | VARCHAR(50) | Yes | No | NULL | No | Couleur véhicule |
| `document_type` | VARCHAR(50) | Yes | No | NULL | No | Carte grise/Procuration |
| `finition` | VARCHAR(80) | Yes | No | NULL | No | Finition (Base, Luxe, etc.) |
| `show_phone` | BOOLEAN | No | No | 1 | No | Afficher tél. annonce? |
| `image_path` | VARCHAR(255) | Yes | No | NULL | No | Image 1 |
| `image_path_2` | VARCHAR(255) | Yes | No | NULL | No | Image 2 |
| `image_path_3` | VARCHAR(255) | Yes | No | NULL | No | Image 3 |
| `image_path_4` | VARCHAR(255) | Yes | No | NULL | No | Image 4 |
| `image_path_5` | VARCHAR(255) | Yes | No | NULL | No | Image 5 |
| `is_active` | BOOLEAN | No | No | 0 | Index | Visible? (approuvée) |
| `views` | INTEGER | No | No | 0 | No | Compteur de vues |
| `created_at` | TIMESTAMP | No | No | CURRENT_TIMESTAMP | Index | Date création |
| `updated_at` | TIMESTAMP | No | No | CURRENT_TIMESTAMP | No | Date dernière modif |

#### Relations

```
Annonce belongsTo User (user_id)
Annonce belongsTo CarBrand (marque -> car_brands.name)
Annonce belongsTo CarModel (modele -> car_models.name)
Annonce hasMany Favorite
Annonce hasMany Boost
Annonce hasMany Conversation
Annonce belongsToMany User (via favorites)
```

#### Statuts Annonce

```
┌──────────────────────────────────────────────────┐
│ Status annonce (logique)                         │
├──────────────────────────────────────────────────┤
│                                                   │
│ 1. BROUILLON (avant upload complet)              │
│    ├─ Annonce en création (form)                 │
│    ├─ Pas encore sauvegardée                     │
│    └─ Visible uniquement au user                 │
│                                                   │
│ 2. EN ATTENTE D'APPROBATION (admin)              │
│    ├─ is_active = 0                               │
│    ├─ Sauvegardée en BD                          │
│    ├─ Visible dans admin panel                  │
│    ├─ Pas visible aux visiteurs                 │
│    └─ Admin reviewa content avant activation    │
│                                                   │
│ 3. ACTIVE (Visible au public)                    │
│    ├─ is_active = 1                              │
│    ├─ Visible sur home & recherche               │
│    ├─ Compteur views > 0                         │
│    ├─ Messages peuvent être envoyés              │
│    └─ User compte dans quota                     │
│                                                   │
│ 4. DÉSACTIVÉE (Admin ou user)                    │
│    ├─ is_active = 0 (après avoir été 1)         │
│    ├─ Pas visible au public                      │
│    ├─ Données préservées en BD                  │
│    ├─ Peut être réactivée                        │
│    └─ Cause: Suspicion fraude ou user request   │
│                                                   │
│ 5. SUPPRIMÉE (Soft/Hard delete)                  │
│    ├─ Record deleted from annonces               │
│    ├─ Log créé dans annonce_deletions            │
│    ├─ Images supprimées du storage               │
│    ├─ Conversations gardées (historique)         │
│    └─ was_sold flag = true/false                 │
│                                                   │
└──────────────────────────────────────────────────┘
```

#### Flux d'une annonce

```plaintext
User crée annonce (form)
  ↓ (images uploadées)
  ├─ est validé
  ├─ enregistré en BD (is_active=false)
  ├─ images traitées (resize, watermark async)
  ├─ user voit "En attente d'approbation"
  │
  └─→ Admin dashboard
      ├─ Déroule annonce en attente
      ├─ Vérife contenu
      │
      └─→ Approbation
          ├─ VALIDE → SET is_active=1 → PUBLIÉE
          └─ INVALIDE → SET is_active=-1 → REJETÉE
      
User voit annonce active
  ├─ Click from home / search
  ├─ View counter incremented (1x/session)
  └─ Peut envoyer message au seller
```

#### Statuts Colonnes Enum

```php
// carburant
'Essence', 'Diesel', 'Hybride', 'Électrique', 'Gaz'

// boite_vitesse
'Manuelle', 'Automatique'

// vehicle_type
'Voiture', 'Utilitaire', 'Moto'

// condition
'oui' (neuf), 'non' (occasion)

// document_type
'carte_grise', 'procuration'
```

#### Méthodes Model utiles

```php
// app/Models/Annonce.php

public function user() {
    return $this->belongsTo(User::class);
}

public function isBoosted(): bool {
    // TRUE si this annonce a un boost actif
    return $this->activeBoost() !== null;
}

public function activeBoost(): ?Boost {
    // Récupère le dernier boost actif
    return $this->boosts()
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->latest()
        ->first();
}

public function scopeActive($query) {
    return $query->where('is_active', 1);
}

public function scopeFilter($query, array $filters) {
    // Filtrer par marque, prix, etc.
    if (!empty($filters['marque'])) {
        $query->where('marque', 'like', '%' . $filters['marque'] . '%');
    }
    if (!empty($filters['price_max'])) {
        $query->where('prix', '<=', $filters['price_max']);
    }
    // ... plus de filtres
    return $query;
}
```

#### Requêtes courantes

```sql
-- Annonces actives (visibles au public)
SELECT * FROM annonces WHERE is_active = 1 ORDER BY created_at DESC LIMIT 20;

-- Annonces en attente d'approbation (admin)
SELECT * FROM annonces WHERE is_active = 0 AND deleted_at IS NULL;

-- Annonces d'un utilisateur spécifique
SELECT * FROM annonces WHERE user_id = 5 AND is_active = 1;

-- Annonces Renault Clio, max 2000000 DZD
SELECT * FROM annonces 
WHERE marque = 'Renault' 
  AND modele = 'Clio' 
  AND prix <= 2000000
  AND is_active = 1;

-- Top annonces par vues
SELECT * FROM annonces 
WHERE is_active = 1 
ORDER BY views DESC 
LIMIT 10;

-- Annonces boostées (avec boost actif)
SELECT a.* FROM annonces a
INNER JOIN boosts b ON a.id = b.annonce_id
WHERE b.status = 'active' 
  AND b.expires_at > NOW()
  AND a.is_active = 1;

-- Annonces d'un user PRO (10 limit check)
SELECT a.* FROM annonces a
WHERE a.user_id = 5 
  AND a.is_active = 1 
  AND a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
LIMIT 10;
```

**Taille DB (estimation):** 5-10 KB par annonce (sans images)


---

### 3. 🏷️ TABLE: `car_brands`

**Fichier migration:** `database/migrations/2025_12_08_195204_create_car_brands_table.php`

**Rôle:** Lookup table pour les marques automobiles

#### Colonnes

| Colonne | Type | Null | Props |
|---------|------|------|-------|
| `id` | INT | No | PK |
| `name` | VARCHAR(100) | No | Unique, Indexed |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Données Initiales

```sql
SELECT * FROM car_brands LIMIT 10;
-- id | name | created_at
-- 1  | Renault
-- 2  | Peugeot
-- 3  | Citroën
-- 4  | Hyundai
-- 5  | Dacia
-- ...
```

#### Relations

```
CarBrand hasMany CarModel
CarBrand hasMany Annonce (via marque -> name)
```

#### Exemple API

```php
// app/Http/Controllers/AnnonceController.php
Route::get('/api/models', fn(Request $req) => {
    return CarModel::whereHas('brand', fn($q) => 
        $q->where('name', $req->brand)
    )->pluck('name');
    // Retourne: ["Clio", "Megane", ...]
});
```

**Taille DB:** ~2 KB total (58 marques)


---

### 4. 🚗 TABLE: `car_models`

**Fichier migration:** `database/migrations/2025_12_08_201005_create_car_models_table.php`

**Rôle:** Lookup table pour les modèles automobiles

#### Colonnes

| Colonne | Type | Null | Props |
|---------|------|------|-------|
| `id` | INT | No | PK |
| `car_brand_id` | INT | No | FK → car_brands.id |
| `name` | VARCHAR(100) | No | Indexed |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Relations

```
CarModel belongsTo CarBrand
CarModel hasMany Annonce (via modele -> name)
```

**Taille DB:** ~10 KB total

---

### 5. ⭐ TABLE: `favorites`

**Fichier migration:** `database/migrations/2025_12_11_114124_create_favorites_table.php`

**Rôle:** Join table entre users et annonces pour les favoris

#### Colonnes

| Colonne | Type | Constraint |
|---------|------|-----------|
| `id` | BIGINT | PK |
| `user_id` | BIGINT | FK → users.id (CASCADE) |
| `annonce_id` | BIGINT | FK → annonces.id (CASCADE) |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

**Unique Constraint:** `(user_id, annonce_id)` - Un user ne peut favoriser une annonce qu'une fois

#### Relations

```
Favorite belongsTo User
Favorite belongsTo Annonce
```

#### Requêtes courantes

```sql
-- Favoris d'une user
SELECT a.* FROM favorites f
INNER JOIN annonces a ON f.annonce_id = a.id
WHERE f.user_id = 5
ORDER BY f.created_at DESC;

-- Nombre de favoris pour une annonce
SELECT COUNT(*) FROM favorites WHERE annonce_id = 123;

-- Ajouter un favori
INSERT INTO favorites (user_id, annonce_id) VALUES (5, 123);

-- Retirer un favori
DELETE FROM favorites WHERE user_id = 5 AND annonce_id = 123;
```

**Taille DB:** ~500 B par favori


---

### 6. 💬 TABLE: `conversations`

**Fichier migration:** `database/migrations/2025_12_11_100442_create_conversations_table.php`

**Rôle:** Threads de conversations entre buyer et seller

#### Colonnes

| Colonne | Type | Null | Props |
|---------|------|------|-------|
| `id` | BIGINT | No | PK |
| `annonce_id` | BIGINT | No | FK → annonces.id |
| `buyer_id` | BIGINT | No | FK → users.id |
| `seller_id` | BIGINT | No | FK → users.id |
| `last_message_at` | TIMESTAMP | Yes | Timestamp du dernier message |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

**Constraints:** 
- Une seule conversation par annonce/buyer combinaison

#### Relations

```
Conversation belongsTo Annonce
Conversation belongsTo User (as buyer)
Conversation belongsTo User (as seller)
Conversation hasMany Message
```

#### Logique métier

```
User A (seller) crée annonce #123

User B (buyer) veut l'acheter
  ↓
Click "Contacter le vendeur"
  ↓
Check: Existe déjà conversation entre B et annonce 123?
  ├─ OUI → Redirect vers conversation existante
  └─ NON → Créer conversation
          ├─ buyer_id = B.id
          ├─ seller_id = A.id
          ├─ annonce_id = 123
          ├─ last_message_at = NOW()
          └─ Redirect vers nouvelles conversation
          
User B envoie message
  ↓
Créer Message record
  ↓
Update conversation.last_message_at = NOW()
```

#### Requêtes courantes

```sql
-- Conversations d'un utilisateur (as buyer or seller)
SELECT * FROM conversations 
WHERE buyer_id = 5 OR seller_id = 5
ORDER BY last_message_at DESC;

-- Conversations d'une annonce
SELECT * FROM conversations WHERE annonce_id = 123;

-- Conversation spécifique
SELECT * FROM conversations 
WHERE annonce_id = 123 AND buyer_id = 5;
```

**Taille DB:** ~1 KB par conversation


---

### 7. 📧 TABLE: `messages`

**Fichier migration:** `database/migrations/2025_12_11_100554_create_messages_table.php`

**Rôle:** Messages individuels dans une conversation

#### Colonnes

| Colonne | Type | Null | Props |
|---------|------|------|-------|
| `id` | BIGINT | No | PK |
| `conversation_id` | BIGINT | No | FK → conversations.id |
| `sender_id` | BIGINT | No | FK → users.id |
| `body` | LONGTEXT | No | Contenu du message |
| `read_at` | TIMESTAMP | Yes | Quand lu par destinataire? |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Relations

```
Message belongsTo Conversation
Message belongsTo User (as sender)
```

#### Logique métier

```
User A envoie message
  ↓
Créer Message record (read_at = NULL)
  ↓
Message visible pour User A (sender)
  
User B reçoit notification
  ├─ Ouvre conversation
  ├─ Messages marqués comme read
  │  UPDATE messages SET read_at = NOW() 
  │  WHERE conversation_id = X AND sender_id != B.id
  └─ Email de notification (optionnel)
```

#### Requêtes courantes

```sql
-- Tous les messages d'une conversation
SELECT m.* FROM messages m
WHERE m.conversation_id = 5
ORDER BY m.created_at ASC;

-- Messages non lus (pour un utilisateur)
SELECT m.* FROM messages m
INNER JOIN conversations c ON m.conversation_id = c.id
WHERE m.read_at IS NULL 
  AND c.seller_id = 5  -- User reçoit les messages comme seller
ORDER BY m.created_at DESC;

-- Nombre messages non lus
SELECT COUNT(*) FROM messages m
WHERE m.read_at IS NULL AND sender_id != 5;

-- Marquer comme lus
UPDATE messages SET read_at = NOW()
WHERE conversation_id = 5 
  AND sender_id != 5
  AND read_at IS NULL;
```

**Taille DB:** ~1-2 KB par message


---

### 8. 💳 TABLE: `plans`

**Fichier migration:** `database/migrations/2026_02_08_151000_create_plans_table.php`

**Rôle:** Définir les tiers d'abonnement (PRO system)

#### Colonnes

| Colonne | Type | Null | Props |
|---------|------|------|-------|
| `id` | BIGINT | No | PK |
| `name` | VARCHAR(255) | No | Unique ("Pro") |
| `price` | DECIMAL(10,2) | No | Prix en DZD |
| `duration_days` | INT | No | Durée (30) |
| `features` | JSON | No | Features JSON |
| `is_active` | BOOLEAN | No | Default: true |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Données PRO Plan

```json
{
  "name": "Pro",
  "price": 3000.00,
  "duration_days": 30,
  "features": {
    "max_active_ads": 10,
    "boosts_per_month": 5,
    "boost_duration_days": 7
  },
  "is_active": true
}
```

#### Relations

```
Plan hasMany Subscription
```

#### Requêtes courantes

```sql
-- Récupérer le plan PRO
SELECT * FROM plans WHERE name = 'Pro' AND is_active = 1;

-- Features en JSON
SELECT JSON_EXTRACT(features, '$.max_active_ads') as max_ads FROM plans WHERE id = 1;
```

**Taille DB:** ~500 B par plan

---

### 9. 📝 TABLE: `subscriptions`

**Fichier migration:** `database/migrations/2026_02_08_151001_create_subscriptions_table.php`

**Rôle:** Stocker les abonnements PRO actifs/refusés

#### Colonnes

| Colonne | Type | Null | Enum/Props |
|---------|------|------|-----------|
| `id` | BIGINT | No | PK |
| `user_id` | BIGINT | No | FK → users.id |
| `plan_id` | BIGINT | No | FK → plans.id |
| `started_at` | TIMESTAMP | Yes | Quand l'abo commence |
| `expires_at` | TIMESTAMP | Yes | Quand l'abo expire |
| `status` | ENUM | No | 'active' \| 'expired' \| 'cancelled' |
| `payment_proof_path` | VARCHAR(255) | Yes | Chemin fichier PDF/Image |
| `payment_status` | ENUM | No | 'pending' \| 'approved' \| 'rejected' |
| `rejection_reason` | TEXT | Yes | Pourquoi rejeté? |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Relations

```
Subscription belongsTo User
Subscription belongsTo Plan
```

#### Statuts Subscription

```
┌────────────────────────────────────────┐
│ Subscription Status Workflow            │
├────────────────────────────────────────┤
│                                         │
│ 1. USER INITIE SOUSCRIPTION            │
│    ├─ Form: Sélectionne plan          │
│    ├─ Upload preuve paiement           │
│    └─ POST /pro/subscribe              │
│       ↓                                 │
│       ├─ Create Subscription record     │
│       ├─ payment_status = "pending"     │
│       ├─ status = "active"              │
│       ├─ started_at = now()             │
│       ├─ expires_at = now() + 30j       │
│       └─ File saved to storage/         │
│                                         │
│ 2. ADMIN REVIEW                        │
│    ├─ Aller admin panel                │
│    ├─ Voir pending subscriptions       │
│    ├─ Review fichier preuve            │
│    │                                    │
│    └─→ Decision:                       │
│        ├─ APPROUVER:                   │
│        │  └─ PATCH /admin/subs/X/app  │
│        │     ├─ payment_status = "approved"
│        │     ├─ Email user: "Welcome!"  │
│        │     └─ Quotas updated         │
│        │                               │
│        └─ REJETER:                     │
│           └─ PATCH /admin/subs/X/rej  │
│              ├─ payment_status = "rejected"
│              ├─ rejection_reason = "..." │
│              ├─ status = "cancelled"     │
│              ├─ File deleted              │
│              └─ Email user: Reason       │
│                                         │
│ 3. EXPIRATION                          │
│    ├─ Chaque jour:                     │
│    │  └─ ExpireSubscriptions command   │
│    ├─ Check: expires_at < now()        │
│    ├─ Update status = "expired"        │
│    ├─ quotas reset pour next month     │
│    └─ User reste FREE pour crear annon. │
│                                         │
└────────────────────────────────────────┘
```

#### Méthodes Model utiles

```php
// app/Models/Subscription.php

public function isActive(): bool {
    return $this->status === 'active' 
        && $this->payment_status === 'approved'
        && $this->expires_at > now();
}

public function isApproved(): bool {
    return $this->payment_status === 'approved';
}

public function isPending(): bool {
    return $this->payment_status === 'pending';
}

public function scopeActive($query) {
    return $query->where('status', 'active')
        ->where('payment_status', 'approved')
        ->where('expires_at', '>', now());
}
```

#### Requêtes courantes

```sql
-- Subscriptions actives (approuvées et valides)
SELECT s.* FROM subscriptions s
WHERE s.status = 'active' 
  AND s.payment_status = 'approved'
  AND s.expires_at > NOW();

-- Subscriptions en attente d'approbation (admin)
SELECT s.* FROM subscriptions s
WHERE s.payment_status = 'pending'
ORDER BY s.created_at ASC;

-- Subscriptions expirées pour être nettoyées
SELECT s.* FROM subscriptions s
WHERE s.expires_at < NOW() 
  AND s.status = 'active';

-- Subscription d'un utilisateur (check isPro)
SELECT * FROM subscriptions 
WHERE user_id = 5 
  AND status = 'active'
  AND payment_status = 'approved'
  AND expires_at > NOW();
```

**Taille DB:** ~2 KB par subscription


---

### 10. 🚀 TABLE: `boosts`

**Fichier migration:** `database/migrations/2026_02_08_151002_create_boosts_table.php`

**Rôle:** Stocker les boosts d'annonces (PRO feature)

#### Colonnes

| Colonne | Type | Null | Enum/Props |
|---------|------|------|-----------|
| `id` | BIGINT | No | PK |
| `annonce_id` | BIGINT | No | FK → annonces.id (CASCADE) |
| `user_id` | BIGINT | No | FK → users.id (CASCADE) |
| `started_at` | TIMESTAMP | No | Quand a commencé |
| `expires_at` | TIMESTAMP | No | Quand l'expiration (7j après) |
| `status` | ENUM | No | 'active' \| 'expired' |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Relations

```
Boost belongsTo Annonce
Boost belongsTo User
Annonce hasMany Boost
```

#### Statuts Boost & Workflow

```
┌──────────────────────────────────────────┐
│ Boost Lifecycle                          │
├──────────────────────────────────────────┤
│                                           │
│ 1. USER PRO INITIATE BOOST               │
│    ├─ View leur annonce                 │
│    ├─ Clic "Booster cette annonce"      │
│    └─ POST /annonces/{id}/boost         │
│       ↓                                  │
│       ├─ Check: isPro()? YES             │
│       ├─ Check: boosts_count < 5?  YES  │
│       ├─ Create Boost record             │
│       │  ├─ status = 'active'            │
│       │  ├─ started_at = now()           │
│       │  ├─ expires_at = now() + 7d      │
│       │  └─ Decrement counter /session   │
│       └─ Annonce devient "featured"      │
│                                           │
│ 2. FEATURED (7 JOURS)                   │
│    ├─ Annonce apparaît en haut page     │
│    ├─ Badge "🚀 Boosté!"               │
│    ├─ Priorité dans recherche            │
│    ├─ Views augmentent                   │
│    └─ Durée: 7 jours                     │
│                                           │
│ 3. EXPIRATION (Automatic)                │
│    ├─ Tous les jours:                    │
│    │  └─ ExpireBoosts command            │
│    ├─ Find boosts where expires_at < now()
│    ├─ Update status = 'expired'          │
│    ├─ Annonce back to normal position   │
│    └─ Peut re-boost prochainement (5/mois)
│                                           │
│ 4. LIMIT PER MONTH                       │
│    ├─ 5 boosts max /mois                 │
│    ├─ Reset le 1er du mois               │
│    ├─ Track by COUNT boosts created_at   │
│    │    month(created_at)=month(now())   │
│    └─ Si 5/5, impossible de booster     │
│                                           │
└──────────────────────────────────────────┘
```

#### Méthodes Model utiles

```php
// app/Models/Boost.php

public function isActive(): bool {
    return $this->status === 'active' 
        && $this->expires_at > now();
}

public function scopeActive($query) {
    return $query->where('status', 'active')
        ->where('expires_at', '>', now());
}

// app/Services/BoostService.php

public function countBoostsThisMonth(User $user): int {
    return Boost::where('user_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
}

public function canBoost(User $user, Annonce $annonce): array {
    if (!$user->isPro()) {
        return ['canBoost' => false, 'reason' => 'Not PRO'];
    }
    
    if ($this->countBoostsThisMonth($user) >= 5) {
        return ['canBoost' => false, 'reason' => 'Quota atteint'];
    }
    
    return ['canBoost' => true];
}
```

#### Requêtes courantes

```sql
-- Boosts actifs pour une annonce
SELECT * FROM boosts 
WHERE annonce_id = 123 
  AND status = 'active'
  AND expires_at > NOW();

-- Annonces boostées (featured)
SELECT a.* FROM annonces a
INNER JOIN boosts b ON a.id = b.annonce_id
WHERE b.status = 'active' 
  AND b.expires_at > NOW()
  AND a.is_active = 1
ORDER BY b.started_at DESC;

-- Boosts d'un user ce mois
SELECT COUNT(*) FROM boosts 
WHERE user_id = 5
  AND MONTH(created_at) = MONTH(NOW())
  AND YEAR(created_at) = YEAR(NOW());

-- Boosts expirés à nettoyer
SELECT * FROM boosts 
WHERE status = 'active' 
  AND expires_at < NOW();
```

**Taille DB:** ~800 B par boost


---

### 11. 📋 TABLE: `annonce_deletions` (Audit Log)

**Fichier migration:** `database/migrations/2025_12_16_221118_create_annonce_deletions_table.php`

**Rôle:** Log des annonces supprimées (audit trail)

#### Colonnes

| Colonne | Type | Null | Props |
|---------|------|------|-------|
| `id` | BIGINT | No | PK |
| `annonce_id` | BIGINT | No | ID de l'annonce (pas FK) |
| `user_id` | BIGINT | No | ID du propriétaire |
| `titre` | VARCHAR(255) | No | Titre de l'annonce |
| `prix` | DECIMAL(12,2) | No | Prix |
| `was_sold` | BOOLEAN | No | Était-ce vendu? |
| `created_at` | TIMESTAMP | No | |
| `updated_at` | TIMESTAMP | No | |

#### Logique métier

```
User supprime annonce (X)
  ↓
AnnonceController::destroy()
  ├─ Check ownership
  ├─ Create AnnonceDeletion record
  │  ├─ Sauvegarder metadata (titre, prix, user_id)
  │  ├─ was_sold = user input (radio button)
  │  └─ created_at = NOW()
  ├─ Delete images from storage
  ├─ Delete annonce from DB
  └─ Redirect: "Annonce supprimée"
```

#### Requêtes courantes

```sql
-- Toutes les suppression du user
SELECT * FROM annonce_deletions 
WHERE user_id = 5;

-- Annonces supposériées "vendues"
SELECT * FROM annonce_deletions 
WHERE was_sold = 1;

-- Statistiques de suppression (par user)
SELECT user_id, COUNT(*) as total_deleted, SUM(was_sold) as sold
FROM annonce_deletions
GROUP BY user_id
ORDER BY total_deleted DESC;
```

**Taille DB:** ~1 KB par suppression

---

### 12-16. Tables Laravel Framework

Les tables suivantes sont créées automatiquement par Laravel:

| Table | Rôle |
|-------|------|
| `migrations` | Historique des migrations exécutées |
| `jobs` | Queue asynchrone |
| `job_batches` | Batch de jobs |
| `cache` | Cache entries |
| `sessions` | Sessions utilisateur |

**Non documentées ici (Laravel standard)**


---

## 🔗 Relations & Contraintes

### Foreign Keys (Cascades)

```plaintext
users
  ├── annonces (ON DELETE CASCADE)
  ├── subscriptions (ON DELETE CASCADE)
  ├── boosts (ON DELETE CASCADE)
  └── favorites (ON DELETE CASCADE)

annonces
  ├── favorites (ON DELETE CASCADE)
  ├── boosts (ON DELETE CASCADE)
  └── conversations (ON DELETE CASCADE)

plans
  └── subscriptions (ON DELETE CASCADE)

conversations
  └── messages (ON DELETE CASCADE)
```

### Unique Constraints

```sql
-- Un seul email par user
UNIQUE KEY unique_email ON users(email)

-- Une seule subscription active par user-plan combo (logique, pas contrainte)
-- Une seule conversation par annonce-buyer combo (logique)
UNIQUE KEY favorite_unique ON favorites(user_id, annonce_id)
```

### Indexes (Performance)

**Critiques pour la performance:**

```sql
-- Recherche rapide annonces
INDEX idx_annonces_marque ON annonces(marque)
INDEX idx_annonces_prix ON annonces(prix)
INDEX idx_annonces_is_active ON annonces(is_active)

-- Recherche users
INDEX idx_users_email ON users(email)
INDEX idx_users_is_banned ON users(is_banned)

-- Subscriptions actives
INDEX idx_subs_user_status ON subscriptions(user_id, payment_status)

-- Boosts expiration
INDEX idx_boosts_expires ON boosts(expires_at)

-- Messages par conversation
INDEX idx_messages_conv_id ON messages(conversation_id)
```


---

## 📊 Statistiques BD

| Métrique | Valeur |
|---|---|
| Nombre de tables métier | 11 |
| Total colonnes | ~120 |
| Relations FK | 15+ |
| Indexes principaux | 8+ |
| Estimated size (100K users + 500K annonces) | ~200-300 MB |
| Daily growth (estimation) | +1000 annonces, +500 users |

---

## 🛠️ Maintenance BD

### Sauvegarde (Backup)

```bash
# Dev (SQLite)
cp database/sqlite.db database/sqlite.db.backup

# Prod (MySQL)
mysqldump -u root -p autodz_prod > autodz_backup_$(date +%Y%m%d).sql
```

### Optimisation

```sql
-- Analyser tables (MySQL)
ANALYZE TABLE users, annonces, subscriptions;

-- Repair (si corruption)
REPAIR TABLE annonces;

-- Purger anciennes sessions & cache
DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP() - 86400;
DELETE FROM cache WHERE expiration < UNIX_TIMESTAMP();
```

### Migrations en Production

```bash
# TOUJOURS tester en local d'abord
php artisan migrate:fresh --seed  # Local only!

# En production, use --force:
ssh prod-server
cd /app/autodz
php artisan migrate --force          # Non-destructif
php artisan db:seed --force --class=PlanSeeder  # Si nouvelle table
```


---

## 🔐 Sécurité BD

- **Pas de données sensibles en plaintext** (passwords hashed avec bcrypt)
- **Foreign key constraints** pour l'intégrité référentielle
- **Timestamps** pour audit (created_at, updated_at)
- **Soft deletes** pouvant être ajoutés (futur)
- **HTTPS en production** pour en-transit encryption
- **DB backups** chiffrés en S3


---

## 📈 Performance & Optimisations

### N+1 Query Prevention

```php
// ❌ MAUVAIS: N+1 query
$annonces = Annonce::all();
foreach ($annonces as $annonce) {
    echo $annonce->user->name;  // SQL pour chaque annonce!
}

// ✅ BON: Eager loading
$annonces = Annonce::with('user')->get();
foreach ($annonces as $annonce) {
    echo $annonce->user->name;  // 0 SQL, données en mémoire
}
```

### Common Queries Optimization

```php
// Annonces actives avec boosts et favoris
$annonces = Annonce::active()
    ->with(['boosts' => fn($q) => $q->active()])
    ->with(['favorites' => fn($q) => $q->where('user_id', auth()->id())])
    ->get();
    
// Subscriptions actives avec plan features
$subs = Subscription::active()
    ->with('plan')
    ->get();
```


---

## 🎯 Conclusion

La BD est **normalisée, scalable et audit-ready**. Les relations permettent des requêtes complexes, les indexes garantissent la performance, et les logs permettent l'audit complet.

Consultez ce document lors de:
- ✅ Création de nouvelles features
- ✅ Modification schéma
- ✅ Problèmes de performance
- ✅ Debugging logique métier
- ✅ Audit et compliance
