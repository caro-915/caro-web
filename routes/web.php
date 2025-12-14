<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SellerController;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminAnnonceController;
use App\Http\Controllers\Admin\AdminUserController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', [AnnonceController::class, 'index'])->name('home');

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

    Route::get('/annonces/{annonce}/edit', [AnnonceController::class, 'edit'])->name('annonces.edit');
    Route::put('/annonces/{annonce}', [AnnonceController::class, 'update'])->name('annonces.update');

    Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy'])->name('annonces.destroy');

    Route::get('/mes-annonces', [AnnonceController::class, 'myAds'])->name('annonces.my');

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
    | Profil (Breeze)
    |-------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| FICHE ANNONCE - Public
| IMPORTANT: toujours après /annonces/create et /annonces/{annonce}/edit
|--------------------------------------------------------------------------
*/
Route::get('/annonces/{annonce}', [AnnonceController::class, 'show'])->name('annonces.show');

/*
|--------------------------------------------------------------------------
| DASHBOARD (Breeze) - auth + verified
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMIN (auth + admin)  ✅ (option: ajouter 'banned' aussi)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin','banned'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Annonces
        Route::get('/annonces', [AdminAnnonceController::class, 'index'])->name('annonces.index');
        Route::patch('/annonces/{annonce}/toggle', [AdminAnnonceController::class, 'toggle'])->name('annonces.toggle');
        Route::delete('/annonces/{annonce}', [AdminAnnonceController::class, 'destroy'])->name('annonces.destroy');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggleAdmin');
        
        Route::patch('/users/{user}/toggle-ban', [AdminUserController::class, 'toggleBan'])->name('users.toggleBan');
        Route::get('/stats', [\App\Http\Controllers\Admin\AdminStatsController::class, 'index'])->name('stats.index');

    });


/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
