<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AnnonceApiController;
use App\Http\Controllers\Api\FavoriteApiController;
use App\Http\Controllers\Api\MessageApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Routes pour l'application mobile Flutter Caro
| Documentation: BACKEND_SPECS.md
*/

// Auth (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Annonces publiques (lecture seule)
Route::get('/annonces', [AnnonceApiController::class, 'index']);
Route::get('/annonces/{id}', [AnnonceApiController::class, 'show']);
Route::get('/users/{id}/annonces', [AnnonceApiController::class, 'userAnnonces']);
Route::get('/annonces/{id}/stats', [AnnonceApiController::class, 'stats']);
Route::post('/annonces/{id}/view', [AnnonceApiController::class, 'incrementView']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Annonces (création/modification/suppression)
    Route::post('/annonces', [AnnonceApiController::class, 'store']);
    Route::put('/annonces/{id}', [AnnonceApiController::class, 'update']);
    Route::post('/annonces/{id}', [AnnonceApiController::class, 'update']); // Support _method=PUT pour multipart
    Route::get('/my-annonces', [AnnonceApiController::class, 'myAnnonces']);
    Route::delete('/annonces/{id}', [AnnonceApiController::class, 'destroy']);
    
    // Favoris
    Route::post('/favoris/toggle', [FavoriteApiController::class, 'toggle']);
    Route::get('/favoris', [FavoriteApiController::class, 'index']);
    
    // Messagerie
    Route::get('/conversations', [MessageApiController::class, 'index']);
    Route::get('/conversations/{id}', [MessageApiController::class, 'show']);
    Route::post('/conversations/{id}/read', [MessageApiController::class, 'markAsRead']);
    Route::post('/messages', [MessageApiController::class, 'store']);
});
