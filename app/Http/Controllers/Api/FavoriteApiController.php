<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Annonce;
use Illuminate\Http\Request;

class FavoriteApiController extends Controller
{
    /**
     * Toggle favorite
     * POST /api/favoris/toggle
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'annonce_id' => 'required|exists:annonces,id',
        ]);

        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('annonce_id', $request->annonce_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
            $message = 'Annonce retirée des favoris';
        } else {
            Favorite::create([
                'user_id' => $request->user()->id,
                'annonce_id' => $request->annonce_id,
            ]);
            $isFavorite = true;
            $message = 'Annonce ajoutée aux favoris';
        }

        return response()->json([
            'message' => $message,
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Get user's favorite annonces
     * GET /api/favoris
     */
    public function index(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with('annonce.user')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $favorites->map(function($favorite) {
            if (!$favorite->annonce) {
                return null;
            }

            $annonce = $favorite->annonce;
            $disk = env('FILESYSTEM_DISK', 's3');
            $images = [];
            $imageFields = ['image_path', 'image_path_2', 'image_path_3', 'image_path_4', 'image_path_5'];
            
            foreach ($imageFields as $field) {
                if ($annonce->$field) {
                    $images[] = Storage::disk($disk)->url($annonce->$field);
                }
            }

            return [
                'id' => $annonce->id,
                'title' => $annonce->titre,
                'description' => $annonce->description,
                'price' => (int) $annonce->prix,
                'marque' => $annonce->marque,
                'modele' => $annonce->modele,
                'year' => $annonce->annee,
                'km' => $annonce->kilometrage,
                'fuel' => $annonce->carburant,
                'gearbox' => $annonce->boite_vitesse,
                'wilaya' => $annonce->ville,
                'isNew' => $annonce->condition === 'neuf',
                'color' => $annonce->couleur,
                'documentType' => $annonce->document_type,
                'finition' => $annonce->finition,
                'images' => $images,
                'views' => $annonce->views,
                'createdAt' => $annonce->created_at->toIso8601String(),
                'isFavorite' => true,
                'isActive' => $annonce->is_active,
                'user' => [
                    'id' => $annonce->user->id,
                    'name' => $annonce->user->name,
                    'phone' => $annonce->show_phone ? $annonce->user->phone : null,
                    'avatar' => $annonce->user->avatar ? Storage::disk($disk)->url($annonce->user->avatar) : null,
                ],
            ];
        })->filter(); // Remove null values

        return response()->json([
            'data' => $data->values(),
        ]);
    }
}
