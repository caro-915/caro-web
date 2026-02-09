<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */      
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'google_id',
        'avatar',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function conversationsAsBuyer()
{
    return $this->hasMany(\App\Models\Conversation::class, 'buyer_id');
}

public function conversationsAsSeller()
{
    return $this->hasMany(\App\Models\Conversation::class, 'seller_id');
}

public function favorites()
{
    return $this->hasMany(\App\Models\Favorite::class);
}

public function favoriteAnnonces()
{
    return $this->belongsToMany(
        \App\Models\Annonce::class,
        'favorites'
    )->withTimestamps();
}
public function annonces()
{
    return $this->hasMany(\App\Models\Annonce::class);
}

public function subscriptions()
{
    return $this->hasMany(\App\Models\Subscription::class);
}

public function boosts()
{
    return $this->hasMany(\App\Models\Boost::class);
}

/**
 * Get the user's active subscription.
 */
public function activeSubscription()
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->where('payment_status', 'approved')
        ->where('expires_at', '>', now())
        ->latest()
        ->first();
}

/**
 * Check if user has active PRO subscription.
 */
public function isPro(): bool
{
    return $this->activeSubscription() !== null;
}
    

}
