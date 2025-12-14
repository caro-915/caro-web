<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
    

}
