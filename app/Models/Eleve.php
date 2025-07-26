<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Classe;
use App\Models\Bulletin;

class Eleve extends Model
{
    protected $fillable = ['matricule','date_naissance','nom_prenom_parent','email_parent','document_justificatif','user_id', 'classe_id'];

    protected static function booted()
    {
        static::creating(function ($eleve) {
            $eleve->matricule = self::generateMatricule();
        });
    }

    public static function generateMatricule()
    {
        do {
            $matricule = 'E' . now()->year . '-' . strtoupper(Str::random(6));
        } while (self::where('matricule', $matricule)->exists());

        return $matricule;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletin::class);
    }
}