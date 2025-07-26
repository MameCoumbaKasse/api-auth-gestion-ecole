<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Eleve;

class Bulletin extends Model
{
    protected $fillable = ['eleve_id','periode','moyenne','mention','rang','appreciation','pdf_path'];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class);
    }
}