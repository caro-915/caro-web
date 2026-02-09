# 📋 AutoDZ - Présentation Générale du Projet

**Version:** 1.0.0  
**Date:** Février 2026  
**Statut:** ✅ Production


---

## 🎯 Objectif du Projet

**AutoDZ** est une **plateforme de petites annonces pour les véhicules en Algérie** permettant aux particuliers de:
- 🚗 **Vendre** et **acheter** des véhicules d'occasion
- 💬 **Communiquer** directement avec les vendeurs/acheteurs
- ⭐ **Marquer** des annonces en favoris
- 💳 **Monétiser** via un système d'abonnement PRO optionnel
- ⚡ **Booster** les annonces pour plus de visibilité

**Modèle commercial:** Plateforme gratuite (FREE) + abonnement optionnel mensualisé (PRO)


---

## 📊 Périmètre Fonctionnel

### Fonctionnalités Gratuites (Utilisateurs FREE)

| Fonctionnalité | Limite |
|---|---|
| Annonces actives | 5 par utilisateur |
| Titre + Description | Illimité |
| Images | 5 images max par annonce |
| Boosts | Aucun |
| Recherche | Avancée (marque, prix, wilaya) |
| Messages | Illimité |
| Favoris | Illimité |
| Admin | Non |

### Fonctionnalités PRO (Utilisateurs payants)

| Fonctionnalité | Limite |
|---|---|
| Annonces actives | **10 annonces** |
| Boosts/mois | **5 boosts** |
| Durée boost | **7 jours** |
| Quota de renouvellement | Mensuel |
| Support prioritaire | Oui |
| Prix abonnement | **3000 DZD/mois** |
| Mode paiement | Manuel (preuve d'achat) |

### Rôles Utilisateurs

```
┌─────────────────────────────────────┐
│         UTILISATEURS                │
├─────────────────────────────────────┤
│                                       │
├─ VISITEUR (non connecté)             │
│  ├─ Voir annonces publiques          │
│  ├─ Rechercher                       │
│  └─ Contacter -> Redirect login      │
│                                       │
├─ FREE USER (connecté, gratuit)       │
│  ├─ Créer 5 annonces max             │
│  ├─ Envoyer messages                 │
│  ├─ Ajouter favoris                  │
│  └─ Voir profil vendeur              │
│                                       │
├─ PRO USER (abonné actif)             │
│  ├─ Créer 10 annonces max            │
│  ├─ Booster 5x/mois                  │
│  ├─ Voir analytiques (futur)         │
│  └─ Support prioritaire (futur)      │
│                                       │
└─ ADMIN (is_admin=1)                  │
   ├─ Approuver annonces               │
   ├─ Valider paiements PRO            │
   ├─ Gérer utilisateurs (ban)         │
   ├─ Voir statistiques                │
   └─ Gérer plans & tarification       │
└─────────────────────────────────────┘
```


---

## 🛠️ Stack Technique Complète

### Backend
- **Framework:** Laravel 12 (PHP 8.3+)
- **ORM:** Eloquent
- **Auth:** Breeze + Sanctum (tokens API)
- **Queue:** Database (développement) / Redis (production)
- **File Storage:** S3 / Cloudflare R2 (production) / Local (dev)
- **Image Processing:** Intervention Image
- **Payment Proof:** File uploads (PDF, image)

### Frontend
- **Side Web:** Blade templates + Alpine.js
- **CSS:** Tailwind CSS 3.x
- **Side Mobile:** Flutter (API REST via Sanctum)
- **Form Validation:** Client-side (Alpine) + Server-side (Laravel)

### Base de Données
- **SGBD:** SQLite (dev) / MySQL 8.0+ (production)
- **Migrations:** Laravel migrations
- **Seeding:** Factoriesetal + JSON seeders

### DevOps
- **Deployment:** Laravel Cloud (TBD) / Docker possible
- **Git:** GitHub (caro-915/caro-web)
- **CI/CD:** GitHub Actions (futur)
- **Storage Image:** Cloudflare R2 / AWS S3
- **Email:** SMTP (Mailtrap dev / SendGrid prod)

### Dépendances Principal NPM
```json
{
  "dependencies": {
    "tailwindcss": "^3.x",
    "alpinejs": "^3.x",
    "@headlessui/vue": "^1.x",
    "sweetalert2": "^11.x"
  }
}
```

### Dépendances Principales Composer
```json
{
  "require": {
    "laravel/framework": "^12.0",
    "laravel/breeze": "^1.0",
    "laravel/sanctum": "^4.0",
    "intervention/image": "^3.0",
    "guzzlehttp/guzzle": "^7.0",
    "firebase/php-jwt": "^6.0"
  }
}
```


---

## 🏗️ Architecture Globale

### Flux Utilisateur

```
VISITEUR
   ↓ (déconnecté)
   ├─ Voir annonces home
   ├─ Rechercher
   │
   └─> Cliquer "Contacter"
       ↓
       Redirect /login
       ↓
       ┌─────────────────┐
       │  Créer Compte   │
       │   ou Login      │
       └─────────────────┘
       ↓
FREE USER (5 annonces max)
   ├─ Créer annonce
   ├─ (Admin approuve -> is_active=true)
   ├─ Envoyer messages
   ├─ Ajouter favoris
   │
   └─> Voir "Passer au PRO"
       ↓
       ┌─────────────────────────────────┐
       │ Souscrire à PRO (3000 DZD/mois) │
       │ Envoyer preuve de paiement      │
       └─────────────────────────────────┘
       ↓
PRO USER (10 annonces max)
   ├─ Créer 10 annonces
   ├─ Booster 5x/mois (7j renew)
   └─ Fonctionnalités premium
```

### Modules Métier

```
┌─────────────────────────────────────────┐
│   AUTODZ PLATFORM - Architecture        │
├─────────────────────────────────────────┤
│                                           │
│  ┌─────────────────────────────────┐    │
│  │  AUTH & USER MANAGEMENT          │    │
│  │  ├─ Registration (email/Google)  │    │
│  │  ├─ Login/Logout                 │    │
│  │  ├─ Roles (FREE, PRO, ADMIN)     │    │
│  │  └─ Ban system                   │    │
│  └─────────────────────────────────┘    │
│                                           │
│  ┌─────────────────────────────────┐    │
│  │  ANNONCES MANAGEMENT             │    │
│  │  ├─ Create/Edit/Delete           │    │
│  │  ├─ Images + Watermark           │    │
│  │  ├─ Quotas (5/10)                │    │
│  │  ├─ Status (pending/active)      │    │
│  │  ├─ Search + Filtering           │    │
│  │  └─ Views counter                │    │
│  └─────────────────────────────────┘    │
│                                           │
│  ┌─────────────────────────────────┐    │
│  │  MESSAGING SYSTEM                │    │
│  │  ├─ Conversations (buyer/seller) │    │
│  │  ├─ Direct messages              │    │
│  │  ├─ Real-time notifications      │    │
│  │  └─ Message history              │    │
│  └─────────────────────────────────┘    │
│                                           │
│  ┌─────────────────────────────────┐    │
│  │  PRO SYSTEM (Monetization)       │    │
│  │  ├─ Plans (PRO tier)             │    │
│  │  ├─ Subscriptions (active)       │    │
│  │  ├─ Payment proofs (manual)      │    │
│  │  ├─ Boosts (7 days)              │    │
│  │  ├─ Quotas renewal (monthly)     │    │
│  │  └─ Admin approval workflow      │    │
│  └─────────────────────────────────┘    │
│                                           │
│  ┌─────────────────────────────────┐    │
│  │  FAVORITES SYSTEM                │    │
│  │  ├─ Add/Remove favorites         │    │
│  │  ├─ Favorite list (user)         │    │
│  │  └─ Favorite count (annonce)     │    │
│  └─────────────────────────────────┘    │
│                                           │
│  ┌─────────────────────────────────┐    │
│  │  ADMIN PANEL                     │    │
│  │  ├─ Approve/Reject annonces      │    │
│  │  ├─ Manage users & ban           │    │
│  │  ├─ Approve subscriptions        │    │
│  │  ├─ View statistics              │    │
│  │  └─ Manage plans                 │    │
│  └─────────────────────────────────┘    │
│                                           │
└─────────────────────────────────────────┘
```


---

## 📁 Structure des Fichiers (Vue d'ensemble)

```
/autodz/
├── app/
│   ├── Console/Commands/          # Scheduled tasks
│   │   ├── ExpireSubscriptions.php
│   │   └── ExpireBoosts.php
│   ├── Http/
│   │   ├── Controllers/           # Logic métier
│   │   │   ├── AnnonceController.php
│   │   │   ├── ProController.php
│   │   │   ├── BoostController.php
│   │   │   ├── FavoriteController.php
│   │   │   ├── MessageController.php
│   │   │   └── Admin/             # Admin panel
│   │   │       ├── UserController.php
│   │   │       └── SubscriptionController.php
│   │   ├── Middleware/            # Auth & permissions
│   │   ├── Requests/              # Form validation
│   │   └── Resources/             # API responses
│   ├── Models/                    # Eloquent models
│   │   ├── User.php
│   │   ├── Annonce.php
│   │   ├── Favorite.php
│   │   ├── Conversation.php
│   │   ├── Message.php
│   │   ├── Plan.php               # PRO: Plan tier
│   │   ├── Subscription.php       # PRO: Active subscription
│   │   └── Boost.php              # PRO: Boost feature
│   ├── Services/                  # Business logic
│   │   ├── SubscriptionService.php
│   │   └── BoostService.php
│   ├── Jobs/                      # Async tasks
│   │   └── ProcessAnnonceImages.php
│   └── Policies/                  # Authorization policies
│       └── AnnoncePolicy.php
│
├── database/
│   ├── migrations/                # 36 migrations
│   ├── factories/
│   ├── seeders/                   # Database seeders
│   │   ├── DatabaseSeeder.php
│   │   ├── CarBrandSeeder.php
│   │   ├── CarModelSeeder.php
│   │   ├── PlanSeeder.php         # PRO: Insert plan
│   │   └── ProductionDataSeeder.php
│   └── manual-migrations.sql      # Custom SQL (if needed)
│
├── routes/
│   ├── web.php                    # Web routes (Blade views)
│   ├── api.php                    # API routes (JSON responses)
│   └── console.php                # Scheduler
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── home.blade.php
│   │   ├── annonces/
│   │   │   ├── index.blade.php
│   │   │   ├── show.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── pro/                   # PRO: Subscription UI
│   │   │   ├── index.blade.php    # Plans showcase
│   │   │   ├── subscribe.blade.php
│   │   │   └── status.blade.php   # Active subscription status
│   │   └── admin/                 # Admin panel views
│   ├── css/
│   │   └── app.css                # Tailwind
│   └── js/
│       ├── app.js
│       └── alpine/
│
├── storage/
│   └── app/public/
│       └── annonces/              # Uploaded images
│
├── public/
│   ├── watermark.png              # Watermark for images
│   ├── assets/
│   └── storage -> ../storage      # Symlink
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── sanctum.php                # API token config
│   └── queue.php                  # Job config
│
├── tests/
│   ├── Feature/                   # Feature tests
│   │   └── AnnonceTest.php
│   └── Unit/
│
├── .env.example                   # Environment template
├── docker-compose.yml
├── tailwind.config.js
├── vite.config.js
└── composer.json
```


---

## 🔄 Flux de Données Principal

### 1. Créer une Annonce (FREE user)

```plaintext
User fills form
   ↓ (client-side validation)
POST /annonces
   ↓
AnnonceController::store()
   ├─ Validate input
   ├─ Check quota (5 max for FREE)
   ├─ Save to DB (is_active=false)
   ├─ Upload images
   ├─ Queue async job: ProcessAnnonceImages (resize, watermark)
   └─ Redirect to show with "awaiting approval"
   
Admin sees pending annonce
   ↓
PATCH /admin/annonces/{id}/toggle
   ├─ Set is_active=true
   └─ Annonce visible on home & search
```

### 2. Passer au PRO (Payment workflow)

```plaintext
User clicks "Passer au PRO" (home card)
   ↓
GET /pro (show plans & features)
   ↓
Click "Souscrire"
   ↓
POST /pro/subscribe (form + payment proof file)
   ├─ Create Subscription (payment_status='pending')
   ├─ Store proof file in storage/
   └─ Email admin: "New subscription to approve"
   
Admin dashboard
   ↓
GET /admin/subscriptions
   ├─ Views pending subscriptions
   ├─ Reviews payment proof
   ├─ Click "Approuver" or "Rejeter"
   └─ PATCH /admin/subscriptions/{id}/approve
       ├─ Update payment_status='approved'
       ├─ Set expires_at = now() + 30 days
       ├─ Update quotas in cache/memory
       └─ Email user: "Welcome to PRO!"
   
User now PRO
   ├─ Can create 10 annonces
   ├─ Can boost 5x/month
   └─ Quotas reset monthly (scheduler task)
```

### 3. Booster une Annonce (PRO user)

```plaintext
PRO user views their active annonce
   ↓
Click "Booster cette annonce"
   ↓
BoostController::store()
   ├─ Check: isPro()?
   ├─ Check: Can boost this month? (count < 5)
   ├─ Create Boost record
   │  ├─ status='active'
   │  ├─ expires_at=now()+7days
   │  └─ Decrement boosts quota
   └─ Boost visible for 7 days (featured in search)
   
After 7 days (scheduled task)
   ↓
app/Console/Commands/ExpireBoosts.php
   ├─ Find all boosts with expires_at < now()
   ├─ Update status='expired'
   └─ No longer featured in search
```


---

## 📊 Statistiques Clés

| Métrique | Valeur |
|---|---|
| Annonces (tous statuts) | ∞ (growth) |
| Utilisateurs inscrits | ∞ (growth) |
| Images par annonce | 5 max |
| Taille image max | 4 MB chacune |
| Résolution image processée | 1280px max (aspect ratio) |
| Quota FREE | 5 annonces |
| Quota PRO | 10 annonces |
| Prix PRO | 3000 DZD/mois |
| Boosts PRO/mois | 5 max |
| Durée boost | 7 jours |
| Wilaya/Régions supportées | 58 wilayas Algérie |

---

## 👥 Équipe & Responsabilités

- **Développeur Principal:** Caro (caro-915)
- **Responsable DevOps:** TBD
- **Responsable Admin:** Admin panel user (admin@autodz.dz)
- **Support** (futur): Support email


---

## 📅 Roadmap (Futur)

### Phase 2 (Q2 2026)
- [ ] Notifications en temps réel (WebSocket)
- [ ] Analytiques seller (vues, inquiries, conversions)
- [ ] Système de reviews/Rating vendeur
- [ ] Paiement online (Stripe/Telr)

### Phase 3 (Q3 2026)
- [ ] Mobile app refresh
- [ ] Video support in annonces
- [ ] Search history saved
- [ ] Recommendations ML

### Phase 4 (Q4 2026)
- [ ] Marketplace for accessories
- [ ] Inspection service
- [ ] Insurance integration


---

## 📞 Support & Ressources

- **Documentation:** `/docs_autodz/` (ce dossier)
- **Code:** GitHub `caro-915/caro-web` (private)
- **Issues:** GitHub Issues
- **Wiki:** TBD
- **Contact Admin:** admin@autodz.dz
