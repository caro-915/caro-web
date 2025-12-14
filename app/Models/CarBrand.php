<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;              // ← import Eloquent Model
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarBrand extends Model
{
    use HasFactory;

    // Allow mass assignment for these attributes
    protected $fillable = ['name', 'region'];

    /**
     * One brand has many car models.
     */
    public function models()
    {
        return $this->hasMany(CarModel::class, 'car_brand_id');
    }

    /**
     * One brand has many ads (via marque_id on Annonce).
     */
    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'marque_id');
    }
}
