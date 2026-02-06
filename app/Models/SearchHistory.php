<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SearchHistory extends Model
{
    protected $fillable = [
        'user_id',
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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSearchLabelAttribute()
    {
        $parts = [];
        if ($this->marque) $parts[] = $this->marque;
        if ($this->modele) $parts[] = $this->modele;
        if ($this->vehicle_type) $parts[] = ucfirst($this->vehicle_type);
        if ($this->wilaya) $parts[] = $this->wilaya;
        
        return implode(' • ', $parts) ?: 'Toutes les annonces';
    }
}
