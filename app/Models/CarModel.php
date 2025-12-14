<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;              // ← important
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarModel extends Model
{
    use HasFactory;

    protected $fillable = ['car_brand_id', 'name'];

    /**
     * Each car model belongs to a brand.
     */
    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    /**
     * One model has many ads (via modele_id on Annonce).
     */
    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'modele_id');
    }
}
