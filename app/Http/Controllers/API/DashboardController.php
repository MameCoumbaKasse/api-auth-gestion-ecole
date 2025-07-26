<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Bulletin;

class DashboardController extends Controller
{
    public function globalStats()
    {
        $eleves = Eleve::count();
        $classes = Classe::count();
        $enseignants = Enseignant::count();

        return response()->json([
            'eleves' => $eleves,
            'classes' => $classes,
            'enseignants' => $enseignants,
        ]);
    }

    public function moyennesParClasse()
    {
        $moyennes = Classe::with(['eleves.bulletins' => function($query) {
            $query->latest('periode');
        }])
        ->get()
        ->map(function ($classe) {
            $bulletins = $classe->eleves->flatMap->bulletins;

            return [
                'classe' => $classe->nom,
                'moyenne' => $bulletins->count() > 0 ? round($bulletins->avg('moyenne'), 2) : null
            ];
        });

        return response()->json($moyennes);
    }
}