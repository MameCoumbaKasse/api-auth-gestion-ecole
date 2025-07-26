<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use App\Models\Eleve;
use App\Models\Enseignant;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom', 'prenom', 'login', 'email', 'password', 'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Role helpers
    public function isAdmin()     { return $this->role === 'admin'; }
    public function isEleve()     { return $this->role === 'eleve'; }
    public function isEnseignant(){ return $this->role === 'enseignant'; }

    public function eleve()
    {
        return $this->hasOne(Eleve::class);
    }

    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->login) {
                $user->login = self::generateLogin($user);
            }
        });
    }

    public static function generateLogin($user)
    {
        do {
            $prefix = strtoupper(substr($user->nom, 0, 1) . substr($user->prenom, 0, 1));
            $login = $prefix . '-' . now()->year . '-' . strtoupper(Str::random(4));
        } while (self::where('login', $login)->exists());

        return strtolower($login); // pour éviter les majuscules dans les logins
    }

    public static function generatePassword()
    {
        return Str::random(8); // tu peux l’adapter avec des chiffres/lettres
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}