<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\User;

class Annonce extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'slug',
        'description',
        'prix',
        'marque',
        'modele',
        'annee',
        'kilometrage',
        'carburant',
        'boite_vitesse',
        'ville',
        'vehicle_type',
        'image_path',
        'image_path_2',
        'image_path_3',
        'image_path_4',
        'image_path_5',
        'user_id',
        'show_phone',
        'condition',
        'couleur',
        'document_type',
        'finition',
        'seller_type',
        'is_active',
    ];

    protected $casts = [
        'show_phone' => 'boolean',
        'is_active'  => 'boolean',
    ];

    /**
     * Boot the model and auto-generate slug on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($annonce) {
            if (empty($annonce->slug)) {
                $annonce->slug = $annonce->generateUniqueSlug();
            }
        });

        static::updating(function ($annonce) {
            // Regenerate slug if title changed and slug wasn't manually set
            if ($annonce->isDirty('titre') && !$annonce->isDirty('slug')) {
                $annonce->slug = $annonce->generateUniqueSlug();
            }
        });
    }

    /**
     * Generate a unique slug for the annonce.
     */
    public function generateUniqueSlug(): string
    {
        $baseSlug = Str::slug($this->titre ?: ($this->marque . ' ' . $this->modele));
        
        if (empty($baseSlug)) {
            $baseSlug = 'annonce';
        }
        
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the route key name for model binding (allows both ID and slug).
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the URL-friendly path for this annonce.
     */
    public function getUrlPath(): string
    {
        return $this->id . '-' . ($this->slug ?: Str::slug($this->titre));
    }

    public function marque()
    {
        return $this->belongsTo(CarBrand::class, 'marque', 'name');
    }

    public function modele()
    {
        return $this->belongsTo(CarModel::class, 'modele', 'name');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversations()
    {
        return $this->hasMany(\App\Models\Conversation::class, 'annonce_id');
    }

    public function favorites()
    {
        return $this->hasMany(\App\Models\Favorite::class);
    }

    public function boosts()
    {
        return $this->hasMany(\App\Models\Boost::class);
    }

    /**
     * Get the active boost for this annonce.
     */
    public function activeBoost()
    {
        return $this->boosts()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Check if annonce is currently boosted.
     */
    public function isBoosted(): bool
    {
        return $this->activeBoost() !== null;
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['marque'])) {
            $query->where('marque', 'like', '%' . $filters['marque'] . '%');
        }

        if (!empty($filters['modele'])) {
            $query->where('modele', 'like', '%' . $filters['modele'] . '%');
        }

        if (!empty($filters['price_max'])) {
            $query->where('prix', '<=', (int) $filters['price_max']);
        }

        if (!empty($filters['annee_min'])) {
            $query->where('annee', '>=', (int) $filters['annee_min']);
        }

        if (!empty($filters['annee_max'])) {
            $query->where('annee', '<=', (int) $filters['annee_max']);
        }

        if (!empty($filters['km_min'])) {
            $query->where('kilometrage', '>=', (int) $filters['km_min']);
        }

        if (!empty($filters['km_max'])) {
            $query->where('kilometrage', '<=', (int) $filters['km_max']);
        }

        if (!empty($filters['carburant']) && $filters['carburant'] !== 'any') {
            $query->where('carburant', $filters['carburant']);
        }

        if (!empty($filters['wilaya'])) {
            $query->where('ville', 'like', '%' . $filters['wilaya'] . '%');
        }

        if (!empty($filters['vehicle_type']) && $filters['vehicle_type'] !== 'any') {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        return $query;
    }
}
