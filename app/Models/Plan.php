<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['name', 'price', 'duration_days', 'features', 'is_active'];

    protected function casts(): array
    {
        return [
            'features' => 'json',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the features attribute, ensuring it's always an array.
     */
    public function getFeaturesAttribute($value): array
    {
        if (is_null($value)) {
            return [
                'max_active_ads' => 5,
                'boosts_per_month' => 0,
                'boost_duration_days' => 7,
            ];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        
        if (!is_array($decoded)) {
            \Log::warning('⚠️ Features mal formatées pour le plan', [
                'plan_id' => $this->id ?? 'unknown',
                'features_raw' => $value,
            ]);
            return [
                'max_active_ads' => 5,
                'boosts_per_month' => 0,
                'boost_duration_days' => 7,
            ];
        }

        // Force conversion to integers for numeric values
        return [
            'max_active_ads' => (int) ($decoded['max_active_ads'] ?? 5),
            'boosts_per_month' => (int) ($decoded['boosts_per_month'] ?? 0),
            'boost_duration_days' => (int) ($decoded['boost_duration_days'] ?? 7),
        ];
    }

    /**
     * Get all subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
