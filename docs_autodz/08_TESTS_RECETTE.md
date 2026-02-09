# 🧪 Testing & QA

**Audience:** QA, Developers  
**Criticité:** 🟡 MOYENNE (quality assurance)

---

## 🧪 Unit & Feature Tests

### Run Tests

```bash
# All tests
php artisan test

# Single file
php artisan test tests/Feature/AnnonceTest.php

# Single method
php artisan test --filter=testCreateAnnonce

# With coverage
php artisan test --coverage

# Stop on first failure
php artisan test --stop-on-failure
```

### Test Database

Tests use in-memory SQLite by default (`config/database.php`):

```php
'testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
],
```

### Writing Tests

```php
// tests/Feature/AnnonceTest.php
use Tests\TestCase;

class AnnonceTest extends TestCase
{
    public function test_user_can_create_annonce()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/annonces', [
            'titre' => 'Renault Clio',
            'prix' => 1500000,
            'marque' => 'Renault',
            'carburant' => 'Essence',
            'boite_vitesse' => 'Manuelle',
            'vehicle_type' => 'Voiture',
            'condition' => 'oui',
        ]);
        
        $response->assertRedirect('/annonces');
        $this->assertDatabaseHas('annonces', [
            'titre' => 'Renault Clio',
            'is_active' => false,  // Awaits admin approval
        ]);
    }
    
    public function test_admin_can_approve_annonce()
    {
        $admin = User::factory()->admin()->create();
        $annonce = Annonce::factory()->create(['is_active' => false]);
        
        $response = $this->actingAs($admin)
            ->patch("/admin/annonces/{$annonce->id}/toggle");
        
        $response->assertRedirect();
        $this->assertTrue($annonce->refresh()->is_active);
    }
}
```

### Factory Usage

```php
// Create single user
$user = User::factory()->create(['email' => 'test@test.com']);

// Create multiple annonces
$annonces = Annonce::factory(10)->create(['marque' => 'Renault']);

// Create with state
$user = User::factory()->admin()->banned()->create();

// Create and save to DB for test
$subscription = Subscription::factory()->create();
$this->assertDatabaseHas('subscriptions', [...]);
```

### Key Test Patterns

| What | Test |
|------|------|
| Authentication | User logged in, guest/auth redirect |
| Authorization | Admin can/cannot, owner can/cannot |
| Validation | Required field, wrong format, max length |
| Business logic | Quota check, boost duration, payment approval |
| Database | Record created, updated, deleted |
| API response | 200/401/403, correct JSON structure |
| Edge cases | Empty input, very large numbers, special chars |

---

## 🎯 Manual Test Cases

### 1. Annonce Creation

**Test:** User creates free annonce

```
1. Login as regular_user
2. Go to /annonces/create
3. Fill form:
   - Titre: "Renault Clio 2018"
   - Prix: "1500000"
   - Marque: "Renault"  
   - Modèle: "Clio"
   - Carburant: "Essence"
   - Boîte de vitesse: "Manuelle"
   - Véhicule: "Voiture"
   - Condition: "Non"
   - Ville: "Alger"
   - Description: "État impeccable, revision faite"
4. Upload 2 images (JPG, < 4MB each)
5. Click "Publier"
✅ EXPECTED: Redirect to annonce show with "En attente d'approbation"
✅ EXPECTED: Annonce NOT visible on home/search (is_active=false)
✅ EXPECTED: Images optimized (1280px width, watermark applied)
```

**Test:** User tries to exceed quota

```
1. Create/buy PRO subscription (quota: 10 active ads)
2. Create 10 active annonces
3. Try to create 11th active annonce
✅ EXPECTED: Error "Vous avez atteint votre limite de 10 annonces"
```

### 2. Annonce Approval (Admin)

**Test:** Admin approves annonce

```
1. Login as admin
2. Go to /admin/annonces
3. Find unapproved annonce (is_active=false)
4. Click toggle button
✅ EXPECTED: is_active becomes true
✅ EXPECTED: Annonce visible on homepage/search
✅ EXPECTED: Send notification to seller (if implemented)
```

### 3. Image Handling

**Test:** Image optimization

```
1. Create annonce with image: 4000x3000px, 3MB jpg
2. Upload image
✅ EXPECTED: Image resized to 1280x960px (aspect ratio kept)
✅ EXPECTED: Watermark overlay applied (18% of width, 45% opacity)
✅ EXPECTED: JPG compression to 70% quality
✅ EXPECTED: File size < 200KB
✅ EXPECTED: Display URL in annonce show page
```

**Test:** Image deletion

```
1. Edit annonce with 3 images (image_path, image_path_2, image_path_3)
2. Check "Supprimer" next to image_path_2 only
3. Add new image
4. Save
✅ EXPECTED: 3 images total (1 kept, 1 deleted, 1 added)
✅ EXPECTED: Deleted image removed from storage
✅ EXPECTED: Database slots correctly populated
```

### 4. Search & Filtering

**Test:** Filter by brand

```
1. Go to /recherche
2. Select "Marque: Renault"
3. Submit
✅ EXPECTED: URL contains ?marque=Renault
✅ EXPECTED: Results show only Renault vehicles
✅ EXPECTED: Count > 0
```

**Test:** Filter by price range

```
1. Go to /recherche
2. Enter "Prix max: 2000000"
3. Submit
✅ EXPECTED: Results show only prix < 2000000
✅ EXPECTED: Results sorted by prix ascending
```

**Test:** Multiple filters

```
1. Go to /recherche
2. Select: Marque=Renault, Carburant=Diesel, Prix max=1500000
3. Submit
✅ EXPECTED: Results match ALL criteria
✅ EXPECTED: Filter pills show selected filters
✅ EXPECTED: Can remove filter individually
```

### 5. Favorites

**Test:** Add/remove favorite

```
1. Login as user1
2. View annonce (not owned by user1)
3. Click heart icon (Ajouter aux favoris)
✅ EXPECTED: Heart becomes filled/highlighted
✅ EXPECTED: Annonce added to /favoris page

4. Go to /favoris
✅ EXPECTED: Annonce listed

5. Click heart again
✅ EXPECTED: Heart becomes unfilled
✅ EXPECTED: Annonce removed from /favoris
```

### 6. Messages & Conversations

**Test:** Buyer sends first message

```
1. Login as user1 (buyer)
2. View annonce owned by user2 (seller)
3. Click "Contacter le vendeur"
4. Type message: "Bonjour, c'est toujours disponible?"
5. Click "Envoyer"
✅ EXPECTED: Message sent
✅ EXPECTED: Conversation created
✅ EXPECTED: Redirect to /messages/{conversation_id}

6. Login as user2
✅ EXPECTED: See conversation in /messages
✅ EXPECTED: See new message from user1
✅ EXPECTED: last_message_at updated
```

**Test:** Message polling

```
1. Login as user1
2. Open conversation in browser
3. In another window, login as user2
4. Send message as user2
5. On user1's window, wait < 5 sec
✅ EXPECTED: New message appears (via polling /messages/{id}/new)
```

### 7. PRO Subscription

**Test:** Buy PRO plan

```
1. Login as regular user (free tier)
2. Go to /pro
✅ EXPECTED: See pricing table (FREE vs PRO)
✅ EXPECTED: See "Passer à PRO" button

3. Click "Passer à PRO"
4. Select payment method (submit fake payment proof)
5. Submit
✅ EXPECTED: Subscription created (payment_status='pending')
✅ EXPECTED: Redirect to /pro/status
✅ EXPECTED: "En attente d'approbation" message
✅ EXPECTED: Quota not yet active (still free tier limits)

6. Admin approves payment proof
   - Login as admin
   - Go to /admin/payments (if exists)
   - Approve payment
✅ EXPECTED: payment_status='approved'
✅ EXPECTED: User now sees PRO features in /pro/status
✅ EXPECTED: Can create up to 10 active annonces
✅ EXPECTED: Can use boost feature
```

### 8. Boost Feature

**Test:** Boost an annonce

```
1. Login as PRO user
2. Go to /mes-annonces
3. Click "Booster cette annonce" on an active annonce
✅ EXPECTED: Boost created with 7-day duration (default)
✅ EXPECTED: Boost shows in /pro/boosts page
✅ EXPECTED: Annonce appears higher in search results

4. Wait 7 days OR manually update database:
   mysql> UPDATE boosts SET created_at = DATE_SUB(NOW(), INTERVAL 8 DAY);
   
5. Run scheduler:
   php artisan ExpireBoosts
✅ EXPECTED: Boost marked as expired
✅ EXPECTED: Annonce drops in ranking
```

**Test:** Boost quota

```
1. Login as PRO user
2. Count active boosts this month:
   SELECT COUNT(*) FROM boosts WHERE MONTH(created_at) = MONTH(NOW());
✅ EXPECTED: Count < 5 (monthly quota)

3. Try to boost 6th annonce this month
✅ EXPECTED: Error "Vous avez atteint votre limite de 5 boosts ce mois"
```

### 9. Admin Controls

**Test:** Ban user

```
1. Login as admin
2. Go to /admin/users
3. Find a user
4. Click "Ban" toggle
✅ EXPECTED: is_banned = true
✅ EXPECTED: User logged out if active
✅ EXPECTED: User cannot login

5. Try to login as banned user
✅ EXPECTED: Error "Votre compte a été désactivé"
```

**Test:** Bulk action

```
1. Admin goes to /admin/annonces
2. Select 3 annonces checkboxes
3. Select action "Mark as active"
4. Click "Appliquer"
✅ EXPECTED: All 3 annonces are_active = true
✅ EXPECTED: Success message shows count
```

### 10. Email & Notifications

**Test:** Verification email (if Breeze has verification)

```
1. Register new account
2. Check email inbox (or Laravel log)
✅ EXPECTED: Verification email sent
✅ EXPECTED: Email contains verify link
✅ EXPECTED: Click link → account verified
```

### 11. Payment Proof Upload

**Test:** Upload payment proof

```
1. During PRO purchase, click "Continuer"
2. Select "Importer une preuve de paiement"
3. Upload image (payment screenshot)
✅ EXPECTED: File stored in storage/app/public/payments/
✅ EXPECTED: payment_proof_path set in database
✅ EXPECTED: Admin can review in /admin/payments
```

### 12. API Tests (Mobile App)

**Test:** Register & Login

```bash
curl -X POST https://caro.laravel.cloud/api/register \
  -d "name=Ahmed&email=ahmed@test.com&password=Password123&phone=05551234567"
# ✅ EXPECTED: token in response

curl -X POST https://caro.laravel.cloud/api/login \
  -d "email=ahmed@test.com&password=Password123"
# ✅ EXPECTED: token in response
```

**Test:** List annonces with filter

```bash
curl "https://caro.laravel.cloud/api/annonces?marque=Renault&price_max=2000000" \
  -H "Authorization: Bearer TOKEN"
# ✅ EXPECTED: Filtered results, paginated
```

---

## 🔍 Regression Testing

After each deployment, test:

### Critical Paths

- [ ] User can create annonce (free)
- [ ] Admin can approve annonce
- [ ] User can search/filter
- [ ] User can toggle favorite
- [ ] Buyer can message seller
- [ ] PRO user can subscribe
- [ ] PRO user can boost
- [ ] Admin can ban user
- [ ] Images display correctly
- [ ] No 500 errors in logs

### Performance

- [ ] Homepage loads in < 1s
- [ ] Search results load in < 500ms
- [ ] Admin dashboard loads in < 2s
- [ ] Image optimization < 3s

### UX

- [ ] Form validation messages in French
- [ ] Error messages clear and helpful
- [ ] Mobile responsive (test on iPhone)
- [ ] Links work (no 404s)
- [ ] Images load without broken links

---

## 🐛 Bug Report Template

**When you find a bug:**

```markdown
## Title
[Brief description]

## Reproduction Steps
1. Login as [user type]
2. Go to [URL]
3. Click [element]
4. Enter [input]

## Expected Behavior
[What should happen]

## Actual Behavior
[What actually happened]

## Screenshots
[Images/video]

## Environment
- Browser: Chrome 90
- Device: Desktop / Mobile
- URL: https://caro.laravel.cloud/[path]

## Logs
[Error message from console/Laravel logs]
```

---

## ✅ Test Checklist (Before Release)

```
[ ] All unit/feature tests pass (php artisan test)
[ ] No console errors (F12 → Console)
[ ] No 500 errors in storage/logs/laravel.log
[ ] Images display & optimized
[ ] Forms validate correctly
[ ] Auth (login/register/logout) works
[ ] Search/filters work
[ ] Admin features work
[ ] PRO features work
[ ] Messages send/receive
[ ] API endpoints respond correctly
[ ] Mobile responsive layout
[ ] Payment flow (mock) works
[ ] Images process asynchronously
[ ] Scheduler commands run
[ ] No hardcoded secrets in code
[ ] Performance acceptable (< 1s pageload)
[ ] Database queries optimized (no N+1)
[ ] Error handling graceful (friendly messages)
```

