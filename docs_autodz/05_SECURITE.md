# 🔐 Sécurité - Guide Complet

**Criticité:** 🔴 HAUTE  
**Audience:** Tous développeurs & DevOps


---

## 🛡️ Stratégie de Sécurité

```
SÉCURITÉ GLOBALE
├─ AUTHENTIFICATION
│  ├─ Breeze (session) + oauth Google  
│  ├─ Sanctum (API tokens)
│  └─ rate limiting
│
├─ AUTHORIZATION
│  ├─ Policies (gate access to resources)
│  ├─ Middleware (check roles/ban)
│  └─ AnnouncePolicy, AdminMiddleware
│
├─ VALIDATION
│  ├─ Request validation (server-side)
│  ├─ Type casting
│  └─ Enum constraints
│
├─ DONNÉES SENSIBLES
│  ├─ Passwords: bcrypt (never plaintext)
│  ├─ Tokens: Sanctum (auto rotation)
│  ├─ Files: Private storage
│  └─ Payment proofs: Encrypted
│
├─ PROTECTION WEB
│  ├─ CSRF (VerifyCsrfToken middleware)
│  ├─ XSS (Blade escaping by default)
│  ├─ SQL Injection (Eloquent queries)
│  └─ CORS (api routes protected)
│
└─ INFRASTRUCTURE
   ├─ HTTPS enforced (production)
   ├─ Firewall rules
   ├─ DB backups encrypted
   └─ Logs monitored
```


---

## 👤 Authentification

### Session-based (Web)

**Middleware:** `web` stack in `config/auth.php`

```php
public function boot()
{
    Route::middleware('web')->group(function () {
        Route::post('/login', [AuthController::class, 'store']);
    });
}
```

**Protecteurs:**
- ✅ CSRF token sur les POST/PUT
- ✅ Session driver (cookie-based)
- ✅ Email verification (Notification)
- ✅ Password reset via email

**Vérifier le login:**

```blade
@auth
    {!! auth()->user()->name !!}
@endauth

@guest
    <!-- Show login button -->
@endguest
```

### Token-based (API / Sanctum)

**Fichier:** `config/sanctum.php`

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:3000')),
'expiration' => 525600,  // 1 year
```

**Flow:**

```plaintext
Mobile App POSTs /api/login
  ├─ Verify credentials
  ├─ Create plaintext token
  └─ Return: {"token": "abc123xyz..."}

App stores token in secure storage
  ↓
Future API calls:
  ├─ Header: Authorization: Bearer abc123xyz...
  ├─ Middleware auth:sanctum validates token
  └─ Proceed if valid
```

**Code:**

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn() => auth()->user());
    Route::post('/annonces', [AnnonceApiController::class, 'store']);
});

// Test token
curl -H "Authorization: Bearer {token}" https://api.autodz.dz/api/user
```

### Google OAuth

**Fichier:** `config/services.php`

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

**Security:**

- ✅ `GOOGLE_CLIENT_SECRET` **NEVER** in git (use `.env`)
- ✅ `GOOGLE_REDIRECT_URI` must **EXACTLY** match console (http://localhost:8000/auth/google/callback for DEV, https://caro.laravel.cloud/auth/google/callback for PROD)
- ✅ Tokens never logged / exposed

**Verify setup:**

```bash
# Check env vars are set
php artisan tinker
echo config('services.google.client_id');  # Should output ID
exit
```

---

## 🔒 Authorization & Policies

### Policies

**Fichier:** `app/Policies/AnnoncePolicy.php`

```php
public function update(User $user, Annonce $annonce): bool
{
    // Only owner or admin can edit
    return $user->is_admin || $user->id === $annonce->user_id;
}

public function delete(User $user, Annonce $annonce): bool
{
    return $user->is_admin || $user->id === $annonce->user_id;
}

public function view(?User $user, Annonce $annonce): bool
{
    // Non-active annonces only viewable by owner/admin
    if (!$annonce->is_active) {
        return $user && ($user->is_admin || $user->id === $annonce->user_id);
    }
    return true;  // Active annonces visible to all
}
```

**Usage in Controller:**

```php
public function update(Request $request, Annonce $annonce)
{
    // Throws 403 if not authorized
    $this->authorize('update', $annonce);
    
    // ... update code
}

// Or in Blade
@can('update', $annonce)
    <a href="/annonces/{{ $annonce->id }}/edit">Edit</a>
@endcan
```

### Middleware: Role-based

```php
// app/Http/Middleware/AdminMiddleware.php

public function handle($request, Closure $next)
{
    if (!auth()->user()?->is_admin) {
        abort(403, 'Accès refusé');
    }
    return $next($request);
}

// Usage in route
Route::middleware('admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

### Middleware: Ban Check

```php
// app/Http/Middleware/EnsureUserNotBanned.php

public function handle($request, Closure $next)
{
    if (auth()->user()?->is_banned) {
        auth()->logout();  // Force logout
        return redirect('/login')
            ->with('error', 'Compte banni');
    }
    return $next($request);
}

// Applied globally in bootstrap/app.php
->withMiddleware(fn(Middleware $m) => 
    $m->web(EnsureUserNotBanned::class)
)
```

---

## ✔️ Validation

### Server-side (OBLIGATOIRE)

**JAMAIS** faire confiance au client!

```php
public function store(Request $request)
{
    // ✅ BON: Explicit validation
    $validated = $request->validate([
        'prix' => 'required|integer|min:0|max:50000000',
        'images' => 'array|max:5',
        'images.*' => 'image|mimes:jpg,png,webp|max:4096',  // Each max 4MB
    ]);
    
    // ✅ La $validated ne contient QUE les champs listés
    // (filtrage automatique pour prévention mass assignment)
}
```

**Type Casting:**

```php
// app/Models/Annonce.php

protected $casts = [
    'prix' => 'integer',  // String input auto-cast to int
    'condition' => 'boolean',
    'coordinates' => AsCollection::class,
];
```

### Custom Validations

```php
// app/Rules/ValidAlgerianPhone.php

public function validate($attribute, $value, $fail)
{
    if (!preg_match('/^(05|06)\d{8}$/', $value)) {
        $fail('Numéro invalide (format: 05/06 XXXXXXXX)');
    }
}

// Usage
$request->validate([
    'phone' => ['required', new ValidAlgerianPhone()],
]);
```

---

## 🔐 Données Sensibles

### Passwords

```php
// Creation
$user->password = bcrypt($input);  // ✅ CORRECT

$user->password = $input;  // ❌ JAMAIS! Non hashé

// Verification
Hash::check($plaintext, $hashed);  // true/false
```

### API Tokens

**Sanctum auto-manages tokens:**

```php
$user->createToken('mobile-app');  // Generate new token
$user->tokens->each->delete();      // Revoke all
```

**JAMAIS log tokens:**

```php
// ❌ MAUVAIS
Log::info("User token: {$token}");

// ✅ BON
Log::info("Token created for user");
```

### Payment Proofs (Storage)

```php
// Store in private storage (not accessible via web)
$file = $request->file('proof');
$path = $file->store('payment_proofs', 'private');

// Retrieve (only for admin/owner)
return response()->download(storage_path("app/private/{$path}"));
```

### Enviroment Variables

**JAMAIS en git ou version control:**

```env
# .env (ignored by .gitignore)
GOOGLE_CLIENT_SECRET=xxxxx  # ✅ Safe
DB_PASSWORD=secure123       # ✅ Safe

# .env.example (shared, safe)
GOOGLE_CLIENT_SECRET=null
DB_PASSWORD=null
```

**Vérifier:**

```bash
# Check .gitignore includes .env
cat .gitignore | grep "^\.env$"

# Never commit secrets
git log --all --oneline --decorate | grep -i secret  # Should be empty
```

---

## 🚨 OWASP Top 10 Mitigations

| Risque | Mitigation | AutoDZ |
|-------|-----------|--------|
| Injection (SQL) | Use Eloquent ORM | ✅ ORM only, no raw SQL |
| Broken Auth | Strong passwords + 2FA | ⚠️ Regular auth, no 2FA (future) |
| Sensitive Data | Encrypt + HTTPS | ✅ bcrypt passwords, HTTPS prod |
| XML External Entity | Disable DTD | N/A (REST only) |
| Access Control | Policies + Middleware | ✅ Policies enforced |
| Security Misconfiguration | Minimal default | ✅ Laravel opinionated |
| XSS | HTML escaping | ✅ Blade escapes by default |
| CSRF | CSRF tokens | ✅ VerifyCsrfToken middleware |
| Using known vulnerabilities | Dependency updates | ⚠️ Regular `composer outdated` |
| Logging & Monitoring | Log all auth events | ⚠️ Basic logging (future: Sentry) |

---

## 🔍 Security Testing

### Common Tests

```bash
# Check for known vulnerabilities in deps
composer audit

# Run test suite (includes auth tests)
php artisan test

# Check Laravel security practices
php artisan security-checker

# Manual auth test
php artisan tinker
\$user = User::find(1);
Hash::check('password123', \$user->password);  // true/false
exit
```

### HTTPS Verification (Production)

```bash
# Should be green
https://caro.laravel.cloud

# Check cert
openssl s_client -connect caro.laravel.cloud:443 | grep -A2 "Verify return code"
# Should be "Verify return code: 0 (ok)"
```

---

## ⚠️ Known Risks & Mitigations

| Risque | Impact | Mitigation |
|--------|--------|-----------|
| Payment proof fraud | Admin approves fake proofs | Manual human review + fraud detection (future) |
| Image malware | Malicious files uploaded | Scan with ClamAV + resize/re-encode |
| Account takeover | Email compromised | Add 2FA (future) |
| Data breach | User data exposed | Encrypt DB backups + firewalled access |
| Admin compromise | Full system compromise | Separate admin account + IP whitelist (future) |
| Rate limiting | Brute force attacks | Add rate limiter middleware (future) |

### File Upload Security

```php
// ✅ Validation strict
'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',

// ✅ Re-encoding (prevents malware)
Image::make($file)->encode('jpg', 70)->save(...);

// ✅ Unique naming (prevents overwrite)
$path = $file->store('annonces');  // Auto UUID

// ✅ Storage symlink (not served directly)
public/storage -> storage/app/public

// ✅ Don't execute uploaded files
// .htaccess or nginx config blocks PHP in upload dir
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
```

---

## 🔧 Security Checklist

- [ ] `.env` NOT in git (check `.gitignore`)
- [ ] HTTPS enabled in production
- [ ] CSRF protection enabled (middleware loaded)
- [ ] Password reset emails work
- [ ] Email verification required for signup
- [ ] Admin middleware on `/admin/*` routes
- [ ] Policies enforced (not just checked, authorized)
- [ ] Ban check middleware applied
- [ ] Payment proof files private storage
- [ ] Secrets rotated after deployment
- [ ] Rate limiting on login (future)
- [ ] 2FA for admin users (future)
- [ ] Security headers configured (HSTS, CSP)
- [ ] Logs monitored for suspicious activity

---

## 📞 Security Contact

- **Report vulnerabilities:** security@autodz.dz *(futur)*
- **Responsible disclosure:** 90 days before public release
- **Not eligible:** brute force, DDoS, automated scanning

