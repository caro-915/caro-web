<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\User;

class Annonce extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
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
        'user_id',
        'show_phone',
        'condition',
        'couleur',
        'document_type',
        'finition',
        'is_active',
    ];

    protected $casts = [
        'show_phone' => 'boolean',
        'is_active'  => 'boolean',
    ];

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

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['marque'])) {
            $query->where('marque', $filters['marque']);
        }

        if (!empty($filters['modele'])) {
            $query->where('modele', $filters['modele']);
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
