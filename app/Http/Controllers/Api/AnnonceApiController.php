<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnonceApiController extends Controller
{
    /**
     * Liste des annonces avec filtres et pagination
     * GET /api/annonces
     */
    public function index(Request $request)
    {
        $query = Annonce::with('user')->where('is_active', true);

        // Filtres
        if ($request->filled('marque')) {
            $query->where('marque', 'like', '%' . $request->marque . '%');
        }

        if ($request->filled('modele')) {
            $query->where('modele', 'like', '%' . $request->modele . '%');
        }

        if ($request->filled('wilaya')) {
            $query->where('ville', 'like', '%' . $request->wilaya . '%');
        }

        if ($request->filled('price_min')) {
            $query->where('prix', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('prix', '<=', $request->price_max);
        }

        if ($request->filled('year_min')) {
            $query->where('annee', '>=', $request->year_min);
        }

        if ($request->filled('year_max')) {
            $query->where('annee', '<=', $request->year_max);
        }

        if ($request->filled('km_min')) {
            $query->where('kilometrage', '>=', $request->km_min);
        }

        if ($request->filled('km_max')) {
            $query->where('kilometrage', '<=', $request->km_max);
        }

        if ($request->filled('fuel')) {
            $query->where('carburant', $request->fuel);
        }

        if ($request->filled('gearbox')) {
            $query->where('boite_vitesse', $request->gearbox);
        }

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('titre', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Tri
        switch ($request->get('sort', 'latest')) {
            case 'price_asc':
                $query->orderBy('prix', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('prix', 'desc');
                break;
            case 'km_asc':
                $query->orderBy('kilometrage', 'asc');
                break;
            case 'year_desc':
                $query->orderBy('annee', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $annonces = $query->paginate(20);

        // Get user's favorites
        $favoriteIds = [];
        if ($request->user()) {
            $favoriteIds = Favorite::where('user_id', $request->user()->id)
                ->pluck('annonce_id')
                ->toArray();
        }

        return response()->json([
            'data' => $annonces->map(function($annonce) use ($favoriteIds) {
                return $this->formatAnnonce($annonce, in_array($annonce->id, $favoriteIds));
            }),
            'current_page' => $annonces->currentPage(),
            'last_page' => $annonces->lastPage(),
            'per_page' => $annonces->perPage(),
            'total' => $annonces->total(),
        ]);
    }

    /**
     * Détail d'une annonce
     * GET /api/annonces/{id}
     */
    public function show(Request $request, $id)
    {
        $annonce = Annonce::with('user')->findOrFail($id);

        // Increment views only if not the owner
        if (!$request->user() || $request->user()->id !== $annonce->user_id) {
            $annonce->increment('views');
        }

        $isFavorite = false;
        if ($request->user()) {
            $isFavorite = Favorite::where('user_id', $request->user()->id)
                ->where('annonce_id', $annonce->id)
                ->exists();
        }

        return response()->json($this->formatAnnonce($annonce, $isFavorite));
    }

    /**
     * Créer une annonce
     * POST /api/annonces
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'prix'          => 'required|integer|min:0',
            'marque'        => 'required|string|max:100',
            'modele'        => 'nullable|string|max:100',
            'annee'         => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'kilometrage'   => 'nullable|integer|min:0',
            'carburant'     => 'required|string|max:50',
            'boite_vitesse' => 'required|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'vehicle_type'  => 'nullable|string|max:50',
            'show_phone'    => 'nullable',
            'couleur'       => 'nullable|string|max:50',
            'document_type' => 'nullable|in:carte_grise,procuration',
            'finition'      => 'nullable|string|max:80',
            'condition'     => 'required|in:oui,non',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'marque.required' => 'La marque est obligatoire.',
            'titre.required' => 'Le titre est obligatoire.',
            'prix.required' => 'Le prix est obligatoire.',
            'carburant.required' => 'Le type de carburant est obligatoire.',
            'boite_vitesse.required' => 'La boîte de vitesses est obligatoire.',
            'condition.required' => 'Veuillez indiquer si le véhicule est neuf.',
        ]);

        $data['show_phone'] = $request->boolean('show_phone');
        $data['condition'] = $request->input('condition', 'non');
        $data['vehicle_type'] = $request->input('vehicle_type', 'car'); // Voiture par défaut
        
        // Upload images
        $imagePaths = [
            'image_path'   => null,
            'image_path_2' => null,
            'image_path_3' => null,
            'image_path_4' => null,
            'image_path_5' => null,
        ];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                if ($index >= 5) break;
                
                $path = $file->store('annonces', 'public');
                
                if ($index === 0) $imagePaths['image_path']   = $path;
                if ($index === 1) $imagePaths['image_path_2'] = $path;
                if ($index === 2) $imagePaths['image_path_3'] = $path;
                if ($index === 3) $imagePaths['image_path_4'] = $path;
                if ($index === 4) $imagePaths['image_path_5'] = $path;
            }
        }

        $data = array_merge($data, $imagePaths);
        $data['user_id'] = $request->user()->id;
        $data['is_active'] = false; // Nécessite validation admin

        $annonce = Annonce::create($data);

        return response()->json([
            'message' => 'Annonce créée avec succès. Elle sera visible après validation.',
            'annonce' => $this->formatAnnonce($annonce, false),
        ], 201);
    }

    /**
     * Mes annonces
     * GET /api/my-annonces
     */
    public function myAnnonces(Request $request)
    {
        $annonces = Annonce::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $annonces->map(function($annonce) {
                return $this->formatAnnonce($annonce, false);
            }),
        ]);
    }

    /**
     * Supprimer une annonce
     * DELETE /api/annonces/{id}
     */
    public function destroy(Request $request, $id)
    {
        $annonce = Annonce::findOrFail($id);

        // Check ownership
        if ($annonce->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette annonce.',
            ], 403);
        }

        // Delete images from storage
        $imageFields = ['image_path', 'image_path_2', 'image_path_3', 'image_path_4', 'image_path_5'];
        foreach ($imageFields as $field) {
            if ($annonce->$field) {
                Storage::disk('public')->delete($annonce->$field);
            }
        }

        $annonce->delete();

        return response()->json([
            'message' => 'Annonce supprimée avec succès',
        ]);
    }

    /**
     * Annonces d'un utilisateur spécifique
     * GET /api/users/{id}/annonces
     */
    public function userAnnonces($id)
    {
        $query = Annonce::where('user_id', $id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        $annonces = $query->paginate(20);

        return response()->json([
            'data' => $annonces->getCollection()->map(function($annonce) {
                return $this->formatAnnonce($annonce, false);
            }),
            'current_page' => $annonces->currentPage(),
            'last_page' => $annonces->lastPage(),
            'per_page' => $annonces->perPage(),
            'total' => $annonces->total(),
        ]);
    }

    /**
     * Statistiques d'une annonce
     * GET /api/annonces/{id}/stats
     */
    public function stats($id)
    {
        $annonce = Annonce::with('user')->findOrFail($id);

        return response()->json([
            'id' => $annonce->id,
            'views' => (int) $annonce->views,
            'favorites' => $annonce->favorites()->count(),
            'messages' => $annonce->conversations()->count(),
            'isActive' => $annonce->is_active,
            'createdAt' => $annonce->created_at->toIso8601String(),
        ]);
    }

    /**
     * Incrémenter les vues d'une annonce
     * POST /api/annonces/{id}/view
     */
    public function incrementView(Request $request, $id)
    {
        $annonce = Annonce::findOrFail($id);

        // Ne pas compter les vues du propriétaire
        if (!$request->user() || $request->user()->id !== $annonce->user_id) {
            $annonce->increment('views');
        }

        return response()->json([
            'views' => (int) $annonce->views,
        ]);
    }

    /**
     * Modifier une annonce
     * PUT/POST /api/annonces/{id}
     */
    public function update(Request $request, $id)
    {
        $annonce = Annonce::findOrFail($id);

        // Check ownership
        if ($annonce->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cette annonce.',
            ], 403);
        }

        // Support both French and English field names (Flutter compatibility)
        $data = $request->validate([
            'titre'         => 'nullable|string|max:255',
            'title'         => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'prix'          => 'nullable|integer|min:0',
            'price'         => 'nullable|integer|min:0',
            'marque'        => 'nullable|string|max:100',
            'modele'        => 'nullable|string|max:100',
            'annee'         => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'year'          => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'kilometrage'   => 'nullable|integer|min:0',
            'km'            => 'nullable|integer|min:0',
            'carburant'     => 'nullable|string|max:50',
            'fuel'          => 'nullable|string|max:50',
            'boite_vitesse' => 'nullable|string|max:50',
            'gearbox'       => 'nullable|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'wilaya'        => 'nullable|string|max:100',
            'vehicle_type'  => 'nullable|string|max:50',
            'show_phone'    => 'nullable|boolean',
            'couleur'       => 'nullable|string|max:50',
            'color'         => 'nullable|string|max:50',
            'document_type' => 'nullable|in:carte_grise,procuration',
            'finition'      => 'nullable|string|max:80',
            'condition'     => 'nullable|in:oui,non,neuf,occasion',
            'images.*'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Map English to French field names
        $mappedData = [];
        if (isset($data['title'])) $mappedData['titre'] = $data['title'];
        if (isset($data['titre'])) $mappedData['titre'] = $data['titre'];
        if (isset($data['price'])) $mappedData['prix'] = $data['price'];
        if (isset($data['prix'])) $mappedData['prix'] = $data['prix'];
        if (isset($data['year'])) $mappedData['annee'] = $data['year'];
        if (isset($data['annee'])) $mappedData['annee'] = $data['annee'];
        if (isset($data['km'])) $mappedData['kilometrage'] = $data['km'];
        if (isset($data['kilometrage'])) $mappedData['kilometrage'] = $data['kilometrage'];
        if (isset($data['fuel'])) $mappedData['carburant'] = $data['fuel'];
        if (isset($data['carburant'])) $mappedData['carburant'] = $data['carburant'];
        if (isset($data['gearbox'])) $mappedData['boite_vitesse'] = $data['gearbox'];
        if (isset($data['boite_vitesse'])) $mappedData['boite_vitesse'] = $data['boite_vitesse'];
        if (isset($data['wilaya'])) $mappedData['ville'] = $data['wilaya'];
        if (isset($data['ville'])) $mappedData['ville'] = $data['ville'];
        if (isset($data['color'])) $mappedData['couleur'] = $data['color'];
        if (isset($data['couleur'])) $mappedData['couleur'] = $data['couleur'];
        if (isset($data['description'])) $mappedData['description'] = $data['description'];
        if (isset($data['marque'])) $mappedData['marque'] = $data['marque'];
        if (isset($data['modele'])) $mappedData['modele'] = $data['modele'];
        if (isset($data['vehicle_type'])) $mappedData['vehicle_type'] = $data['vehicle_type'];
        if (isset($data['document_type'])) $mappedData['document_type'] = $data['document_type'];
        if (isset($data['finition'])) $mappedData['finition'] = $data['finition'];
        if (isset($data['condition'])) $mappedData['condition'] = $data['condition'];
        
        // Handle show_phone
        if ($request->has('show_phone')) {
            $mappedData['show_phone'] = $request->boolean('show_phone');
        }

        // Upload new images if provided
        if ($request->hasFile('images')) {
            $imageFields = ['image_path', 'image_path_2', 'image_path_3', 'image_path_4', 'image_path_5'];
            
            foreach ($request->file('images') as $index => $file) {
                if ($index >= 5) break;
                
                // Delete old image if exists
                $fieldName = $index === 0 ? 'image_path' : 'image_path_' . ($index + 1);
                if ($annonce->$fieldName) {
                    Storage::disk('public')->delete($annonce->$fieldName);
                }
                
                // Upload new image
                $path = $file->store('annonces', 'public');
                $mappedData[$fieldName] = $path;
            }
        }

        $annonce->update($mappedData);

        return response()->json([
            'message' => 'Annonce modifiée avec succès',
            'annonce' => $this->formatAnnonce($annonce->fresh(), false),
        ]);
    }

    /**
     * Format annonce for API response
     */
    private function formatAnnonce($annonce, $isFavorite = false)
    {
        $images = [];
        $imageFields = ['image_path', 'image_path_2', 'image_path_3', 'image_path_4', 'image_path_5'];
        
        foreach ($imageFields as $field) {
            if ($annonce->$field) {
                $images[] = url('storage/' . $annonce->$field);
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
            'isFavorite' => $isFavorite,
            'isActive' => $annonce->is_active,
            'user' => [
                'id' => $annonce->user->id,
                'name' => $annonce->user->name,
                'phone' => $annonce->show_phone ? $annonce->user->phone : null,
                'avatar' => $annonce->user->avatar ? url('storage/' . $annonce->user->avatar) : null,
            ],
        ];
    }
}
