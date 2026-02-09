# 🎯 Fonctionnalités Détaillées par Module

**Audience:** Développeurs  
**Détail:** Flux complets, code, exemples


---

## 1️⃣ MODULE: ANNONCES (Listings)

### 1.1 Créer une Annonce

**Fichier controller:** `app/Http/Controllers/AnnonceController.php::store()`

**Flux complet:**

```plaintext
GET /annonces/create
  ↓ (Show form Blade)
  └─→ Form avec 15 champs + images

POST /annonces (form-data multipart)
  ├─ Validation (create 15 champs)
  ├─ Check quota:
  │  ├─ FREE: max 5 annonces actives
  │  ├─ PRO: max 10 annonces actives
  │  └─ ADMIN: illimité
  │
  ├─ Save to DB (is_active=false)
  │  ├─ Annonce record créée
  │  ├─ User associé
  │  └─ Timestamps auto
  │
  ├─ Image upload + processing
  │  ├─ Valider fichiers (image, max 4MB)
  │  ├─ Store files: storage/app/public/annonces/{uuid}.jpg
  │  ├─ Queue async job: ProcessAnnonceImages
  │  │  ├─ Resize to 1280px (aspect ratio)
  │  │  ├─ Apply watermark (45% opacity, ~18% width)
  │  │  ├─ Encode JPG 70% quality
  │  │  └─ Re-save to storage
  │  └─ Attendre: Quelques secondes (background job)
  │
  └─ Redirect /annonces/{id}
      └─ Success msg: "Annonce créée! En attente d'approbation"
```

**Code clé:**

```php
// app/Http/Controllers/AnnonceController.php

public function store(Request $request)
{
    // 1. Validate input
    $validated = $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'nullable|string',
        'prix' => 'required|integer|min:0',
        'marque' => 'required|string|max:100',
        'modele' => 'nullable|string|max:100',
        'annee' => 'nullable|integer|min:1980|max:2026',
        'kilometrage' => 'nullable|integer|min:0',
        'carburant' => 'required|in:Essence,Diesel,Hybride,Électrique',
        'boite_vitesse' => 'required|in:Manuelle,Automatique',
        'ville' => 'nullable|string|max:100',
        'vehicle_type' => 'required|in:Voiture,Utilitaire,Moto',
        'condition' => 'required|in:oui,non',
        'couleur' => 'nullable|string|max:50',
        'document_type' => 'nullable|in:carte_grise,procuration',
        'finition' => 'nullable|string|max:80',
        'images' => 'nullable|array|max:5',
        'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        'show_phone' => 'nullable|boolean',
    ], [
        // French error messages
        'titre.required' => 'Le titre est obligatoire.',
        'prix.required' => 'Le prix est obligatoire.',
        // ...
    ]);

    // 2. Check quota for this user
    $user = auth()->user();
    $features = app(SubscriptionService::class)->getFeatures($user);
    $maxAds = $features['max_active_ads'];  // 5 or 10
    $activeCount = $user->annonces()->where('is_active', 1)->count();
    
    if ($activeCount >= $maxAds) {
        return back()->withErrors([
            'quota' => "Limite atteinte ({$maxAds} annonces). " .
                       ($maxAds === 5 ? "Passer au PRO pour plus!" : "")
        ])->withInput();
    }

    // 3. Create annonce (is_active=false by default)
    $annonce = $user->annonces()->create([
        ...$validated,
        'is_active' => false,  // Awaiting admin approval
    ]);

    // 4. Handle image uploads
    $uploadedFiles = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('annonces', 'public');
            $uploadedFiles[] = storage_path("app/public/{$path}");
        }
    }

    // 5. Queue async job to process images
    if (!empty($uploadedFiles)) {
        ProcessAnnonceImages::dispatch($uploadedFiles)->afterResponse();
    }

    return redirect()
        ->route('annonces.show', $annonce)
        ->with('success', 'Annonce créée! En attente d\'approbation par admin.');
}
```

**Image Processing Job:**

```php
// app/Jobs/ProcessAnnonceImages.php

class ProcessAnnonceImages implements ShouldQueue
{
    public function handle()
    {
        foreach ($this->filePaths as $filePath) {
            // Load image
            $image = Image::make($filePath)->orientate();
            
            // Resize to max 1280px heigth/width
            $image->resize(1280, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Add watermark
            $watermarkPath = public_path('watermark.png');
            if (file_exists($watermarkPath)) {
                $watermark = Image::make($watermarkPath)->opacity(45);
                $watermark->resize(
                    (int) ($image->width() * 0.18), // 18% of image width
                    null,
                    fn($c) => $c->aspectRatio()
                );
                $image->insert($watermark, 'center');
            }
            
            // Encode & save as JPG 70% quality
            $image->encode('jpg', 70);
            $image->save($filePath);
        }
    }
}
```

### 1.2 Éditer une Annonce

**Fichier:** `AnnonceController.php::edit()` & `::update()`

**Flux:**

```plaintext
GET /annonces/{id}/edit
  ├─ Check: Owner or Admin?
  ├─ Show 5 image slots (can delete old)
  └─ Form pré-rempli

PUT /annonces/{id}
  ├─ Validate (same as create)
  ├─ Delete images (if checked in delete_images[])
  ├─ Add new images (up to 5 total)
  ├─ Update record (can re-set is_active=false)
  └─ Redirect with message
```

**Code:**

```php
public function update(Request $request, Annonce $annonce)
{
    // Check ownership
    $this->authorize('update', $annonce);
    
    $validated = $request->validate([...]);  // Same as create
    
    // Handle image deletes
    if ($request->has('delete_images')) {
        foreach ($request->delete_images as $field => $shouldDelete) {
            if ($shouldDelete && $annonce->{$field}) {
                Storage::disk('public')->delete($annonce->{$field});
                $annonce->{$field} = null;
            }
        }
    }
    
    // Handle new images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Store and queue processing
            $path = $image->store('annonces', 'public');
            ProcessAnnonceImages::dispatch([
                storage_path("app/public/{$path}")
            ])->afterResponse();
        }
    }
    
    $annonce->update($validated);
    
    return redirect()
        ->route('annonces.show', $annonce)
        ->with('success', 'Annonce mise à jour!');
}
```

### 1.3 Afficher une Annonce

**Fichier:** `AnnonceController.php::show()`

**Logique:**

```php
public function show(Annonce $annonce)
{
    // Only show active (or owner/admin sees pending)
    if (!$annonce->is_active) {
        $this->authorize('view', $annonce);  // Owner or admin only
    }

    // Incr counter (once per session, not owner/admin)
    $isOwner = auth()->id() === $annonce->user_id;
    $isAdmin = auth()->user()?->is_admin;
    
    if (!$isOwner && !$isAdmin) {
        $key = "viewed_annonce_{$annonce->id}";
        if (!session()->has($key)) {
            $annonce->increment('views');
            session()->put($key, true);
        }
    }

    // Load relations
    $annonce->load(['user', 'boosts' => fn($q) => $q->active()]);

    // Collect images (null-safe)
    $images = collect([
        $annonce->image_path,
        $annonce->image_path_2,
        $annonce->image_path_3,
        $annonce->image_path_4,
        $annonce->image_path_5,
    ])->filter()->map(fn($path) => asset('storage/' . $path));

    return view('annonces.show', [
        'annonce' => $annonce,
        'images' => $images,
        'isBoosted' => $annonce->isBoosted(),
    ]);
}
```

### 1.4 Recherche & Filtrage

**Fichier:** `AnnonceController.php::index()`

**Filtre Scope:**

```php
// app/Models/Annonce.php

public function scopeFilter($query, array $filters)
{
    if (!empty($filters['marque'])) {
        $query->where('marque', 'like', '%' . $filters['marque'] . '%');
    }
    if (!empty($filters['modele'])) {
        $query->where('modele', 'like', '%' . $filters['modele'] . '%');
    }
    if (!empty($filters['price_max'])) {
        $query->where('prix', '<=', $filters['price_max']);
    }
    if (!empty($filters['annee_min'])) {
        $query->where('annee', '>=', $filters['annee_min']);
    }
    if (!empty($filters['annee_max'])) {
        $query->where('annee', '<=', $filters['annee_max']);
    }
    if (!empty($filters['km_min'])) {
        $query->where('kilometrage', '>=', $filters['km_min']);
    }
    if (!empty($filters['km_max'])) {
        $query->where('kilometrage', '<=', $filters['km_max']);
    }
    if (!empty($filters['carburant'])) {
        $query->where('carburant', $filters['carburant']);
    }
    if (!empty($filters['wilaya'])) {
        $query->where('ville', 'like', '%' . $filters['wilaya'] . '%');
    }
    if (!empty($filters['vehicle_type'])) {
        $query->where('vehicle_type', $filters['vehicle_type']);
    }
    return $query;
}
```

**Controller:**

```php
public function index(Request $request)
{
    $query = Annonce::active()
        ->with('user')
        ->with(['boosts' => fn($q) => $q->active()]);
    
    // Apply filters
    if ($request->filled('search_fields')) {
        $query->filter($request->only([
            'marque', 'modele', 'price_max', 'annee_min', 'annee_max',
            'km_min', 'km_max', 'carburant', 'wilaya', 'vehicle_type'
        ]));
    }
    
    $annonces = $query->paginate(15);
    
    return view('annonces.index', compact('annonces'));
}
```

### 1.5 Supprimer une Annonce

**Fichier:** `AnnonceController.php::destroy()`

```php
public function destroy(Annonce $annonce)
{
    // Check ownership
    $this->authorize('delete', $annonce);
    
    // Create audit log
    AnnonceDeletion::create([
        'annonce_id' => $annonce->id,
        'user_id' => $annonce->user_id,
        'titre' => $annonce->titre,
        'prix' => $annonce->prix,
        'was_sold' => $request->input('was_sold', false),
    ]);
    
    // Delete images
    foreach (['image_path', 'image_path_2', ..., 'image_path_5'] as $field) {
        if ($annonce->{$field}) {
            Storage::disk('public')->delete($annonce->{$field});
        }
    }
    
    // Delete record
    $annonce->delete();
    
    return redirect('/mes-annonces')
        ->with('success', 'Annonce supprimée');
}
```

---

## 2️⃣ MODULE: SYSTÈME PRO (Monetization)

### 2.1 Plans & Abonnements

**Fichier:** `app/Http/Controllers/ProController.php`

**Afficher plans:**

```php
public function index()
{
    $plans = Plan::where('is_active', 1)->get();
    
    return view('pro.index', [
        'plans' => $plans,
        'userPlan' => auth()->user()?->activeSubscription(),
    ]);
}
```

**Souscrire:**

```php
public function subscribe(Request $request)
{
    $validated = $request->validate([
        'plan_id' => 'required|exists:plans,id',
        'payment_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);
    
    // Create subscription (payment_status = pending)
    $subscription = auth()->user()->subscriptions()->create([
        'plan_id' => $validated['plan_id'],
        'status' => 'active',
        'payment_status' => 'pending',  // Admin must approve
        'started_at' => now(),
        'expires_at' => now()->addDays(30),
    ]);
    
    // Save proof file
    $path = $request->file('payment_proof')
        ->store('payment_proofs', 'private');
    $subscription->update(['payment_proof_path' => $path]);
    
    // Email admin
    Mail::to('admin@autodz.dz')
        ->send(new NewSubscriptionNotification($subscription));
    
    return redirect('/pro')
        ->with('success', 'Demande envoyée! Admin révisera bientôt.');
}
```

### 2.2 Admin: Approuver/Rejeter Paiements

**Fichier:** `app/Http/Controllers/Admin/SubscriptionController.php`

```php
public function approve(Subscription $subscription)
{
    // Check admin
    $this->authorize('admin');
    
    $subscription->update([
        'payment_status' => 'approved',
    ]);
    
    // Email user
    Mail::to($subscription->user->email)
        ->send(new SubscriptionApprovedMail($subscription));
    
    return back()->with('success', 'Subscription approuvée!');
}

public function reject(Request $request, Subscription $subscription)
{
    $this->authorize('admin');
    
    $request->validate(['rejection_reason' => 'required|string']);
    
    $subscription->update([
        'payment_status' => 'rejected',
        'rejection_reason' => $request->input('rejection_reason'),
        'status' => 'cancelled',
    ]);
    
    // Delete proof file
    if ($subscription->payment_proof_path) {
        Storage::disk('private')->delete($subscription->payment_proof_path);
    }
    
    // Email user
    Mail::to($subscription->user->email)
        ->send(new SubscriptionRejectedMail($subscription));
    
    return back()->with('success', 'Subscription rejetée.');
}
```

### 2.3 Boosts (PRO Feature)

**Fichier:** `app/Http/Controllers/BoostController.php`

```php
public function store(Request $request, Annonce $annonce)
{
    $user = auth()->user();
    
    // Check PRO status
    if (!$user->isPro()) {
        return back()->with('error', 'Passer au PRO pour booster.');
    }
    
    // Check quota this month
    $boostService = app(BoostService::class);
    $canBoost = $boostService->canBoost($user, $annonce);
    
    if (!$canBoost['canBoost']) {
        return back()->with('error', $canBoost['reason']);
    }
    
    // Create boost
   $boost = Boost::create([
        'annonce_id' => $annonce->id,
        'user_id' => $user->id,
        'started_at' => now(),
        'expires_at' => now()->addDays(7),
        'status' => 'active',
    ]);
    
    return back()->with('success', 'Annonce boostée pour 7 jours!');
}
```

**BoostService:**

```php
// app/Services/BoostService.php

class BoostService
{
    public function canBoost(User $user, Annonce $annonce): array
    {
        if (!$user->isPro()) {
            return ['canBoost' => false, 'reason' => 'Not PRO'];
        }
        
        $plan = $user->activeSubscription()->plan;
        $maxBoosts = $plan->features['boosts_per_month'];
        $count = $this->countBoostsThisMonth($user);
        
        if ($count >= $maxBoosts) {
            return [
                'canBoost' => false,
                'reason' => "Quota atteint ({$count}/{$maxBoosts})"
            ];
        }
        
        // Check annonce not already boosted
        if ($annonce->isBoosted()) {
            return ['canBoost' => false, 'reason' => 'Déjà boostée'];
        }
        
        return ['canBoost' => true];
    }
    
    public function countBoostsThisMonth(User $user): int
    {
        return Boost::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
    
    public function expireOldBoosts()
    {
        Boost::where('expires_at', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);
    }
}
```

### 2.4 Scheduler: Expirer Subscriptions & Boosts

**Fichier:** `routes/console.php`

```php
use App\Console\Commands\ExpireSubscriptions;
use App\Console\Commands\ExpireBoosts;

Schedule::command('command:expire-subscriptions')
    ->daily()
    ->at('00:00')
    ->timezone('Africa/Algiers');

Schedule::command('command:expire-boosts')
    ->hourly();
```

**Commands:**

```php
// app/Console/Commands/ExpireSubscriptions.php

class ExpireSubscriptions extends Command
{
    public function handle()
    {
        $count = Subscription::where('expires_at', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);
        
        $this->info("Expired $count subscriptions");
    }
}

// app/Console/Commands/ExpireBoosts.php

class ExpireBoosts extends Command
{
    public function handle()
    {
        $count = Boost::where('expires_at', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);
        
        $this->info("Expired $count boosts");
    }
}
```

---

## 3️⃣ MODULE: MESSAGES & CONVERSATIONS

**Fichier principal:** `app/Http/Controllers/MessageController.php`

### 3.1 Démarrer une Conversation

```php
public function startConversation(Request $request, Annonce $annonce)
{
    $buyer = auth()->user();
    $seller = $annonce->user;
    
    // Check if conversation already exists
    $conversation = Conversation::where([
        'annonce_id' => $annonce->id,
        'buyer_id' => $buyer->id,
    ])->first();
    
    if (!$conversation) {
        $conversation = Conversation::create([
            'annonce_id' => $annonce->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'last_message_at' => now(),
        ]);
    }
    
    return redirect()
        ->route('messages.show', $conversation);
}
```

### 3.2 Envoyer un Message

```php
public function store(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'body' => 'required|string|max:5000',
    ]);
    
    // Check user is partie of conversation
    $this->authorize('view', $conversation);
    
    $message = $conversation->messages()->create([
        'sender_id' => auth()->id(),
        'body' => $validated['body'],
    ]);
    
    // Update last_message_at
    $conversation->update(['last_message_at' => now()]);
    
    // Send notification (email/pusher)
    event(new MessageSent($message));
    
    return back()->with('success', 'Message envoyé');
}
```

### 3.3 Voir Conversations

```php
public function index()
{
    $conversations = Conversation::where(fn($q) => 
        $q->where('buyer_id', auth()->id())
          ->orWhere('seller_id', auth()->id())
    )
    ->with(['annonce', 'messages'])
    ->orderBy('last_message_at', 'desc')
    ->paginate(20);
    
    return view('messages.index', compact('conversations'));
}
```

---

## 4️⃣ MODULE: FAVORIS

**Fichier:** `app/Http/Controllers/FavoriteController.php`

```php
public function toggle(Annonce $annonce)
{
    $user = auth()->user();
    
    $favorite = Favorite::where([
        'user_id' => $user->id,
        'annonce_id' => $annonce->id,
    ])->first();
    
    if ($favorite) {
        $favorite->delete();
        $message = 'Retiré des favoris';
    } else {
        Favorite::create([
            'user_id' => user->id,
            'annonce_id' => $annonce->id,
        ]);
        $message = 'Ajouté aux favoris';
    }
    
    return back()->with('success', $message);
}

public function index()
{
    $favorites = auth()->user()
        ->favoriteAnnonces()
        ->paginate(20);
    
    return view('favorites.index', compact('favorites'));
}
```

---

## 5️⃣ MODULE: ADMIN PANEL

**Fichier:** `app/Http/Controllers/Admin/*`

### 5.1 Approuver Annonces

```php
// app/Http/Controllers/Admin/AnnonceController.php

public function toggle(Annonce $annonce)
{
    $this->authorize('admin');
    
    $annonce->update([
        'is_active' => !$annonce->is_active,
    ]);
    
    return back()->with('success', 'Annonce ' .
        ($annonce->is_active ? 'approuvée' : 'désactivée'));
}
```

### 5.2 Gérer Utilisateurs & Bans

```php
public function toggleBan(User $user)
{
    $this->authorize('admin');
    
    $user->update([
        'is_banned' => !$user->is_banned,
    ]);
    
    if ($user->is_banned) {
        // Auto-logout if currently logged in
        session()->forget("users.{$user->id}");
        
        Mail::to($user->email)
            ->send(new UserBannedMail($user));
    }
    
    return back();
}
```

### 5.3 Voir Statistiques

```php
public function stats()
{
    $this->authorize('admin');
    
    return view('admin.stats.index', [
        'totalUsers' => User::count(),
        'totalAnnonces' => Annonce::count(),
        'activeAnnonces' => Annonce::active()->count(),
        'totalPRO' => Subscription::active()->count(),
        'totalMessages' => Message::count(),
        'topBrands' => Annonce::active()
            ->groupBy('marque')
            ->selectRaw('marque, COUNT(*) as count')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get(),
    ]);
}
```

---

## 6️⃣ MODULE: AUTHENTIFICATION & RÔLES

**Fichier:** `app/Http/Controllers/AuthController.php` (via Breeze + Sanctum)

### 6.1 Registration

```php
public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
    ]);
    
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'phone' => $validated['phone'],
        'is_admin' => false,
        'is_banned' => false,
    ]);
    
    // Send verification email
    $user->sendEmailVerificationNotification();
    
    return redirect('/login')->with('success', 'Vérifiez votre email');
}
```

### 6.2 Google OAuth

```php
// config/services.php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

// app/Http/Controllers/OAuthController.php
public function googleRedirect()
{
    return Socialite::driver('google')->redirect();
}

public function googleCallback()
{
    $user = Socialite::driver('google')->user();
    
    // Find or create user
    $authUser = User::firstOrCreate(
        ['email' => $user->email],
        [
            'name' => $user->name,
            'google_id' => $user->id,
            'avatar' => $user->avatar,
            'email_verified_at' => now(),
        ]
    );
    
    auth()->login($authUser);
    
    return redirect('/');
}
```

### 6.3 Middleware: Check Roles

```php
// app/Http/Middleware/AdminMiddleware.php

public function handle($request, Closure $next)
{
    if (!auth()->user() || !auth()->user()->is_admin) {
        abort(403, 'Unauthorized');
    }
    return $next($request);
}

// app/Http/Middleware/EnsureUserNotBanned.php

public function handle($request, Closure $next)
{
    if (auth()->user() && auth()->user()->is_banned) {
        auth()->logout();
        return redirect('/login')
            ->with('error', 'Votre compte a été banni');
    }
    return $next($request);
}

// app/Http/Middleware/EnsureUserIsPro.php

public function handle($request, Closure $next)
{
    if (!auth()->user() || !auth()->user()->isPro()) {
        abort(403, 'Passer au PRO pour accéder');
    }
    return $next($request);
}
```

---

Cette documentation couvre tous les **flux métier clés**. Chaque fonction a un fichier route correspondant et un test Feature associé.

Consultez ce document lors de:
- ✅ Modification d'une fonctionnalité
- ✅ Ajout de nouvelle feature
- ✅ Debug d'un flux utilisateur
- ✅ Optimisation de performance
