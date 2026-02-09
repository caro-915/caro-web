# 👨‍💻 Guide du Nouveau Développeur

**READ THIS FIRST!** This is your starting point.  
**Audience:** New developers  
**Time to read:** 30 minutes  
**Time to setup:** 15 minutes

---

## 📚 Documentation Map

After this file, read in order:

1. **00_PRESENTATION_GENERALE.md** (5 min) - What is AutoDZ?
2. **01_INSTALLATION_ENVIRONNEMENT.md** (15 min) - Setup your machine
3. **03_BASES_DE_DONNEES.md** (15 min) - Database schema & relations
4. **04_FONCTIONNALITES_DETAILLEES.md** (20 min) - Features deep-dive
5. **07_GIT_WORKFLOW.md** (10 min) - How we work together
6. **06_MONITORING_EXPLOITATION.md** (prod-only) - Production support
7. **08_TESTS_RECETTE.md** (10 min) - How to test changes
8. **05_SECURITE.md** (15 min) - Security best practices
9. **10_ROADMAP.md** (5 min) - What's planned

**Fast track:** Just read sections 1, 2, then start `QUICK_START.md` in root folder.

---

## ⚡ 5-Minute Quick Start

```bash
# 1. Clone & install
cd c:\laragon\www
git clone <repo> autodz
cd autodz
composer install
npm install

# 2. Create .env
cp .env.example .env
php artisan key:generate

# 3. Database + seed
php artisan migrate
php artisan db:seed

# 4. Run app
php artisan serve          # Terminal 1
npm run dev               # Terminal 2 (CSS/JS watch)

# 5. Access
http://localhost:8000
# Admin: admin@autodz.dz / password123
```

If issues, check `01_INSTALLATION_ENVIRONNEMENT.md`.

---

## 🏗️ Project Architecture at a Glance

### Folders You'll Work In

```
app/
  ├── Models/              ← Database entities (User, Annonce, etc)
  ├── Http/
  │   ├── Controllers/     ← Request handlers
  │   └── Requests/        ← Form validation
  ├── Services/            ← Business logic (ProService, etc)
  └── Jobs/                ← Async tasks (ProcessAnnonceImages)

resources/
  ├── views/               ← Blade templates (.blade.php)
  │   ├── annonces/        ← Listing pages
  │   ├── pro/             ← PRO subscription pages
  │   └── components/      ← Reusable parts
  └── js/                  ← Alpine.js (lightweight reactive)

routes/
  ├── web.php              ← User-facing URLs
  └── api.php              ← Mobile app endpoints

database/
  ├── migrations/          ← Schema changes
  └── seeders/             ← Test data
```

### Key Technologies

| Stack | Purpose | Version |
|-------|---------|---------|
| Laravel | Backend framework | 12 |
| Blade | Templating | Built-in |
| Tailwind CSS | Styling | 3 |
| Alpine.js | Interactive UI | 3 |
| SQLite | Dev database | - |
| MySQL | Prod database | 8 |
| Eloquent | ORM | Laravel 12 |
| Sanctum | API auth | Built-in |

---

## 📊 Database Model Overview

**6 Core Models:**

```
User
  ├── Many Annonce (seller)
  ├── Many Favorite
  ├── Many Conversation (buyer or seller)
  └── Many Subscription (PRO)

Annonce (vehicle listing)
  ├── BelongsTo User
  ├── BelongsTo CarBrand (via 'marque' string)
  ├── BelongsTo CarModel (via 'modele' string)
  ├── Many Favorite
  ├── Many Conversation
  └── Many Boost

Conversation (messaging)
  ├── BelongsTo Annonce
  ├── BelongsTo User (buyer)
  ├── BelongsTo User (seller)
  └── Many Message

Message
  ├── BelongsTo Conversation
  └── BelongsTo User (sender)

Subscription (PRO)
  ├── BelongsTo User
  └── HasMany Boost

Boost (featured listing)
  ├── BelongsTo Annonce
  ├── BelongsTo Subscription
```

See `03_BASES_DE_DONNEES.md` for complete schema with SQL examples.

---

## 🚀 Common Development Tasks

### Task 1: Add a New Column to Annonce

**Scenario:** Add `transmission_type` field (AWD, RWD, FWD)

**Steps:**

1. **Create migration:**
   ```bash
   php artisan make:migration add_transmission_type_to_annonces
   ```

2. **Edit** `database/migrations/xxxx_add_transmission_type_to_annonces.php`:
   ```php
   public function up(): void
   {
       Schema::table('annonces', function (Blueprint $table) {
           $table->string('transmission_type')->nullable();
       });
   }
   ```

3. **Run migration:**
   ```bash
   php artisan migrate
   ```

4. **Update** `app/Models/Annonce.php`:
   ```php
   protected $fillable = [
       // ... existing fields
       'transmission_type',
   ];
   ```

5. **Add to form** (`resources/views/annonces/partials/form.blade.php`):
   ```blade
   <select name="transmission_type">
       <option value="">-- Sélectionner --</option>
       <option value="FWD">Traction avant</option>
       <option value="RWD">Propulsion</option>
       <option value="AWD">Intégrale</option>
   </select>
   ```

6. **Add validation** (in controller):
   ```php
   'transmission_type' => 'nullable|in:FWD,RWD,AWD',
   ```

7. **Display in view** (`resources/views/annonces/show.blade.php`):
   ```blade
   @if($annonce->transmission_type)
       <p>Transmission: {{ $annonce->transmission_type }}</p>
   @endif
   ```

8. **Test:**
   ```bash
   php artisan test tests/Feature/AnnonceTest.php::test_create_with_transmission
   ```

### Task 2: Create a New API Endpoint

**Scenario:** Add endpoint to get seller's latest 4 annonces (for profile card)

**Steps:**

1. **Create method** in `app/Http/Controllers/SellerController.php`:
   ```php
   public function recentAnnonces(User $seller)
   {
       return response()->json([
           'data' => $seller->annonces()
               ->where('is_active', true)
               ->latest()
               ->take(4)
               ->get()
               ->map(fn($a) => [
                   'id' => $a->id,
                   'titre' => $a->titre,
                   'prix' => $a->prix,
                   'image' => $a->image_path ? asset('storage/' . $a->image_path) : null,
               ]),
       ]);
   }
   ```

2. **Add route** in `routes/api.php`:
   ```php
   Route::get('/users/{user}/recent-annonces', 
       [SellerController::class, 'recentAnnonces']
   );
   ```

3. **Test:**
   ```bash
   curl https://localhost:8000/api/users/1/recent-annonces
   # Returns JSON with seller's latest annonces
   ```

4. **Add test** in `tests/Feature/SellerApiTest.php`:
   ```php
   public function test_get_seller_recent_annonces()
   {
       $seller = User::factory()->create();
       Annonce::factory(5)->create(['user_id' => $seller->id, 'is_active' => true]);
       
       $response = $this->getJson("/api/users/{$seller->id}/recent-annonces");
       
       $response->assertOk();
       $response->assertJsonCount(4, 'data');
   }
   ```

### Task 3: Add User Authentication Check

**Scenario:** Only logged-in users can message sellers

**Steps:**

1. **Add middleware to route** in `routes/web.php`:
   ```php
   Route::post('/annonces/{id}/message', [MessageController::class, 'start'])
       ->middleware(['auth', 'verified']);  // Must be logged in
   ```

2. **In controller**, check authorization:
   ```php
   public function start(Request $request, Annonce $annonce)
   {
       // Can't message yourself
       if ($annonce->user_id === auth()->id()) {
           abort(403, "Vous ne pouvez pas vous envoyer un message");
       }
       
       // Create conversation...
   }
   ```

3. **In view**, show button conditionally:
   ```blade
   @auth
       <button onclick="startConversation({{ $annonce->id }})">
           Contacter le vendeur
       </button>
   @else
       <a href="{{ route('login') }}">
           Se connecter pour contacter ce vendeur
       </a>
   @endauth
   ```

### Task 4: Add Validation Rule

**Scenario:** Annonce price must be reasonable (not < 100k DA, not > 50M DA)

**Steps:**

1. **In controller** (or create custom rule):
   ```php
   $validated = $request->validate([
       'prix' => [
           'required',
           'integer',
           'min:100000',    // At least 100k
           'max:50000000',  // Max 50M
       ],
   ], [
       'prix.min' => 'Le prix minimum est 100 000 DA (10k €)',
       'prix.max' => 'Le prix maximum est 50 000 000 DA (330k €)',
   ]);
   ```

2. **Or create custom rule** (`app/Rules/ValidVehiclePrice.php`):
   ```php
   public function validate(string $attribute, mixed $value, Closure $fail): void
   {
       if ($value < 100000 || $value > 50000000) {
           $fail('Le prix doit être entre 100k et 50M DA');
       }
   }
   ```

3. **Use in controller:**
   ```php
   'prix' => ['required', 'integer', new ValidVehiclePrice()],
   ```

### Task 5: Debug an Issue

**Issue:** Images not showing in annonce detail page

**Debug Steps:**

```php
// In annonceShow view or controller:

// 1. Check image_path exists
echo "Image path: " . $annonce->image_path ?? 'NULL'; // If NULL, problem

// 2. Check file exists in storage
file_exists(storage_path('app/public/' . $annonce->image_path))
    ? 'File exists ✓'
    : 'File missing ✗';

// 3. Check URL is correct
echo asset('storage/' . $annonce->image_path);
// Should output: http://localhost:8000/storage/annonces/uuid.jpg

// 4. Check database record
User::where('email', 'debug@test.com')->first()->annonces;
// Or in tinker:
// > $a = Annonce::find(1)
// > $a->image_path
// > Storage::disk('public')->exists('annonces/xyz.jpg')

// 5. Check permissions
ls -la storage/app/public/annonces/
// Should show readable files

// 6. Check symlink
ls -la public/storage
// Should point to storage/app/public
// If broken: php artisan storage:link
```

---

## 🔐 Security Sensitive Code Areas

### 1. Payment Proof Upload

**File:** `app/Http/Controllers/SubscriptionController.php`

⚠️ CRITICAL: This is where payment proofs are uploaded

**What to watch:**
- Validate file is image (JPG/PNG only)
- Check file < 4MB
- Store in secure location (storage/app, NOT public)
- Never trust filename from user
- Validate payment_status only by admin

**Safe code:**
```php
if ($request->hasFile('payment_proof')) {
    $file = $request->file('payment_proof');
    
    // Validate type
    $file->validate(['image', 'mimes:jpg,png', 'max:4096']);
    
    // Generate safe filename
    $filename = 'payments/' . Str::uuid() . '.' . $file->extension();
    
    // Store (not public)
    $path = Storage::disk('private')->put($filename, $file);
    
    $subscription->update(['payment_proof_path' => $path]);
}
```

### 2. Image Upload & Processing

**File:** `app/Jobs/ProcessAnnonceImages.php`

⚠️ HIGH: Images are user-uploaded but publicly visible

**What to watch:**
- Check file is actually image (use fileinfo, not just extension)
- Sanitize filename (remove special chars)
- Generate new UUID filename
- Apply watermark (proves images are from AutoDZ)
- Store at reasonable quality (70% JPEG OK)

### 3. User Banning

**File:** `app/Http/Controllers/AdminController.php`

⚠️ HIGH: Admin can ban users, disable accounts, delete listings

**What to watch:**
- Only `is_admin=true` users can access /admin/*
- Log all admin actions (for audit trail)
- Don't allow regular users to become admin
- Check `is_banned` before allowing login

### 4. Message Content

**File:** `app/Http/Controllers/MessageController.php`

⚠️ MEDIUM: Users can send any text, risk of abuse/spam

**What to watch:**
- Sanitize message text (remove XSS attempts)
- Don't allow spamming (check rate limits)
- Archive/delete illegal content
- Monitor for payment outside platform

**Safe code:**
```php
$message = $request->validate([
    'body' => 'required|string|max:5000',
]);

// Sanitize
$message['body'] = strip_tags($message['body']);

Message::create($message);
```

---

## 🔍 Code Pattern Recognition

### When You See This Parameter...

| Parameter | Meaning | Default |
|-----------|---------|---------|
| `is_active` | Annonce approved by admin | false |
| `is_admin` | User is administrator | false |
| `is_banned` | User prevented from access | false |
| `payment_status` | Subscription payment state | 'pending' |
| `is_pro` | User has active subscription | (computed) |
| `deleted_at` | Soft delete timestamp | NULL |

### When You See This Field...

| Field | What it Means | Example |
|-------|---------------|---------|
| `marque` | Car brand (string) | "Renault" |
| `modele` | Car model (string) | "Clio" |
| `boite_vitesse` | Gearbox | "Manuelle" or "Automatique" |
| `carburant` | Fuel type | "Essence", "Diesel", "Hybride", "Électrique" |
| `vehicle_type` | Vehicle category | "Voiture", "Utilitaire", "Moto" |
| `condition` | Is new? | "oui" or "non" |
| `image_path` | Path to image slot 1 | "annonces/uuid-1.jpg" |
| `image_path_2...5` | Image slots 2-5 | Same format as image_path |

---

## 🚨 Known Issues & Gotchas

### 1. Google OAuth Redirect URI

**Problem:** Login with Google fails

**Cause:** Google Cloud Console redirect URIs don't match Laravel app URLs

**Fix:**
```
Google Cloud Console → Settings → Authorized URIs:
- Add: http://127.0.0.1:8000/auth/google/callback (dev)
- Add: https://caro.laravel.cloud/auth/google/callback (prod)
```

### 2. Image Processing Not Running

**Problem:** Images don't get resized/watermarked

**Cause:** Queue driver is not running (default: sync, runs immediately)

**Fix:**
```bash
# In development, use sync driver (executes immediately)
# In .env: QUEUE_CONNECTION=sync

# In production, use redis
# In .env: QUEUE_CONNECTION=redis
# Then run: php artisan queue:work
```

### 3. Storage Symlink Broken

**Problem:** Images show 404, path /storage/ doesn't exist

**Fix:**
```bash
php artisan storage:link
# Now /public/storage → storage/app/public
```

### 4. Tests Fail with Database Locked

**Problem:** SQLite DB locked during parallel test runs

**Fix:**
```php
// Use in-memory DB instead (tests/Feature/TestCase.php)
protected $refreshTestDatabase = true;  // Use transactions instead
```

### 5. Pagination Querystring Lost

**Problem:** Search criteria disappear when clicking page 2

**Fix:**
```blade
{{ $annonces->appends(request()->query())->links() }}
<!-- Preserves ?marque=X&price_max=Y in pagination -->
```

---

## 📖 Where to Find Code

| What You Need | Where to Look | Filename |
|---------------|--------------|----------|
| Create annonce form | views | `resources/views/annonces/partials/form.blade.php` |
| Annonce validation | controller | `app/Http/Controllers/AnnonceController.php` |
| Annonce model relations | model | `app/Models/Annonce.php` |
| Image processing | job | `app/Jobs/ProcessAnnonceImages.php` |
| PRO subscription logic | service | `app/Services/ProService.php` |
| Search filters | model scope | `app/Models/Annonce.php::scopeFilter()` |
| Admin dashboard | controller | `app/Http/Controllers/AdminController.php` |
| API endpoints | controller | `app/Http/Controllers/Api/AnnonceApiController.php` |
| Message sending | controller | `app/Http/Controllers/MessageController.php` |
| Database schema | migrations | `database/migrations/` |
| Form styling | layout | `resources/views/layouts/app.blade.php` + Tailwind CSS |

---

## ✅ First Week Checklist

- [ ] Read this guide (10 min)
- [ ] Read 00_PRESENTATION_GENERALE.md (5 min)
- [ ] Complete setup from 01_INSTALLATION_ENVIRONNEMENT.md (15 min)
- [ ] Run `php artisan serve` and view http://localhost:8000 (2 min)
- [ ] Login as admin (admin@autodz.dz / password123) (2 min)
- [ ] Create test annonce (5 min)
- [ ] Approve annonce as admin (1 min)
- [ ] Read 03_BASES_DE_DONNEES.md (understand relationships) (15 min)
- [ ] Read 07_GIT_WORKFLOW.md (understand how team works) (10 min)
- [ ] Make small code change (fix typo / add comment) (5 min)
- [ ] Commit using correct format (✨ feat / 🐛 fix) (2 min)
- [ ] Create feature branch and open PR (5 min)
- [ ] Read 04_FONCTIONNALITES_DETAILLEES.md (20 min)
- [ ] Read 08_TESTS_RECETTE.md (10 min)
- [ ] Run tests: `php artisan test` (5 min)
- [ ] Ask question if stuck! 🙋‍♂️

---

## 🆘 Getting Help

### Command to Debug Anything

```bash
# 1. Check logs
tail -50 storage/logs/laravel.log
grep "ERROR" storage/logs/laravel.log

# 2. Use tinker (PHP REPL)
php artisan tinker
>>> User::count()                    # How many users?
>>> Annonce::latest()->first()       # Latest annonce
>>> Auth::user()->is_pro             # Am I pro?
>>> exit

# 3. Search codebase
grep -r "function searchByBrand" app/
grep -r "payment_proof" database/

# 4. Check database
php artisan tinker
>>> DB::select("SELECT * FROM annonces WHERE titre LIKE '%Renault%'")
>>> exit
```

### Common Questions

**Q: Where do I add a new feature?**  
A: Create a feature branch from `caro_bedro`, add code, write tests, open PR, get approval, merge to `caro_bedro`, then merge to `main` after testing.

**Q: How do I know if my code is secure?**  
A: Read `05_SECURITE.md`. Main check: Never trust user input, always validate & sanitize.

**Q: How do I run the mobile app against my dev database?**  
A: Change API URL in Flutter app to `http://192.168.x.x:8000/api` (your local IP), then requests will work against your database.

**Q: Can I delete annonces in production?**  
A: Yes, but it creates an `AnnonceDeletion` record for audit (tracks was_sold, was_flagged, was_reported).

**Q: How do I add a new payment method?**  
A: Payment is currently manual (upload proof). For real payments, integrate Stripe/Transfer in the future. See 10_ROADMAP.md.

---

## 📚 Next Documents to Read

1. **For understanding business:** 00_PRESENTATION_GENERALE.md
2. **For database work:** 03_BASES_DE_DONNEES.md
3. **For feature development:** 04_FONCTIONNALITES_DETAILLEES.md
4. **For security:** 05_SECURITE.md
5. **For production support:** 06_MONITORING_EXPLOITATION.md

---

**Questions?** Check QUICK_START.md in the root folder for troubleshooting, or ask your tech lead.

Welcome to the team! 🚀

