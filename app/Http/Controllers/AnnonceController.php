<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Jobs\ProcessAnnonceImages;
use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AnnonceController extends Controller
{
    public function index(Request $request)
    {
        $allowedVehicleTypes = ['Voiture', 'Moto'];
        $marques = CarBrand::orderBy('name')->get();
        $modeles = CarModel::orderBy('name')->get();

        $baseQuery = Annonce::query()
            ->where('is_active', true)
            ->whereIn('vehicle_type', $allowedVehicleTypes)
            ->latest();

        $filteredQuery = (clone $baseQuery)->filter($request->only([
            'marque',
            'modele',
            'price_max',
            'annee_min',
            'annee_max',
            'km_min',
            'km_max',
            'carburant',
            'wilaya',
            'vehicle_type',
        ]));

        $latestAds = (clone $filteredQuery)->take(6)->get();

        $topAnnonces = Annonce::with(['marque', 'modele'])
            ->where('is_active', true)
            ->whereIn('vehicle_type', $allowedVehicleTypes)
            ->orderBy('views', 'desc')
            ->take(3)
            ->get();

        $popularMarques = Annonce::select(
                DB::raw('marque as name'),
                DB::raw('COUNT(*) as annonces_count')
            )
            ->where('is_active', true)
            ->whereNotNull('marque')
            ->whereIn('vehicle_type', $allowedVehicleTypes)
            ->groupBy('marque')
            ->orderByDesc('annonces_count')
            ->take(8)
            ->get();

        $popularModeles = Annonce::select(
                DB::raw('modele as name'),
                DB::raw('COUNT(*) as annonces_count')
            )
            ->where('is_active', true)
            ->whereNotNull('modele')
            ->whereIn('vehicle_type', $allowedVehicleTypes)
            ->groupBy('modele')
            ->orderByDesc('annonces_count')
            ->take(8)
            ->get();

        return view('home', compact(
            'marques',
            'modeles',
            'latestAds',
            'topAnnonces',
            'popularMarques',
            'popularModeles'
        ));
    }

    public function create()
    {
        // Clean up any existing temp images from previous attempts
        $this->cleanTempImages();
        
        // Build brand → models maps from DB (split by vehicle_type)
        $carBrandsMap = [];
        $motoBrandsMap = [];

        CarBrand::with(['models' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->each(function (CarBrand $brand) use (&$carBrandsMap, &$motoBrandsMap) {
                $models = $brand->models->pluck('name')->filter()->unique()->sort()->values()->all();
                if ($brand->vehicle_type === 'Moto') {
                    $motoBrandsMap[$brand->name] = $models;
                } else {
                    $carBrandsMap[$brand->name] = $models;
                }
            });

        // Enrich with static car models for Algeria
        $staticCarModels = [
            'Renault' => ['Clio', 'Megane', 'Symbol', 'Logan', 'Sandero', 'Duster', 'Captur', 'Kadjar', 'Talisman', 'Twingo', 'Kangoo'],
            'Peugeot' => ['208', '308', '2008', '3008', '301', '508', '407', '206', '307', '5008', 'Partner', 'Expert'],
            'Citroën' => ['C3', 'C4', 'C5', 'Berlingo', 'Jumpy', 'C-Elysée', 'C3 Aircross', 'C4 Cactus', 'Jumper'],
            'Dacia' => ['Sandero', 'Logan', 'Duster', 'Lodgy', 'Dokker', 'Spring'],
            'Volkswagen' => ['Golf', 'Polo', 'Passat', 'Tiguan', 'Jetta', 'Touareg', 'Caddy', 'T-Cross', 'T-Roc', 'Arteon'],
            'Hyundai' => ['i10', 'i20', 'Accent', 'Elantra', 'Tucson', 'Santa Fe', 'Kona', 'i30', 'Creta'],
            'Kia' => ['Picanto', 'Rio', 'Cerato', 'Sportage', 'Sorento', 'Optima', 'Stonic', 'Seltos', 'Carens'],
            'Toyota' => ['Corolla', 'Yaris', 'Hilux', 'Land Cruiser', 'RAV4', 'Camry', 'Prado', 'Fortuner', 'Avensis', 'Auris'],
            'Nissan' => ['Micra', 'Qashqai', 'X-Trail', 'Juke', 'Navara', 'Note', 'Patrol', 'Sentra', 'Altima'],
            'Ford' => ['Fiesta', 'Focus', 'Mondeo', 'Kuga', 'Ranger', 'EcoSport', 'Puma', 'Mustang', 'Transit'],
            'Opel' => ['Corsa', 'Astra', 'Insignia', 'Mokka', 'Grandland', 'Crossland', 'Combo', 'Vivaro'],
            'Seat' => ['Ibiza', 'Leon', 'Arona', 'Ateca', 'Tarraco', 'Toledo'],
            'Skoda' => ['Fabia', 'Octavia', 'Superb', 'Kodiaq', 'Karoq', 'Scala', 'Kamiq'],
            'Fiat' => ['500', 'Panda', 'Punto', 'Tipo', 'Doblo', '500X', 'Fiorino', 'Ducato'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'Jazz', 'HR-V', 'City', 'Fit'],
            'Mazda' => ['2', '3', '6', 'CX-3', 'CX-5', 'CX-30', 'MX-5'],
            'Mitsubishi' => ['Pajero', 'L200', 'ASX', 'Outlander', 'Lancer', 'Eclipse Cross'],
            'Suzuki' => ['Swift', 'Vitara', 'Baleno', 'Jimny', 'S-Cross', 'Celerio', 'Alto', 'Dzire'],
            'Isuzu' => ['D-Max', 'MU-X', 'Trooper'],
            'Chevrolet' => ['Spark', 'Aveo', 'Cruze', 'Malibu', 'Captiva', 'Trax', 'Tahoe', 'Silverado'],
            'Jeep' => ['Renegade', 'Compass', 'Cherokee', 'Grand Cherokee', 'Wrangler'],
            'Mercedes-Benz' => ['Classe A', 'Classe B', 'Classe C', 'Classe E', 'Classe S', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'CLA', 'CLS', 'Vito', 'Sprinter'],
            'BMW' => ['Série 1', 'Série 2', 'Série 3', 'Série 4', 'Série 5', 'Série 7', 'X1', 'X3', 'X5', 'X6', 'X7', 'Z4'],
            'Audi' => ['A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'TT'],
        ];

        foreach ($staticCarModels as $brand => $models) {
            $existing = $carBrandsMap[$brand] ?? [];
            $carBrandsMap[$brand] = array_values(array_unique(array_merge($existing, $models)));
            sort($carBrandsMap[$brand], SORT_NATURAL | SORT_FLAG_CASE);
        }

        // Combined map for JS
        $brandModelsMap = array_merge($carBrandsMap, $motoBrandsMap);
        
        $carBrandsList = array_keys($carBrandsMap);
        sort($carBrandsList, SORT_NATURAL | SORT_FLAG_CASE);
        
        $motoBrandsList = array_keys($motoBrandsMap);
        sort($motoBrandsList, SORT_NATURAL | SORT_FLAG_CASE);
        
        $brands = collect($carBrandsList);
        
        // Official list of Algerian Wilayas
        $wilayas = [
            'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar', 'Blida', 'Bouira',
            'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Alger', 'Djelfa', 'Jijel', 'Sétif', 'Saïda',
            'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma', 'Constantine', 'Médéa', 'Mostaganem', 'M\'Sila', 'Mascara', 'Ouargla',
            'Oran', 'El Bayadh', 'Illizi', 'Bordj Bou Arreridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt', 'El Oued', 'Khenchela',
            'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent', 'Ghardaïa', 'Relizane', 'Timimoun', 'Bordj Badji Mokhtar',
            'Ouled Djellal', 'Béni Abbès', 'In Salah', 'In Guezzam', 'Touggourt', 'Djanet', 'El M\'Ghair', 'El Meniaa'
        ];
        
        $models = CarModel::orderBy('name')->get();
        
        // Récupérer les features de l'utilisateur pour le formulaire
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $features = $subscriptionService->getFeatures(auth()->user());
        $isPro = $subscriptionService->userIsPro(auth()->user());
        $maxImagesIntro = $features['max_images_per_ad'] ?? 4;

        return view('annonces.create', compact(
            'brands', 'models', 'wilayas', 'brandModelsMap',
            'isPro', 'maxImagesIntro',
            'carBrandsMap', 'motoBrandsMap', 'motoBrandsList'
        ));
    }

    public function cleanTempImages()
    {
        $tempImages = session('temp_images', []);
        
        foreach ($tempImages as $tempPath) {
            if (Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->delete($tempPath);
            }
        }
        
        session()->forget('temp_images');
        
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        // ✅ QUOTA CHECK: Vérifier la limite d'annonces (TOUTES, pas seulement actives)
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $features = $subscriptionService->getFeatures(auth()->user());
        $maxAds = $features['max_active_ads'];
        $maxImages = $features['max_images_per_ad'] ?? 4;
        $activeCount = auth()->user()->annonces()->count();
        
        if ($activeCount >= $maxAds) {
            return back()->withErrors([
                'quota' => "Vous avez atteint votre limite de {$maxAds} annonces actives. " . 
                           ($maxAds === 5 ? "Passez à PRO pour publier jusqu'à 10 annonces !" : "")
            ])->withInput();
        }

        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'prix'          => 'required|integer|min:0',

            'marque'        => 'required|string|max:100',
            'modele'        => 'nullable|string|max:100',
            'annee'         => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'kilometrage'   => 'nullable|integer|min:0',
            'carburant'     => 'required|string|max:50',
            'boite_vitesse' => 'required_if:vehicle_type,Voiture|nullable|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'vehicle_type'  => 'required|in:Voiture,Moto',

            'show_phone'    => ['nullable', 'boolean'],
            'couleur'       => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:carte_grise,procuration'],
            'finition'      => ['nullable', 'string', 'max:80'],

            // ✅ Véhicule neuf ? oui/non
            'condition'     => ['required', 'in:oui,non'],
            'seller_type'   => ['nullable', 'in:particulier,pro'],
        ], [
            'marque.required' => 'La marque est obligatoire.',
            'titre.required' => 'Le titre est obligatoire.',
            'prix.required' => 'Le prix est obligatoire.',
            'carburant.required' => 'Le type de carburant est obligatoire.',
            'boite_vitesse.required' => 'La boîte de vitesses est obligatoire.',
            'vehicle_type.required' => 'Le type de véhicule est obligatoire.',
            'condition.required' => 'Veuillez indiquer si le véhicule est neuf.',
        ]);

        $data['show_phone'] = $request->boolean('show_phone');
        $data['condition']  = $request->input('condition', 'non');
        $data['seller_type'] = $request->input('seller_type', 'particulier');
        
        // Si Moto et boite_vitesse vide, mettre N/A par défaut
        if ($data['vehicle_type'] === 'Moto' && empty($data['boite_vitesse'])) {
            $data['boite_vitesse'] = 'N/A';
        }

        // Vérifier que l'utilisateur a un numéro de téléphone si show_phone est activé
        if ($data['show_phone'] && empty(auth()->user()->phone)) {
            return back()->withErrors([
                'show_phone' => 'Vous devez ajouter un numéro de téléphone dans votre profil avant de pouvoir l\'afficher dans vos annonces.'
            ])->withInput();
        }

        // Upload rapide (sans traitement) puis traitement async après réponse
        $imagePaths = [
            'image_path'   => null,
            'image_path_2' => null,
            'image_path_3' => null,
            'image_path_4' => null,
            'image_path_5' => null,
            'image_path_6' => null,
            'image_path_7' => null,
            'image_path_8' => null,
        ];

        if ($request->hasFile('images')) {
            $disk = config('filesystems.default', 'public');
            $imageCount = 0;
            
            \Log::info('🔍 DEBUT UPLOAD IMAGES', [
                'maxImages' => $maxImages,
                'files_count' => count($request->file('images')),
                'user_id' => auth()->id(),
                'plan' => auth()->user()->subscriptions()->latest()->first()?->plan?->name ?? 'Free',
            ]);
            
            // Charger watermark une fois pour toutes les images (performance)
            $watermarkOpacity = config('app.watermark_opacity', 0.20);
            $watermarkWidth = config('app.watermark_width', 0.65);
            $fontPath = null;
            $possibleFonts = [
                'C:/Windows/Fonts/arialbd.ttf',
                'C:/Windows/Fonts/arial.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            ];
            foreach ($possibleFonts as $font) {
                if (file_exists($font)) {
                    $fontPath = $font;
                    break;
                }
            }
            
            foreach ($request->file('images') as $index => $file) {
                \Log::info('🔍 ITERATION FOREACH', [
                    'index' => $index,
                    'imageCount' => $imageCount,
                    'maxImages' => $maxImages,
                    'will_break' => $imageCount >= $maxImages,
                ]);
                
                // Limiter selon le maxImages
                if ($imageCount >= $maxImages) {
                    \Log::info('⚠️ BREAK: imageCount >= maxImages', [
                        'imageCount' => $imageCount,
                        'maxImages' => $maxImages,
                    ]);
                    break;
                }

                try {
                    // Traiter l'image inline avec watermark (pas async)
                    $filename = 'annonces/' . Str::uuid() . '.jpg';
                    $image = Image::make($file->getRealPath())->orientate();

                    // Redimensionner
                    $image->resize(1280, null, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    });

                    // Appliquer watermark
                    $targetTextWidth = $image->width() * $watermarkWidth;
                    $fontSize = (int) ($targetTextWidth / 4.8);
                    
                    $image->text('ELSAYARA', $image->width() / 2, $image->height() / 2, function($font) use ($fontSize, $watermarkOpacity, $fontPath) {
                        if ($fontPath) {
                            $font->file($fontPath);
                        }
                        $font->size($fontSize);
                        $font->color([255, 255, 255, $watermarkOpacity]);
                        $font->align('center');
                        $font->valign('middle');
                    });

                    // Sauvegarder
                    Storage::disk($disk)->put($filename, (string) $image->encode('jpg', 95));
                    
                    \Log::info('✅ Image traitée inline', [
                        'index' => $index,
                        'filename' => $filename,
                        'width' => $image->width(),
                        'height' => $image->height(),
                    ]);
                    
                    if ($index === 0) $imagePaths['image_path']   = $filename;
                    if ($index === 1) $imagePaths['image_path_2'] = $filename;
                    if ($index === 2) $imagePaths['image_path_3'] = $filename;
                    if ($index === 3) $imagePaths['image_path_4'] = $filename;
                    if ($index === 4) $imagePaths['image_path_5'] = $filename;
                    if ($index === 5) $imagePaths['image_path_6'] = $filename;
                    if ($index === 6) $imagePaths['image_path_7'] = $filename;
                    if ($index === 7) $imagePaths['image_path_8'] = $filename;
                    
                    $imageCount++;
                } catch (\Exception $e) {
                    \Log::error("Image upload exception: " . $e->getMessage());
                }
            }
        }

        $data = array_merge($data, $imagePaths);
        $data['user_id'] = auth()->id() ?? 1;
        $data['is_active'] = false; // ✅ En attente de validation admin par défaut

        $annonce = Annonce::create($data);

        return redirect()
            ->route('annonces.show', ['annonce' => $annonce->id, 'slug' => $annonce->slug])
            ->with('success', 'Annonce créée avec succès.');
    }

    /**
     * Display an annonce with SEO-friendly URL.
     * Redirects to canonical URL if slug is missing or doesn't match.
     */
    public function show(Request $request, Annonce $annonce, ?string $slug = null)
{
    // SEO: Redirect to canonical URL with slug if missing or wrong
    $expectedSlug = $annonce->slug ?: Str::slug($annonce->titre);
    if ($slug !== $expectedSlug) {
        return redirect()->route('annonces.show', [
            'annonce' => $annonce->id,
            'slug' => $expectedSlug
        ], 301);
    }

    $isOwner = auth()->check() && auth()->id() === $annonce->user_id;
    $isAdmin = auth()->check() && auth()->user()->is_admin;

    if (!$annonce->is_active && !($isOwner || $isAdmin)) {
        abort(404);
    }

    // ✅ 1 vue max par session (pas owner/admin)
    if (!$isOwner && !$isAdmin) {
        $key = 'viewed_annonce_' . $annonce->id;
        if (!session()->has($key)) {
            $annonce->increment('views');
            session()->put($key, true);
        }
    }

    $annonce->load('user');

    // ✅ Images : 8 slots fixes (filtre null)
    $disk = config('filesystems.default', 'public');
    $images = collect([
        $annonce->image_path,
        $annonce->image_path_2,
        $annonce->image_path_3,
        $annonce->image_path_4,
        $annonce->image_path_5,
        $annonce->image_path_6,
        $annonce->image_path_7,
        $annonce->image_path_8,
    ])->filter()->values()
      ->map(function ($path) use ($disk) {
          $path = ltrim($path, '/');
          $path = preg_replace('#^storage/#', '', $path); // évite storage/storage
          
          // Si on utilise S3 ou autre cloud storage
          if ($disk !== 'public' && $disk !== 'local') {
              return Storage::disk($disk)->url($path);
          }
          
          // Sinon, utiliser le storage local
          return asset('storage/' . $path);
      })->values();

    // ✅ fallback si aucune image en storage
    if ($images->isEmpty()) {
        if (!empty($annonce->image_url)) {
            $images = collect([$annonce->image_url]);
        } else {
            $images = collect([asset('images/placeholder-car.jpg')]);
        }
    }

    // ✅ Annonces similaires (même marque)
    $similarAds = Annonce::where('id', '!=', $annonce->id)
        ->where('is_active', true)
        ->where('marque', $annonce->marque)
        ->latest()
        ->take(4)
        ->get();

    // ✅ IMPORTANT : on passe TOUT ce que la vue utilise
    return view('annonces.show', compact('annonce', 'images', 'similarAds'));
}


    public function search(Request $request)
    {
        $allowedVehicleTypes = ['Voiture', 'Moto'];
        // Enregistrer l'historique de recherche si l'utilisateur est connecté
        if (auth()->check() && $request->hasAny(['marque', 'modele', 'price_max', 'annee_min', 'annee_max', 'km_min', 'km_max', 'carburant', 'wilaya', 'vehicle_type'])) {
            \App\Models\SearchHistory::create([
                'user_id' => auth()->id(),
                'marque' => $request->input('marque'),
                'modele' => $request->input('modele'),
                'price_max' => $request->input('price_max'),
                'annee_min' => $request->input('annee_min'),
                'annee_max' => $request->input('annee_max'),
                'km_min' => $request->input('km_min'),
                'km_max' => $request->input('km_max'),
                'carburant' => $request->input('carburant'),
                'wilaya' => $request->input('wilaya'),
                'vehicle_type' => $request->input('vehicle_type'),
            ]);
        }

        $query = Annonce::query()
            ->where('is_active', true)
            ->whereIn('vehicle_type', $allowedVehicleTypes);

        $type = $request->input('vehicle_type');
        if ($type && $type !== 'any' && in_array($type, $allowedVehicleTypes, true)) {
            $query->where('vehicle_type', $type);
        }

        if ($marque = $request->input('marque')) {
            $query->where('marque', 'like', '%' . $marque . '%');
        }

        if ($modele = $request->input('modele')) {
            $query->where('modele', 'like', '%' . $modele . '%');
        }

        if ($anneeMin = $request->input('annee_min')) {
            $query->where('annee', '>=', (int) $anneeMin);
        }
        if ($anneeMax = $request->input('annee_max')) {
            $query->where('annee', '<=', (int) $anneeMax);
        }

        if ($kmMin = $request->input('km_min')) {
            $query->where('kilometrage', '>=', (int) $kmMin);
        }
        if ($kmMax = $request->input('km_max')) {
            $query->where('kilometrage', '<=', (int) $kmMax);
        }

        $carb = $request->input('carburant', 'any');
        if ($carb !== 'any') {
            $query->where('carburant', $carb);
        }

        if ($gear = $request->input('boite_vitesse')) {
            $query->where('boite_vitesse', $gear);
        }

        if ($wilaya = $request->input('wilaya')) {
            $query->where('ville', 'like', '%' . $wilaya . '%');
        }

        if ($priceMax = $request->input('price_max')) {
            $query->where('prix', '<=', (int) $priceMax);
        }

        $keyword = trim((string) $request->input('q', ''));
        if ($keyword === '') {
            $keyword = trim((string) $request->input('home_q', ''));
        }

        if ($keyword !== '') {
            $query->where(function ($qb) use ($keyword) {
                $qb->where('titre', 'like', '%' . $keyword . '%')
                   ->orWhere('marque', 'like', '%' . $keyword . '%');
            });
        }

        // ✅ TRI BOOST: Les annonces boostées remontent en premier
        $query->leftJoin('boosts', function ($join) {
            $join->on('annonces.id', '=', 'boosts.annonce_id')
                 ->where('boosts.status', '=', 'active')
                 ->where('boosts.expires_at', '>', now());
        })
        ->orderByRaw('CASE WHEN boosts.id IS NOT NULL THEN 0 ELSE 1 END');

        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':  $query->orderBy('annonces.prix', 'asc'); break;
            case 'price_desc': $query->orderBy('annonces.prix', 'desc'); break;
            case 'km_asc':     $query->orderBy('annonces.kilometrage', 'asc'); break;
            case 'km_desc':    $query->orderBy('annonces.kilometrage', 'desc'); break;
            case 'year_asc':   $query->orderBy('annonces.annee', 'asc'); break;
            case 'year_desc':  $query->orderBy('annonces.annee', 'desc'); break;
            case 'latest':
            default:           $query->orderBy('annonces.created_at', 'desc'); break;
        }

        $annonces = $query->select([
                'annonces.id','annonces.titre','annonces.prix','annonces.marque','annonces.modele','annonces.annee',
                'annonces.kilometrage','annonces.carburant','annonces.boite_vitesse','annonces.ville',
                'annonces.image_path','annonces.views','annonces.created_at','annonces.condition'
            ])
            ->paginate(15)
            ->withQueryString();

        $filters = $request->only([
            'q','marque','modele','wilaya','carburant','price_max',
            'vehicle_type','boite_vitesse','annee_min','annee_max',
            'km_min','km_max','sort',
        ]);

        return view('annonces.search', compact('annonces', 'filters'));
    }

    public function myAds()
    {
        $annonces = Annonce::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('annonces.my', compact('annonces'));
    }

    public function update(Request $request, Annonce $annonce)
    {
        if ($annonce->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez modifier que vos propres annonces.');
        }

        // Déterminer le nombre max d'images selon les features de l'abonnement
        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $features = $subscriptionService->getFeatures(auth()->user());
        $maxImages = $features['max_images_per_ad'] ?? 4; // 4 par défaut pour compte gratuit

        // Nettoyer fichiers vides
        if ($request->hasFile('images')) {
            $request->merge([
                'images' => array_values(array_filter($request->file('images'), function ($file) {
                    return $file && $file->getSize() > 0;
                }))
            ]);
        }

        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'prix'          => 'required|integer|min:0',

            'marque'        => 'required|string|max:100',
            'modele'        => 'nullable|string|max:100',
            'annee'         => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'kilometrage'   => 'nullable|integer|min:0',
            'carburant'     => 'required|string|max:50',
            'boite_vitesse' => 'required_if:vehicle_type,Voiture|nullable|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'vehicle_type'  => 'nullable|in:Voiture,Moto',

            'show_phone'    => 'nullable|boolean',
            'couleur'       => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:carte_grise,procuration'],
            'finition'      => ['nullable', 'string', 'max:80'],

            // ✅ Véhicule neuf ? oui/non
            'condition'     => ['required', 'in:oui,non'],
            'seller_type'   => ['nullable', 'in:particulier,pro'],

            // ✅ suppression images existantes : delete_images[slot] = 0/1
            'delete_images'   => 'nullable|array',
            'delete_images.*' => 'in:0,1',

            'images'        => "nullable|array|max:{$maxImages}",
            'images.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'images.max' => "Vous pouvez uploader maximum {$maxImages} images selon votre plan.",
        ]);

        $data['show_phone'] = $request->boolean('show_phone');
        $data['condition']  = $request->input('condition', $annonce->condition ?? 'non');
        
        // Si Moto et boite_vitesse vide, mettre N/A par défaut
        if (($data['vehicle_type'] ?? $annonce->vehicle_type) === 'Moto' && empty($data['boite_vitesse'])) {
            $data['boite_vitesse'] = 'N/A';
        }

        $slots = ['image_path','image_path_2','image_path_3','image_path_4','image_path_5','image_path_6','image_path_7','image_path_8'];

        // 1) Suppression demandée (delete_images[slot]=1)
        $deleteMap = $request->input('delete_images', []);
        foreach ($deleteMap as $slot => $flag) {
            if ($flag === '1' && in_array($slot, $slots, true) && !empty($annonce->$slot)) {
                Storage::disk('public')->delete($annonce->$slot);
                $data[$slot] = null;
            }
        }

        // 2) ajout nouvelles images (sans dépasser la limite)
        if ($request->hasFile('images')) {

            // compter images restantes après suppressions
            $current = 0;
            foreach ($slots as $slot) {
                $willBeDeleted = array_key_exists($slot, $data) && $data[$slot] === null;
                if (!empty($annonce->$slot) && !$willBeDeleted) $current++;
            }

            $incoming = count($request->file('images'));
            if (($current + $incoming) > $maxImages) {
                return back()->withErrors(['images' => "Max {$maxImages} images au total."])->withInput();
            }

            $stored = [];
            foreach ($request->file('images') as $file) {
                $filename = 'annonces/' . Str::uuid() . '.jpg';
                $image = Image::make($file->getRealPath())->orientate();

                // Alléger le poids pour accélérer l'upload
                $image->resize(1280, null, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });

                // Ajouter le watermark texte "ELSAYARA" (style LaCentrale)
                // Config: config/app.php - watermark_opacity (défaut: 0.20 = 20%)
                //                        - watermark_width (défaut: 0.65 = 65%)
                $watermarkOpacity = config('app.watermark_opacity', 0.20);
                $watermarkWidth = config('app.watermark_width', 0.65);
                
                // Calculer la taille de police pour que le texte fasse 65% de la largeur
                // Pour "ELSAYARA" (8 chars), ratio approximatif: fontSize = targetWidth / 4.8
                $targetTextWidth = $image->width() * $watermarkWidth;
                $fontSize = (int) ($targetTextWidth / 4.8);
                
                // Utiliser Arial Bold (système Windows/Linux)
                $fontPath = null;
                $possibleFonts = [
                    'C:/Windows/Fonts/arialbd.ttf',  // Windows Arial Bold
                    'C:/Windows/Fonts/arial.ttf',    // Windows Arial Regular
                    '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',  // Linux Liberation Sans Bold
                    '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',         // Linux DejaVu Sans Bold
                ];
                
                foreach ($possibleFonts as $font) {
                    if (file_exists($font)) {
                        $fontPath = $font;
                        break;
                    }
                }
                
                $image->text('ELSAYARA', $image->width() / 2, $image->height() / 2, function($font) use ($fontSize, $watermarkOpacity, $fontPath) {
                    if ($fontPath) {
                        $font->file($fontPath);
                    }
                    $font->size($fontSize);
                    $font->color([255, 255, 255, $watermarkOpacity]); // Blanc avec opacité configurable
                    $font->align('center');
                    $font->valign('middle');
                });

                Storage::disk('public')->put($filename, (string) $image->encode('jpg', 70));
                $stored[] = $filename;
            }

            // assigner aux slots vides (ou supprimés)
            foreach ($slots as $slot) {
                $slotEmptyNow = empty($annonce->$slot) || (array_key_exists($slot, $data) && $data[$slot] === null);
                if ($slotEmptyNow && !empty($stored)) {
                    $data[$slot] = array_shift($stored);
                }
            }
        }

        $annonce->update($data);

        return redirect()
            ->route('annonces.my')
            ->with('success', 'Annonce mise à jour avec succès.');
    }

    public function destroy(Annonce $annonce)
    {
        if ($annonce->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez supprimer que vos propres annonces.');
        }

        // Store deletion info with sale status
        \App\Models\AnnonceDeletion::create([
            'annonce_id' => $annonce->id,
            'user_id' => $annonce->user_id,
            'titre' => $annonce->titre,
            'prix' => $annonce->prix,
            'was_sold' => request()->has('was_sold') && request('was_sold') === 'oui',
        ]);

        $images = [
            $annonce->image_path,
            $annonce->image_path_2,
            $annonce->image_path_3,
            $annonce->image_path_4,
            $annonce->image_path_5,
            $annonce->image_path_6,
            $annonce->image_path_7,
            $annonce->image_path_8,
        ];

        foreach ($images as $path) {
            if (!empty($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $annonce->delete();

        return redirect()
            ->route('annonces.my')
            ->with('success', 'Annonce supprimée avec succès.');
    }

    public function edit(Annonce $annonce)
    {
        // Autoriser l'édition au propriétaire ou à l'admin
        $isOwner = Auth::check() && $annonce->user_id === Auth::id();
        $isAdmin = Auth::check() && optional(Auth::user())->is_admin;

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Accès refusé : vous ne pouvez modifier que vos propres annonces.');
        }

        $brands = CarBrand::orderBy('name')->get();
        $models = CarModel::orderBy('name')->get();

        $subscriptionService = app(\App\Services\SubscriptionService::class);
        $isPro = $subscriptionService->userIsPro(Auth::user());

        return view('annonces.edit', compact('annonce', 'brands', 'models', 'isPro'));
    }

    public function getModels(Request $request)
    {
        $brand = $request->query('brand');
        if (!$brand) return response()->json([]);

        $models = CarModel::whereHas('brand', function ($q) use ($brand) {
                $q->where('name', $brand);
            })
            ->orderBy('name')
            ->get(['name']);

        return response()->json($models->pluck('name'));
    }
}
