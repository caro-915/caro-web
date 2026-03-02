<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SellerController;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminAnnonceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProController;
use App\Http\Controllers\BoostController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PhoneController;

/*
|--------------------------------------------------------------------------
| SEO: Sitemap & Robots
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| CONTACT (public, avec rate limiting)
|--------------------------------------------------------------------------
*/
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])
    ->middleware('throttle:10,1') // max 10 requêtes par minute
    ->name('contact.send');

/*
|--------------------------------------------------------------------------
| TEST EMAIL (local/dev uniquement) - SUPPRIMER EN PRODUCTION
|--------------------------------------------------------------------------
*/
if (app()->environment('local', 'development', 'staging')) {
    Route::get('/test-email', function () {
        try {
            \Illuminate\Support\Facades\Mail::to(config('autodz.contact_email', 'contact@elsayara.com'))
                ->send(new \App\Mail\ContactMessageMail([
                    'name'       => 'Test User',
                    'email'      => 'test@example.com',
                    'phone'      => '0555123456',
                    'subject'    => 'Test Email ElSayara',
                    'body'       => 'Ceci est un email de test envoyé depuis la route /test-email.',
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'sent_at'    => now()->format('d/m/Y à H:i'),
                ]));

            return response()->json([
                'success' => true,
                'message' => 'Email envoyé avec succès à ' . config('autodz.contact_email', 'contact@elsayara.com'),
                'from'    => config('mail.from.address'),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Test email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    })->name('test.email');
}

/*
|--------------------------------------------------------------------------
| GOOGLE OAUTH
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/domicile', function () {
    return redirect()->route('home');
})->name('domicile');

/*
|--------------------------------------------------------------------------
| ANNONCES - Public
|--------------------------------------------------------------------------
*/
Route::get('/recherche', [AnnonceController::class, 'search'])->name('annonces.search');

/*
|--------------------------------------------------------------------------
| SELLER PROFILE - Public
|--------------------------------------------------------------------------
*/
Route::get('/vendeur/{user}', [SellerController::class, 'show'])->name('seller.show');

/*
|--------------------------------------------------------------------------
| ROUTES AUTHENTIFIÉES (auth + banned)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'banned'])->group(function () {

    /*
    |-------------------------
    | Annonces (CRUD)
    |-------------------------
    */
    Route::get('/annonces/create', [AnnonceController::class, 'create'])->name('annonces.create');
    Route::post('/annonces', [AnnonceController::class, 'store'])->name('annonces.store');
    Route::post('/annonces/clean-temp-images', [AnnonceController::class, 'cleanTempImages'])->name('annonces.cleanTempImages');

    Route::get('/annonces/{annonce}/edit', [AnnonceController::class, 'edit'])->name('annonces.edit');
    Route::put('/annonces/{annonce}', [AnnonceController::class, 'update'])->name('annonces.update');

    Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy'])->name('annonces.destroy');

    Route::get('/mes-annonces', [AnnonceController::class, 'myAds'])->name('annonces.my');

    // API routes for dynamic data
    Route::get('/api/models', [AnnonceController::class, 'getModels'])->name('api.models');

    /*
    |-------------------------
    | Favoris
    |-------------------------
    */
    Route::get('/favoris', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/annonces/{annonce}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    /*
    |-------------------------
    | Messagerie
    |-------------------------
    */
    Route::post('/annonces/{annonce}/messages', [MessageController::class, 'start'])->name('messages.start');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/messages/{conversation}/new', [MessageController::class, 'fetchNew'])->name('messages.new');

    Route::get('/messages/unread-count', function () {
        if (!auth()->check()) {
            return response()->json(['count' => 0]);
        }
        
        return response()->json([
            'count' => \App\Models\Message::whereHas('conversation', function ($q) {
                    $q->where('buyer_id', auth()->id())
                      ->orWhere('seller_id', auth()->id());
                })
                ->whereNull('read_at')
                ->where('sender_id', '!=', auth()->id())
                ->count(),
        ]);
    })->name('messages.unread-count');

    /*
    |-------------------------
    | Historique de recherche & Alertes
    |-------------------------
    */
    Route::get('/historique-recherche', [\App\Http\Controllers\SearchHistoryController::class, 'index'])->name('search.history');
    Route::get('/mes-alertes-resultats', [\App\Http\Controllers\SearchHistoryController::class, 'alertResults'])->name('search.alert.results');
    Route::post('/alertes/creer', [\App\Http\Controllers\SearchHistoryController::class, 'createAlert'])->name('search.alert.create');
    Route::delete('/alertes/{id}', [\App\Http\Controllers\SearchHistoryController::class, 'deleteAlert'])->name('search.alert.delete');

    // Route de test temporaire pour déboguer
    Route::get('/test-search-history', function() {
        try {
            $searches = \App\Models\SearchHistory::where('user_id', auth()->id())->latest()->take(10)->get();
            $alerts = \App\Models\SearchAlert::where('user_id', auth()->id())->where('is_active', true)->latest()->get();
            return response()->json([
                'success' => true,
                'searches_count' => $searches->count(),
                'alerts_count' => $alerts->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    });

    /*
    |-------------------------
    | Profil (Breeze)
    |-------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |-------------------------
    | Téléphone (validation compte Google)
    |-------------------------
    */
    Route::get('/compte/telephone', [PhoneController::class, 'edit'])->name('phone.edit');
    Route::post('/compte/telephone', [PhoneController::class, 'update'])->name('phone.update');

    /*
    |-------------------------
    | PRO Subscription
    |-------------------------
    */
    Route::get('/pro', [ProController::class, 'index'])->name('pro.index');
    Route::get('/pro/subscribe/{plan}', [ProController::class, 'create'])->name('pro.subscribe.form');
    Route::post('/pro/subscribe/{plan}', [ProController::class, 'store'])->name('pro.subscribe');
    Route::get('/pro/status', [ProController::class, 'status'])->name('pro.status');

    /*
    |-------------------------
    | Boost Annonce
    |-------------------------
    */
    Route::post('/annonces/{annonce}/boost', [BoostController::class, 'store'])->name('annonces.boost');
});

/*
|--------------------------------------------------------------------------
| FICHE ANNONCE - Public (SEO-friendly URL with slug)
| Supports: /annonces/123-renault-clio-2020
| Legacy /annonces/123 will 301 redirect to canonical URL
| IMPORTANT: toujours après /annonces/create et /annonces/{annonce}/edit
|--------------------------------------------------------------------------
*/
Route::get('/annonces/{annonce}-{slug}', [AnnonceController::class, 'show'])
    ->where('annonce', '[0-9]+')
    ->where('slug', '[a-z0-9\-]+')
    ->name('annonces.show');

// Legacy route without slug - redirects to canonical URL
Route::get('/annonces/{annonce}', [AnnonceController::class, 'show'])
    ->where('annonce', '[0-9]+')
    ->name('annonces.show.legacy');

/*
|--------------------------------------------------------------------------
| DASHBOARD (Breeze) - auth only (email verification disabled)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMIN (auth + admin)  ✅ (option: ajouter 'banned' aussi)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Annonces
        Route::get('/annonces', [AdminAnnonceController::class, 'index'])->name('annonces.index');
        Route::patch('/annonces/{annonce}/toggle', [AdminAnnonceController::class, 'toggle'])->name('annonces.toggle');
        Route::delete('/annonces/{annonce}', [AdminAnnonceController::class, 'destroy'])->name('annonces.destroy');
        Route::post('/annonces/bulk-action', [AdminAnnonceController::class, 'bulkAction'])->name('annonces.bulkAction');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggleAdmin');
        
        Route::patch('/users/{user}/toggle-ban', [AdminUserController::class, 'toggleBan'])->name('users.toggleBan');
        Route::get('/stats', [\App\Http\Controllers\Admin\AdminStatsController::class, 'index'])->name('stats.index');

        // Plans
        Route::resource('plans', AdminPlanController::class);

        // Subscriptions
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::get('/subscriptions/{subscription}/proof', [AdminSubscriptionController::class, 'proof'])->name('subscriptions.proof');
        Route::get('/subscriptions/{subscription}/proof-check', [AdminSubscriptionController::class, 'proofCheck'])->name('subscriptions.proof.check');
        Route::patch('/subscriptions/{subscription}/approve', [AdminSubscriptionController::class, 'approve'])->name('subscriptions.approve');
        Route::patch('/subscriptions/{subscription}/reject', [AdminSubscriptionController::class, 'reject'])->name('subscriptions.reject');

    });


/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
