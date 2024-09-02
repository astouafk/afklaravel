<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'role_id',
        'password',
        'refresh_token',
        'photo', 
        'etat'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'refresh_token',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role->name, $roles);
        }
        return $this->role->name === $roles;
    }
}
