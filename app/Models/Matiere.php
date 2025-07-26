<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Note;

class Matiere extends Model
{
    protected $fillable = ['nom','coefficient','periode','classe_id','enseignant_id'];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}