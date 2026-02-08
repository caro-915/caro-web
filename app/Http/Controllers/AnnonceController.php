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
        $marques = CarBrand::orderBy('name')->get();
        $modeles = CarModel::orderBy('name')->get();

        $baseQuery = Annonce::query()
            ->where('is_active', true)
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
            ->orderBy('views', 'desc')
            ->take(3)
            ->get();

        $popularMarques = Annonce::select(
                DB::raw('marque as name'),
                DB::raw('COUNT(*) as annonces_count')
            )
            ->where('is_active', true)
            ->whereNotNull('marque')
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
        
        // Full list of car brands
        $brandsList = [
            'Abarth', 'Acura', 'Aiways', 'Alfa Romeo', 'Alpine', 'Aston Martin', 'Audi',
            'BAIC', 'Bentley', 'BMW', 'Borgward', 'BRP (Can-Am, etc.)', 'Buick', 'BYD',
            'Cadillac', 'Changan', 'Changhe', 'Chevrolet', 'Chrysler', 'Citroën', 'Cupra', 'Chery', 'CFMoto',
            'Dacia', 'Daihatsu', 'Dodge', 'DS', 'Denza',
            'Ferrari', 'Fiat', 'Ford',
            'Genesis', 'GMC', 'Great Wall Motors', 'GAC',
            'Honda', 'Hummer', 'Hyundai', 'Hongqi',
            'Infiniti', 'Isuzu', 'Ineos',
            'Jaguar', 'Jeep', 'JMC',
            'Kia', 'Koenigsegg',
            'Lada', 'Lamborghini', 'Land Rover', 'Lexus', 'Lucid', 'Lotus',
            'Maserati', 'Mazda', 'McLaren', 'Mercedes-Benz', 'Mini', 'Mitsubishi', 'MG Motor', 'Maxus',
            'Nissan', 'Nio',
            'Opel',
            'Peugeot', 'Porsche', 'Polestar',
            'Renault', 'Rivian', 'Rolls-Royce',
            'Saab', 'SEAT', 'Skoda', 'Smart', 'SsangYong', 'Subaru', 'Suzuki',
            'Tata Motors', 'Tesla', 'Toyota',
            'VinFast', 'Vauxhall', 'Volkswagen', 'Volvo',
            'Wuling', 'Wey',
            'Zeekr', 'Zotye'
        ];
        
        // Official list of Algerian Wilayas
        $wilayas = [
            'Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Béjaïa', 'Biskra', 'Béchar', 'Blida', 'Bouira',
            'Tamanrasset', 'Tébessa', 'Tlemcen', 'Tiaret', 'Tizi Ouzou', 'Alger', 'Djelfa', 'Jijel', 'Sétif', 'Saïda',
            'Skikda', 'Sidi Bel Abbès', 'Annaba', 'Guelma', 'Constantine', 'Médéa', 'Mostaganem', 'M\'Sila', 'Mascara', 'Ouargla',
            'Oran', 'El Bayadh', 'Illizi', 'Bordj Bou Arreridj', 'Boumerdès', 'El Tarf', 'Tindouf', 'Tissemsilt', 'El Oued', 'Khenchela',
            'Souk Ahras', 'Tipaza', 'Mila', 'Aïn Defla', 'Naâma', 'Aïn Témouchent', 'Ghardaïa', 'Relizane', 'Timimoun', 'Bordj Badji Mokhtar',
            'Ouled Djellal', 'Béni Abbès', 'In Salah', 'In Guezzam', 'Touggourt', 'Djanet', 'El M\'Ghair', 'El Meniaa'
        ];
        
        $brands = collect($brandsList);
        $models = CarModel::orderBy('name')->get();

        return view('annonces.create', compact('brands', 'models', 'wilayas'));
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
            'vehicle_type'  => 'required|string|max:50',

            'show_phone'    => ['nullable', 'boolean'],
            'couleur'       => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:carte_grise,procuration'],
            'finition'      => ['nullable', 'string', 'max:80'],

            // ✅ Véhicule neuf ? oui/non
            'condition'     => ['required', 'in:oui,non'],
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

        // Upload rapide (sans traitement) puis traitement async après réponse
        $imagePaths = [
            'image_path'   => null,
            'image_path_2' => null,
            'image_path_3' => null,
            'image_path_4' => null,
            'image_path_5' => null,
        ];

        $uploadedFiles = [];
        if ($request->hasFile('images')) {
            $disk = env('FILESYSTEM_DISK', 's3');
            
            foreach ($request->file('images') as $index => $file) {
                if ($index >= 5) break;

                try {
                    $path = $file->store('annonces', $disk);
                    
                    if (!$path) {
                        \Log::error("Image upload failed for slot $index");
                        continue;
                    }
                    
                    $uploadedFiles[] = $path;
                    
                    if ($index === 0) $imagePaths['image_path']   = $path;
                    if ($index === 1) $imagePaths['image_path_2'] = $path;
                    if ($index === 2) $imagePaths['image_path_3'] = $path;
                    if ($index === 3) $imagePaths['image_path_4'] = $path;
                    if ($index === 4) $imagePaths['image_path_5'] = $path;
                } catch (\Exception $e) {
                    \Log::error("Image upload exception: " . $e->getMessage());
                }
            }
        }

        $data = array_merge($data, $imagePaths);
        $data['user_id'] = auth()->id() ?? 1;
        $data['is_active'] = false; // ✅ En attente de validation admin par défaut

        $annonce = Annonce::create($data);

        // ✅ Dispatcher le traitement des images (redimensionne 1280px, watermark, qualité 95%)
        if (!empty($uploadedFiles)) {
            ProcessAnnonceImages::dispatch($uploadedFiles)->afterResponse();
        }

        return redirect()
            ->route('annonces.show', $annonce->id)
            ->with('success', 'Annonce créée avec succès.');
    }

    public function show(Annonce $annonce)
{
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

    // ✅ Images : 5 slots fixes (filtre null)
    $disk = env('FILESYSTEM_DISK', 's3');
    $images = collect([
        $annonce->image_path,
        $annonce->image_path_2,
        $annonce->image_path_3,
        $annonce->image_path_4,
        $annonce->image_path_5,
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

        $query = Annonce::query()->where('is_active', true);

        $type = $request->input('vehicle_type');
        if ($type && $type !== 'any') {
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

        if ($q = $request->input('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('titre', 'like', '%' . $q . '%')
                   ->orWhere('marque', 'like', '%' . $q . '%');
            });
        }

        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':  $query->orderBy('prix', 'asc'); break;
            case 'price_desc': $query->orderBy('prix', 'desc'); break;
            case 'km_asc':     $query->orderBy('kilometrage', 'asc'); break;
            case 'km_desc':    $query->orderBy('kilometrage', 'desc'); break;
            case 'year_asc':   $query->orderBy('annee', 'asc'); break;
            case 'year_desc':  $query->orderBy('annee', 'desc'); break;
            case 'latest':
            default:           $query->orderBy('created_at', 'desc'); break;
        }

        $annonces = $query->select([
                'id','titre','prix','marque','modele','annee','kilometrage','carburant','boite_vitesse','ville',
                'image_path','views','created_at','condition'
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
            'boite_vitesse' => 'required|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'vehicle_type'  => 'nullable|string|max:50',

            'show_phone'    => 'nullable|boolean',
            'couleur'       => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:carte_grise,procuration'],
            'finition'      => ['nullable', 'string', 'max:80'],

            // ✅ Véhicule neuf ? oui/non
            'condition'     => ['required', 'in:oui,non'],

            // ✅ suppression images existantes : delete_images[slot] = 0/1
            'delete_images'   => 'nullable|array',
            'delete_images.*' => 'in:0,1',

            'images'        => 'nullable|array|max:5',
            'images.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data['show_phone'] = $request->boolean('show_phone');
        $data['condition']  = $request->input('condition', $annonce->condition ?? 'non');

        $slots = ['image_path','image_path_2','image_path_3','image_path_4','image_path_5'];
        $maxImages = 5;

        // 1) Suppression demandée (delete_images[slot]=1)
        $deleteMap = $request->input('delete_images', []);
        foreach ($deleteMap as $slot => $flag) {
            if ($flag === '1' && in_array($slot, $slots, true) && !empty($annonce->$slot)) {
                Storage::disk('public')->delete($annonce->$slot);
                $data[$slot] = null;
            }
        }

        // 2) watermark chargé une seule fois
        $watermarkBase = null;
        $watermarkPath = public_path('watermark.png');
        if (file_exists($watermarkPath)) {
            $watermarkBase = Image::make($watermarkPath)->opacity(45);
        }

        // 3) ajout nouvelles images (sans dépasser 5 total)
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

                if ($watermarkBase) {
                    $wm = clone $watermarkBase;
                    $wm->resize((int) ($image->width() * 0.18), null, function ($c) {
                        $c->aspectRatio();
                    });
                    // Placer le watermark au centre de l'image
                    $image->insert($wm, 'center');
                }

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

        return view('annonces.edit', compact('annonce', 'brands', 'models'));
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
