<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matiere;

class MatiereController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return response()->json(Matiere::with(['classe', 'enseignant.user'])->get());
        }

        if ($user->isEnseignant() && $user->enseignant) {
            return response()->json(Matiere::with(['classe', 'enseignant.user'])
                ->where('enseignant_id', $user->enseignant->id)
                ->get());
        }

        if ($user->isEleve() && $user->eleve) {
            return response()->json(Matiere::with(['classe', 'enseignant.user'])->get());
        }

        return response()->json(['message' => 'Non autorisé'], 403);
    }

    public function show($id)
    {
        return response()->json(Matiere::with('classe', 'enseignant.user')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $incomingFields = $request->validate([
            'nom' => 'required|string|max:100',
            'coefficient' => 'required|numeric|min:0.1|max:20',
            'periode' => 'required|string|max:20',
            'classe_id' => 'required|exists:classes,id',
            'enseignant_id' => 'required|exists:enseignants,id'
        ]);

        $matiere = Matiere::create($incomingFields);

        return response()->json([
            'message' => 'Matière créée avec succès.',
            'matiere' => $matiere
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $matiere = Matiere::findOrFail($id);

        $incomingFields = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'coefficient' => 'sometimes|numeric|min:0.1|max:20',
            'periode' => 'sometimes|string|max:20',
            'classe_id' => 'sometimes|exists:classes,id',
            'enseignant_id' => 'sometimes|exists:enseignants,id'
        ]);

        $matiere->update($incomingFields);

        return response()->json([
            'message' => 'Matière mise à jour avec succès.',
            'matiere' => $matiere
        ]);
    }

    public function destroy($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();

        return response()->json(['message' => 'Matière supprimée avec succès.']);
    }

    public function periodes()
    {
        $annees = [];
        $anneeActuelle = now()->year;

        for ($i = 0; $i < 99; $i++) {
            $debut = $anneeActuelle + $i;
            $fin = $debut + 1;
            $annees[] = "$debut-$fin";
        }

        return response()->json($annees);
    }
}