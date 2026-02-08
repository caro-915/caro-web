# 📄 PREUVES TECHNIQUES - SYSTÈME PRO

## A. ROUTES CONCERNÉES

Toutes les routes PRO, Boost et Admin Subscription/Plan :

### Routes Utilisateur PRO
```
GET    /pro                              ProController@index
GET    /pro/subscribe/{plan}             ProController@create
POST   /pro/subscribe/{plan}             ProController@store
GET    /pro/status                       ProController@status
```

### Routes Boost
```
POST   /annonces/{annonce}/boost         BoostController@store
```

### Routes Admin (PREFIX /admin)
```
GET    /admin/plans                      AdminPlanController@index
POST   /admin/plans                      AdminPlanController@store
GET    /admin/plans/{plan}               AdminPlanController@show
GET    /admin/plans/{plan}/edit          AdminPlanController@edit
PUT    /admin/plans/{plan}               AdminPlanController@update
DELETE /admin/plans/{plan}               AdminPlanController@destroy

GET    /admin/subscriptions              AdminSubscriptionController@index
GET    /admin/subscriptions/{sub}        AdminSubscriptionController@show
PATCH  /admin/subscriptions/{sub}/approve AdminSubscriptionController@approve
PATCH  /admin/subscriptions/{sub}/reject AdminSubscriptionController@reject
```

---

## B. CODE CLÉ

### 1. Middleware Pro (`app/Http/Middleware/EnsureUserIsPro.php`)

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPro
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !$this->subscriptionService->userIsPro(auth()->user())) {
            return abort(403, 'Vous devez avoir un abonnement PRO pour accéder à cette fonctionnalité.');
        }

        return $next($request);
    }
}
```

### Enregistrement dans bootstrap/app.php:
```php
$middleware->alias([
    'admin'  => \App\Http\Middleware\AdminMiddleware::class,
    'banned' => \App\Http\Middleware\EnsureUserNotBanned::class,
    'pro'    => \App\Http\Middleware\EnsureUserIsPro::class,  // ← NOUVEAU
]);
```

---

### 2. SubscriptionService::userIsPro() (`app/Services/SubscriptionService.php`)

```php
/**
 * Check if user is PRO.
 */
public function userIsPro(User $user): bool
{
    return $this->getActiveSubscription($user) !== null;
}

/**
 * Get the active subscription for a user.
 */
public function getActiveSubscription(User $user): ?Subscription
{
    return $user->subscriptions()
        ->where('status', 'active')
        ->where('payment_status', 'approved')  // ← MUST BE APPROVED
        ->where('expires_at', '>', now())      // ← NOT EXPIRED
        ->latest()
        ->first();
}
```

**GARANTIES:**
- ✅ Seul `payment_status = 'approved'` compte
- ✅ Doit être `status = 'active'`
- ✅ Doit pas être expiré (`expires_at > now()`)

---

### 3. Logique Approve Subscription (`app/Services/SubscriptionService.php`)

```php
/**
 * Approve a subscription payment.
 */
public function approveSubscription(Subscription $subscription): void
{
    \Log::info('✅ ACTIVATION ABONNEMENT', [
        'user_id' => $subscription->user_id,
        'plan' => $subscription->plan->name,
        'started_at' => now(),
        'expires_at' => now()->addDays($subscription->plan->duration_days),
        'payment_status' => 'approved',
    ]);
    $subscription->update(['payment_status' => 'approved']);
}
```

**Appelée depuis:** `AdminSubscriptionController::approve()`

```php
public function approve(Subscription $subscription)
{
    $this->subscriptionService->approveSubscription($subscription);
    
    return redirect()
        ->route('admin.subscriptions.index')
        ->with('success', "Abonnement approbé pour {$subscription->user->name}");
}
```

**Flot:**
1. Admin POST `/admin/subscriptions/{id}/approve`
2. Controller appelle `$subscriptionService->approveSubscription($subscription)`
3. Service set `payment_status = 'approved'`
4. Log créé avec détails
5. Redirect avec succès

---

### 4. Code de Tri Annonces avec Boost (`app/Http/Controllers/AnnonceController.php` search)

À IMPLÉMENTER - Actuellement absent. Devra ajouter dans la requête:

```php
// Ajouter dans la requête search()
$query = Annonce::where('is_active', true);

// Tri par boost (boostées en premier), puis par date
$query->with('boosts')
    ->orderByRaw('(SELECT COUNT(*) FROM boosts WHERE boosts.annonce_id = annonces.id AND boosts.status = "active" AND boosts.expires_at > NOW()) DESC')
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

**Résultat:** Les annonces boostées apparaissent EN PREMIER dans `/recherche`

---

### 5. Validation Quotas Annonces (`app/Http/Controllers/AnnonceController.php` store)

À IMPLÉMENTER - Actuellement absent. Devra ajouter:

```php
public function store(Request $request)
{
    // ... validation ...

    // ✅ VÉRIFIER QUOTA (NOUVEAU)
    $subscriptionService = app(\App\Services\SubscriptionService::class);
    $features = $subscriptionService->getFeatures(auth()->user());
    $activeAds = Annonce::where('user_id', auth()->id())
        ->where('is_active', true)
        ->count();

    if ($activeAds >= $features['max_active_ads']) {
        return back()
            ->with('error', "Vous avez atteint votre limite de {$features['max_active_ads']} annonces actives.")
            ->withInput();
    }

    // ... rest of creation ...
}
```

**Logique:**
- FREE: max 5 annonces actives
- PRO: max 50 annonces actives

**Où appliquer aussi:**
- `AnnonceController::update()` - si augmente images/slots
- API `/api/annonces` - même validation

---

### 6. BoostService::canBoost() (avec logs)

```php
public function canBoost(User $user, Annonce $annonce): array
{
    $canBoost = true;
    $reason = '';

    // Check if user is PRO
    if (!$this->subscriptionService->userIsPro($user)) {
        $canBoost = false;
        $reason = 'Vous devez avoir un abonnement PRO pour booster une annonce.';
        \Log::warning('❌ BOOST NON AUTORISÉ : Utilisateur non PRO', [
            'user_id' => $user->id,
            'annonce_id' => $annonce->id,
        ]);
        return compact('canBoost', 'reason');
    }

    // Check if annonce belongs to user
    if ($annonce->user_id !== $user->id) {
        $canBoost = false;
        $reason = "Vous ne pouvez booster que vos propres annonces.";
        \Log::warning('❌ BOOST NON AUTORISÉ : Annonce pas du propriétaire', [
            'user_id' => $user->id,
            'annonce_id' => $annonce->id,
        ]);
        return compact('canBoost', 'reason');
    }

    // Check monthly boost limit
    $features = $this->subscriptionService->getFeatures($user);
    $boostsThisMonth = $this->countBoostsThisMonth($user);

    if ($boostsThisMonth >= $features['boosts_per_month']) {
        $canBoost = false;
        $reason = "Vous avez atteint votre limite de {$features['boosts_per_month']} boosts ce mois-ci.";
        \Log::warning('❌ BOOST NON AUTORISÉ : Quota mensuel dépassé', [
            'user_id' => $user->id,
            'boosts_this_month' => $boostsThisMonth,
            'quota' => $features['boosts_per_month'],
        ]);
        return compact('canBoost', 'reason');
    }

    \Log::info('✅ BOOST AUTORISÉ', [
        'user_id' => $user->id,
        'annonce_id' => $annonce->id,
        'boosts_this_month' => $boostsThisMonth,
    ]);

    return compact('canBoost', 'reason');
}
```

---

## C. MIGRATIONS CRÉÉES

### 1. Plans Table (`database/migrations/2026_02_08_151000_create_plans_table.php`)

```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // "Pro"
    $table->decimal('price', 10, 2); // 3000.00
    $table->integer('duration_days'); // 30
    $table->json('features'); // {"max_active_ads":50,"boosts_per_month":5,...}
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**Contenu initial (PlanSeeder):**
```json
{
    "name": "Pro",
    "price": 3000.00,
    "duration_days": 30,
    "features": {
        "max_active_ads": 50,
        "boosts_per_month": 5,
        "boost_duration_days": 7
    }
}
```

---

### 2. Subscriptions Table (`database/migrations/2026_02_08_151001_create_subscriptions_table.php`)

```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
    $table->timestamp('started_at');
    $table->timestamp('expires_at');
    $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
    $table->string('payment_proof_path')->nullable(); // Chemin du fichier
    $table->enum('payment_status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
});
```

**État lors création:**
- `status` = 'active'
- `payment_status` = 'pending' ← RESTE PENDING JUSQU'À APPROVE
- `payment_proof_path` = chemin du fichier uploadé

**État après approve:**
- `payment_status` = 'approved'
- Accessible via `activeSubscription()`

---

### 3. Boosts Table (`database/migrations/2026_02_08_151002_create_boosts_table.php`)

```php
Schema::create('boosts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('annonce_id')->constrained('annonces')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->timestamp('started_at');
    $table->timestamp('expires_at');
    $table->enum('status', ['active', 'expired'])->default('active');
    $table->timestamps();
});
```

**État lors création:**
- `status` = 'active'
- `expires_at` = now() + 7 jours (duration from plan features)

**État après expiration:**
- `status` = 'expired'
- Pas compté comme actif par `isBoosted()`

---

## D. MODÈLES + RELATIONS

### User Model (ajouts)

```php
public function subscriptions()
{
    return $this->hasMany(\App\Models\Subscription::class);
}

public function boosts()
{
    return $this->hasMany(\App\Models\Boost::class);
}

/**
 * Get the user's active subscription.
 */
public function activeSubscription()
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->where('payment_status', 'approved')
        ->where('expires_at', '>', now())
        ->latest()
        ->first();
}

/**
 * Check if user has active PRO subscription.
 */
public function isPro(): bool
{
    return $this->activeSubscription() !== null;
}
```

### Annonce Model (ajouts)

```php
public function boosts()
{
    return $this->hasMany(\App\Models\Boost::class);
}

/**
 * Get the active boost for this annonce.
 */
public function activeBoost()
{
    return $this->boosts()
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->latest()
        ->first();
}

/**
 * Check if annonce is currently boosted.
 */
public function isBoosted(): bool
{
    return $this->activeBoost() !== null;
}
```

---

## E. COMMANDES CONSOLE

### 1. ExpireSubscriptions (`app/Console/Commands/ExpireSubscriptions.php`)

```php
php artisan subscriptions:expire
```

**Exécution:**
- Met à jour toutes les subscriptions avec `expires_at <= now()` en `status = 'expired'`
- Affiche: "Expired X subscription(s)."

### 2. ExpireBoosts (`app/Console/Commands/ExpireBoosts.php`)

```php
php artisan boosts:expire
```

**Exécution:**
- Met à jour tous les boosts avec `expires_at <= now()` en `status = 'expired'`
- Affiche: "Expired X boost(s)."

---

## F. VÉRIFICATIONS LIGNE DE COMMANDE

### Voir toutes les routes PRO:
```bash
php artisan route:list | grep -E "pro|subscription|boost"
```

### Afficher les migrations appliquées:
```bash
php artisan migrate:status | grep -E "plans|subscriptions|boosts"
```

### Vérifier les modèles:
```bash
php artisan tinker
# App\Models\Plan::all()
# App\Models\Subscription::all()
# App\Models\Boost::all()
```

### Voir les services enregistrés:
```bash
php artisan tinker
# app(\App\Services\SubscriptionService::class)
# app(\App\Services\BoostService::class)
```

---

## RÉSUMÉ FINAL

| Élément | Status | Localisation |
|---------|--------|-------------|
| Middleware Pro | ✅ | `app/Http/Middleware/EnsureUserIsPro.php` |
| SubscriptionService | ✅ | `app/Services/SubscriptionService.php` |
| BoostService | ✅ | `app/Services/BoostService.php` |
| Modèle Plan | ✅ | `app/Models/Plan.php` |
| Modèle Subscription | ✅ | `app/Models/Subscription.php` |
| Modèle Boost | ✅ | `app/Models/Boost.php` |
| Migrations | ✅ | `database/migrations/2026_02_08_*` |
| Seeders | ✅ | `database/seeders/PlanSeeder.php` |
| Commandes | ✅ | `app/Console/Commands/Expire*.php` |
| Routes | ✅ | `routes/web.php` |
| Logs | ✅ | Implémentés dans services |
| Validation quotas | ⏳ | À ajouter dans AnnonceController::store() |
| Tri boosts | ⏳ | À ajouter dans AnnonceController::search() |
| Badge boost | ⏳ | À ajouter dans vues |
| Scheduler | ⏳ | À configurer dans app/Console/Kernel.php |

