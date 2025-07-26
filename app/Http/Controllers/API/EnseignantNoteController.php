<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class EnseignantNoteController extends Controller
{

    public function getClasses()
    {
        $enseignant = auth()->user()->enseignant;

        // Classes distinctes liées à l’enseignant via ses matières
        $classes = $enseignant->matieres()->with('classe')->get()
            ->pluck('classe')
            ->unique('id')
            ->values();

        return response()->json($classes);
    }

    public function getElevesParClasse($classeId)
    {
        $enseignant = auth()->user()->enseignant;

        // Vérifie que la classe est liée à au moins une matière de l'enseignant
        $matiere = $enseignant->matieres()->where('classe_id', $classeId)->first();

        if (!$matiere) {
            return response()->json(['error' => 'Cette classe ne vous est pas affectée.'], 403);
        }

        $eleves = Eleve::where('classe_id', $classeId)
            ->with(['user', 'classe.niveau'])
            ->get()
            ->map(function ($eleve) use ($matiere) {
                $note = Note::where('eleve_id', $eleve->id)
                    ->where('matiere_id', $matiere->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $eleve->id,
                    'nom' => $eleve->user->nom,
                    'prenom' => $eleve->user->prenom,
                    'date_naissance' => $eleve->date_naissance,
                    'classe' => $eleve->classe->nom,
                    'niveau' => $eleve->classe->niveau->nom ?? '',
                    'matiere_id' => $matiere->id,
                    'matiere_nom' => $matiere->nom,
                    'note' => $note ? $note->note : null,
                    'periode' => $note ? $note->periode : null,
                    'note_id' => $note ? $note->id : null,
                ];
            });

        return response()->json($eleves);
    }

    public function store(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'matiere_id' => 'required|exists:matieres,id',
            'periode' => 'required',
            'note' => 'required|numeric|min:0|max:20',
        ]);

        $enseignant = auth()->user()->enseignant;

        $matiere = Matiere::findOrFail($request->matiere_id);
        $eleve = Eleve::findOrFail($request->eleve_id);

        if ($matiere->enseignant_id !== $enseignant->id) {
            return response()->json(['error' => 'Vous n\'êtes pas autorisé pour cette matière.'], 403);
        }

        if ($matiere->classe_id !== $eleve->classe_id) {
            return response()->json(['error' => 'L’élève ne fait pas partie de la classe de la matière.'], 422);
        }

        $note = Note::updateOrCreate(
            [
                'eleve_id' => $request->eleve_id,
                'matiere_id' => $request->matiere_id,
                'periode' => $request->periode,
            ],
            ['note' => $request->note]
        );

        return response()->json([
            'message' => 'Note enregistrée avec succès.',
            'note' => $note
        ]);
    }

}