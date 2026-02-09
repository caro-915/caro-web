# 🚀 Installation & Configuration Environnement

**Audience:** Développeurs & DevOps  
**Durée:** 30-45 minutes (première installation)


---

## ✅ Prérequis Système

### Pour Développement (Windows/Mac/Linux)

```
PHP:        8.3+          (Extensions: pdo_sqlite, pdo_mysql, mbstring, gd)
Node.js:    18.0+
Composer:   2.5+
Git:        2.35+
Laravel:    12.x
SQLite:     3.x (included in PHP)
```

**Extensions PHP requises** (`php -m`):
```
- curl
- json
- gd (Image processing)
- mbstring (String functions)
- openssl
- pdo
- pdo_sqlite
- tokenizer
- xml
```

### Pour Production

```
PHP:        8.3+
MySQL:      8.0+
Composer:   2.5+
Node.js:    18.0+ (for build only)
Redis:      6.0+ (optional, queue driver)
S3/R2:      Credentials for storage
```

**Vérifier les extensions:**
```bash
php --info | grep "Loaded Configuration"
```


---

## 📦 Installation Locale (Développement)

### 1️⃣ Cloner le Repository

```bash
cd c:/laragon/www       # ou /var/www si Linux
git clone https://github.com/caro-915/caro-web.git autodz
cd autodz
git checkout caro_bedro # Branche de développement (par défaut)
```

### 2️⃣ Installer les dépendances

#### Backend (Composer)
```bash
composer install
```

#### Frontend (NPM)
```bash
npm install
```

### 3️⃣ Générer la clé APP

```bash
php artisan key:generate
```

**Résultat attendu:** `.env` reçoit une nouvelle clé `APP_KEY=base64:xxxxx`

### 4️⃣ Créer le fichier .env

```bash
cp .env.example .env
```

**Personnaliser le fichier** `.env` (voir section suivante)

### 5️⃣ Créer le symlink stockage

```bash
php artisan storage:link
```

**Cela crée:** `public/storage` → `storage/app/public` (symlink)

*Pour Windows (si le symlink échoue):*
```powershell
New-Item -ItemType SymbolicLink -Path "public/storage" -Target "storage/app/public"
# Ou manuellement dans Laravel Cloud
```

### 6️⃣ Exécuter les migrations

```bash
php artisan migrate
```

**Résultat:** Tables créées dans `database/sqlite.db`

### 7️⃣ Seeder la base de données

```bash
php artisan db:seed
# Ou spécifiquement:
php artisan db:seed --class=DatabaseSeeder
php artisan db:seed --class=CarBrandSeeder
php artisan db:seed --class=CarModelSeeder
php artisan db:seed --class=PlanSeeder          # PRO plans
```

### 8️⃣ Compiler les assets

```bash
npm run dev    # Mode development (watch)
# Ou
npm run build  # Mode production
```

### 9️⃣ Démarrer le serveur

```bash
php artisan serve
```

**Accessible à:** `http://localhost:8000`

### ✅ Test d'installation

Ouvrir le navigateur:
```
http://localhost:8000
```

**Vous devez voir:**
- ✅ Page d'accueil avec annonces
- ✅ Bouton "Déposer une annonce"
- ✅ Formulaire de recherche fonctionnel
- ✅ Pas d'erreurs PHP


---

## 🔧 Configuration .env Détaillée

Le fichier `.env` contient toutes les variables d'environnement. Voici la configuration clé:

### Section Application

```env
APP_NAME=AutoDZ
APP_ENV=local                          # 'local' | 'production' | 'testing'
APP_DEBUG=true                         # true en dev, false en prod
APP_URL=http://localhost:8000
APP_KEY=base64:xxxxx                   # Généré avec php artisan key:generate
```

### Section Database

**Pour Développement (SQLite):**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/sqlite.db         # Chemin relatif
```

**Pour Production (MySQL):**
```env
DB_CONNECTION=mysql
DB_HOST=mysql-server.laravel.app
DB_PORT=3306
DB_DATABASE=autodz_prod
DB_USERNAME=root
DB_PASSWORD=***secure***
```

**Vérifier la connexion:**
```bash
php artisan tinker
DB::connection()->getPdo()      # Doit ret

urn la connexion
exit
```

### Section Mail

```env
MAIL_DRIVER=smtp               # 'log' (dev), 'smtp', 'sendgrid'
MAIL_HOST=smtp.mailtrap.io     # Ou smtp.SendGrid.net
MAIL_PORT=2525
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=yyyyy
MAIL_ENCRYPTION=tls            # 'tls' ou 'ssl'
MAIL_FROM_ADDRESS=noreply@autodz.dz
MAIL_FROM_NAME=AutoDZ
```

**Test d'envoi d'email:**
```bash
php artisan tinker
Mail::raw('Test', fn($msg) => $msg->to('test@test.com'));
exit
```

### Section Storage & Fichiers

```env
FILESYSTEM_DISK=public         # 'public' (local), 's3', 'r2'

# Pour S3 (production)
AWS_ACCESS_KEY_ID=xxxxx
AWS_SECRET_ACCESS_KEY=yyyyy
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=images-de-caro
AWS_URL=https://s3....amazonaws.com

# Pour Cloudflare R2 (notre config)
AWS_ACCESS_KEY_ID=***
AWS_SECRET_ACCESS_KEY=***
AWS_DEFAULT_REGION=auto
AWS_BUCKET=images-de-caro
AWS_URL=https://pub-xxx.r2.dev
AWS_ENDPOINT=https://xxx.r2.cloudflarestorage.com
```

### Section Authentication (OAuth)

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
# En production: https://caro.laravel.cloud/auth/google/callback

# ⚠️ Get credentials from Google Cloud Console
# See https://console.cloud.google.com/
```

### Section Queue (Jobs asynchrones)

```env
QUEUE_CONNECTION=sync           # 'sync' (dev), 'database', 'redis'

# Si vous utilisez Redis:
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Exécuter les jobs en dev:**
```bash
php artisan queue:listen --tries=1
```

### Section Sanctum (API Tokens)

```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SANCTUM_DOMAIN=localhost:8000
```

### Section Session

```env
SESSION_DRIVER=cookie
SESSION_LIFETIME=120            # minutes
SESSION_DOMAIN=localhost
SESSION_SECURE_COOKIES=false    # true en production (HTTPS)
SESSION_HTTP_ONLY=true
```

### Exemple complet .env (Development)

```env
# ==================
# APPLICATION
# ==================
APP_NAME=AutoDZ
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Africa/Algiers
APP_KEY=base64:xxxxx

# ==================
# DATABASE
# ==================
DB_CONNECTION=sqlite
DB_DATABASE=database/sqlite.db

# ==================
# MAIL
# ==================
MAIL_DRIVER=log                    # Ne pas envoyer les emails
MAIL_FROM_ADDRESS=noreply@autodz.dz
MAIL_FROM_NAME=AutoDZ

# ==================
# STORAGE
# ==================
FILESYSTEM_DISK=public             # Sauver images localement

# ==================
# AUTHENTICATION
# ==================
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# ==================
# QUEUE
# ==================
QUEUE_CONNECTION=sync              # Traiter les jobs immédiatement

# ==================
# SESSION
# ==================
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_SECURE_COOKIES=false
```


---

## 🌱 Seeders & Données Initiales

### Qu'est-ce qu'un Seeder?

Un seeder est un script qui **remplit la BD avec des données de test**. Utile pour:
- Initialiser les données de lookup (marques, modèles)
- Créer un utilisateur admin
- Créer des plans de prix

### Seeders inclus

#### 1. DatabaseSeeder (Point d'entrée)

**Fichier:** `database/seeders/DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call([
        CarBrandSeeder::class,
        CarModelSeeder::class,
        PlanSeeder::class,
    ]);
}
```

**Exécution:**
```bash
php artisan db:seed
```

#### 2. CarBrandSeeder

**Fichier:** `database/seeders/CarBrandSeeder.php`

Importe les marques automobiles depuis `export-brands.json`:

```json
[
    {"id": 1, "name": "Renault", "created_at": "..."},
    {"id": 2, "name": "Peugeot", "created_at": "..."},
    ...
]
```

**BD résultant:**
```sql
SELECT * FROM car_brands;
-- id | name | created_at | updated_at
-- 1  | Renault | ... | ...
-- 2  | Peugeot | ... | ...
```

#### 3. CarModelSeeder

**Fichier:** `database/seeders/CarModelSeeder.php`

Importe les modèles de voitures depuis `export-models.json`:

```json
[
    {"id": 1, "car_brand_id": 1, "name": "Clio", "created_at": "..."},
    {"id": 2, "car_brand_id": 1, "name": "Megane", "created_at": "..."},
    ...
]
```

#### 4. PlanSeeder (PRO System)

**Fichier:** `database/seeders/PlanSeeder.php`

Crée le plan PRO dans la table `plans`:

```php
Plan::firstOrCreate(
    ['name' => 'Pro'],
    [
        'price' => 3000.00,
        'duration_days' => 30,
        'features' => [
            'max_active_ads' => 10,      // Changé de 50 à 10
            'boosts_per_month' => 5,
            'boost_duration_days' => 7,
        ],
        'is_active' => true,
    ]
);
```

**Résultat en BD:**
```sql
SELECT * FROM plans;
-- id | name | price | duration_days | features | is_active
-- 1  | Pro  | 3000  | 30 | {"max_active_ads":10,"boosts_per_month":5,...} | 1
```

#### 5. ProductionDataSeeder (Production only)

**Fichier:** `database/seeders/ProductionDataSeeder.php`

Importe les données de production depuis des fichiers JSON:
- `export-brands.json`
- `export-models.json`
- `export-users.json` (utilisateurs existants)

**Utilisation:**
```bash
php artisan db:seed --class=ProductionDataSeeder --force
```

### Créer un Admin User

**Méthode 1: Via Tinker**

```bash
php artisan tinker

\$admin = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@autodz.dz',
    'password' => bcrypt('password123'),
    'is_admin' => 1,
    'is_banned' => 0,
    'email_verified_at' => now(),
]);

echo "Admin créé: {$admin->email}\n";
exit
```

**Méthode 2: Via Laravel Cloud (Production)**

```bash
php artisan tinker --execute="\$admin = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@autodz.dz', 'password' => bcrypt('password123'), 'is_admin' => 1]); echo 'Admin created';"
```

### Réinitialiser la BD

**⚠️ ATTENTION: Cela supprimera TOUTES les données**

```bash
# Dev: Réinitialiser complètement
php artisan migrate:refresh --seed

# Production: Fortement déconseillé! Utiliser plutôt:
php artisan migrate --force
php artisan db:seed --class=PlanSeeder --force
```


---

## 🎯 Configuration Scheduler (Scheduled Jobs)

Le scheduler exécute des tâches à intervalles réguliers (crons):

**Fichier:** `routes/console.php`

```php
use Illuminate\Support\Facades\Schedule;

// Expirer les subscriptions tous les jours à minuit
Schedule::command('command:expire-subscriptions')
    ->daily()
    ->timezone('Africa/Algiers');

// Expirer les boosts toutes les heures
Schedule::command('command:expire-boosts')
    ->hourly();
```

### Enregistrer le Cron System

**Sur le serveur, ajouter une seule ligne au crontab:**

```bash
crontab -e
```

Ajouter:
```cron
* * * * * cd /path/to/autodz && php artisan schedule:run >> /dev/null 2>&1
```

Cela exécute `php artisan schedule:run` **toutes les minutes**, qui lance les jobs configurés.

### Tâches Incluses

| Commande | Fréquence | Rôle |
|---|---|---|
| `command:expire-subscriptions` | Daily | Marquer les subscriptions expirées |
| `command:expire-boosts` | Hourly | Marquer les boosts expirés |

**Vérifier le scheduler:**
```bash
# Test local (exécute les commandes manuellement)
php artisan schedule:run

# En production, le cron s'occupe d'appeler schedule:run automatiquement
```


---

## 🚀 Déploiement (Laravel Cloud)

### 1. Préparer le code

```bash
# Assurez-vous d'être sur caro_bedro avec tous les commits pushés
git status                 # Doit être clean
git log --oneline -5       # Vérifier les commits
```

### 2. Déployer vers main (production)

```bash
git checkout main
git merge caro_bedro
git push origin main       # Déclenche le déploiement automatique
```

### 3. Dans Laravel Cloud Dashboard

- Aller à **Settings** → **Environment** (Custom variables)
- Configurer les variables (voir `.env` plus haut)
- Aller à **Commands** et exécuter:

```bash
php artisan migrate --force
php artisan db:seed --class=PlanSeeder --force
```

### 4. Vérifier le déploiement

```bash
# Dans Logs, chercher pour "Deployment successful"
# Accéder à https://caro.laravel.cloud/
```

**Si erreurs:**
- Voir onglet **Logs** pour les détails
- Vérifier les variables `.env` dans Settings
- Relancer: **Deployments** → **Deploy** (bouton rouge)


---

## 🐛 Dépannage Installation

### Problème: "Class 'Intervention\Image\ImageManager' not found"

**Cause:** Laravel 12 utilise une nouvelle API d'Intervention Image

**Solution:**
```bash
composer update intervention/image
```

Puis remplacer les imports dans le code:
```php
// Ancien:
use Intervention\Image\Facades\Image;

// Nouveau:
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\GdDriver;

$manager = new ImageManager(new GdDriver());
$image = $manager->read($filePath);
```

### Problème: Permission refusée sur storage/

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Problème: SQLite database locked

```bash
# Fermer tous les processus PHP
pkill -f "php artisan"

# Supprimer le fichier lock (si exists)
rm database/sqlite.db-journal

# Recommencer
php artisan serve
```

### Problème: Webpack/Vite build fails

```bash
# Nettoyer
npm ci                     # Reinstall dependencies
rm -rf node_modules

# Rebuild
npm install
npm run build
```

### Problème: Port 8000 déjà utilisé

```bash
# Utiliser un autre port
php artisan serve --port=8001

# Ou fermer le processus occupant le port
lsof -i :8000              # Trouver le PID
kill -9 <PID>              # Tuer le processus
```


---

## ✅ Checklist Installation Complète

- [ ] PHP 8.3+ avec extensions installées
- [ ] Repository cloné sur machine locale
- [ ] `composer install` réussi
- [ ] `npm install` réussi
- [ ] `.env` créé et configuré
- [ ] `php artisan key:generate` exécuté
- [ ] `php artisan storage:link` exécuté
- [ ] `php artisan migrate` réussi (BD créée)
- [ ] `php artisan db:seed` réussi (données remplies)
- [ ] `npm run dev` ou `npm run build` réussi
- [ ] `php artisan serve` démarre sans erreur
- [ ] Accès à `http://localhost:8000` fonctionne
- [ ] Admin user créé (voir Tinker)
- [ ] Login avec admin@autodz.dz possible
- [ ] Dashboard admin accessible
- [ ] Aucune erreur de permission sur storage/

Vous êtes prêt à développer! 🎉
