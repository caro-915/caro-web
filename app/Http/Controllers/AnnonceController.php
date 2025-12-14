<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
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
    /**
     * Home page: search form + sections.
     */
    public function index(Request $request)
    {
        $marques = CarBrand::orderBy('name')->get();
        $modeles = CarModel::orderBy('name')->get();

        $baseQuery = Annonce::query()->latest();

        // Si tu veux que la home n’affiche que les actives :
        $baseQuery->where('is_active', true);

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

        $topDeals = Annonce::with(['marque', 'modele'])
            ->where('is_active', true)
            ->orderBy('prix', 'asc')
            ->take(6)
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
            'topDeals',
            'popularMarques',
            'popularModeles'
        ));
    }

    public function create()
    {
        $brands = CarBrand::orderBy('name')->get();
        $models = CarModel::orderBy('name')->get();

        return view('annonces.create', compact('brands', 'models'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'required|string',
            'prix'          => 'required|integer|min:0',

            'marque'        => 'required|string|max:100',
            'modele'        => 'required|string|max:100',
            'annee'         => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'kilometrage'   => 'nullable|integer|min:0',
            'carburant'     => 'nullable|string|max:50',
            'boite_vitesse' => 'nullable|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'vehicle_type'  => 'nullable|string|max:50',

            'show_phone'    => ['nullable', 'boolean'],
            'couleur'       => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:carte_grise,procuration'],
            'finition'      => ['nullable', 'string', 'max:80'],
            'condition'     => ['required', 'in:neuf,occasion'],

            'images'        => 'nullable|array|max:4',
            'images.*'      => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data['show_phone'] = $request->boolean('show_phone');
        $data['condition']  = $request->input('condition', 'occasion');

        $imagePaths = [
            'image_path'   => null,
            'image_path_2' => null,
            'image_path_3' => null,
            'image_path_4' => null,
        ];

        if ($request->hasFile('images')) {
            $stored = [];

            foreach ($request->file('images') as $index => $file) {
                if ($index >= 4) break;

                $filename = 'annonces/' . Str::uuid() . '.jpg';

                $image = Image::make($file->getRealPath())->orientate();

                $watermarkPath = public_path('watermark.png');
                if (file_exists($watermarkPath)) {
                    $watermark = Image::make($watermarkPath)->opacity(30)->resize($image->width() * 0.2, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->insert($watermark, 'center');
                }

                Storage::disk('public')->put($filename, (string) $image->encode('jpg', 90));

                $stored[] = $filename;
            }

            if (isset($stored[0])) $imagePaths['image_path']   = $stored[0];
            if (isset($stored[1])) $imagePaths['image_path_2'] = $stored[1];
            if (isset($stored[2])) $imagePaths['image_path_3'] = $stored[2];
            if (isset($stored[3])) $imagePaths['image_path_4'] = $stored[3];
        }

        $data = array_merge($data, $imagePaths);
        $data['user_id'] = auth()->id() ?? 1;

        $annonce = Annonce::create($data);

        return redirect()
            ->route('annonces.show', $annonce->id)
            ->with('success', 'Annonce créée avec succès.');
    }

    public function show(Annonce $annonce)
    {
        $isOwner = auth()->check() && auth()->id() === $annonce->user_id;
        $isAdmin = auth()->check() && auth()->user()->is_admin;

        // annonce désactivée : visible uniquement par owner ou admin
        if (!$annonce->is_active && !($isOwner || $isAdmin)) {
            abort(404);
        }

        // vues : 1 seule fois par session + on ne compte pas owner/admin
        if (!$isOwner && !$isAdmin) {
            $key = 'viewed_annonce_' . $annonce->id;
            if (!session()->has($key)) {
                $annonce->increment('views');
                session()->put($key, true);
            }
        }

        $annonce->load('user');

        $similarAds = Annonce::where('id', '!=', $annonce->id)
            ->where('marque', $annonce->marque)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        return view('annonces.show', compact('annonce', 'similarAds'));
    }

    public function search(Request $request)
    {
        // front = uniquement annonces actives
        $query = Annonce::query()->where('is_active', true);

        // vehicle_type: ignorer si vide ou "any"
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
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder
                    ->where('titre', 'like', '%' . $q . '%')
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
        'image_path','views','created_at'
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

        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'prix'          => 'required|integer|min:0',

            'marque'        => 'required|string|max:100',
            'modele'        => 'required|string|max:100',
            'annee'         => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'kilometrage'   => 'nullable|integer|min:0',
            'carburant'     => 'nullable|string|max:50',
            'boite_vitesse' => 'nullable|string|max:50',
            'ville'         => 'nullable|string|max:100',
            'vehicle_type'  => 'nullable|string|max:50',

            'show_phone'    => 'nullable|boolean',
            'couleur'       => ['nullable', 'string', 'max:50'],
            'document_type' => ['nullable', 'in:carte_grise,procuration'],
            'finition'      => ['nullable', 'string', 'max:80'],
            'condition'     => ['required', 'in:neuf,occasion'],
        ]);

        $data['show_phone'] = $request->boolean('show_phone');

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

        $images = [
            $annonce->image_path,
            $annonce->image_path_2,
            $annonce->image_path_3,
            $annonce->image_path_4,
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
        if ($annonce->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez modifier que vos propres annonces.');
        }

        $brands = CarBrand::orderBy('name')->get();
        $models = CarModel::orderBy('name')->get();

        return view('annonces.edit', compact('annonce', 'brands', 'models'));
    }
}
