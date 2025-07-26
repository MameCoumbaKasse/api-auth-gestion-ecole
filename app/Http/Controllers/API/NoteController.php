<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function index()
    {
        return response()->json(Note::with(['eleve.user', 'matiere'])->get());
    }

    public function show($id)
    {
        return response()->json(Note::with(['eleve.user', 'matiere'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'eleve_id' => 'required|exists:eleves,id',
            'matiere_id' => 'required|exists:matieres,id',
            'periode' => 'required',
            'note' => 'required|numeric|min:0|max:20',
        ]);

        $note = Note::create($request->all());

        return response()->json([
            'message' => 'Note ajoutée avec succès.',
            'note' => $note
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $note = Note::findOrFail($id);

        $request->validate([
            'note' => 'required|numeric|min:0|max:20',
        ]);

        $note->update(['note' => $request->note]);

        return response()->json([
            'message' => 'Note mise à jour.',
            'note' => $note
        ]);
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();

        return response()->json(['message' => 'Note supprimée.']);
    }

    public function periodes()
    {
        $periodes = [];
        $anneeActuelle = now()->year;

        for ($annee = $anneeActuelle; $annee <= 2098; $annee++) {
            $anneeSuivante = $annee + 1;
            $label = "$annee-$anneeSuivante";

            $periodes[] = "1er trimestre $label";
            $periodes[] = "2eme trimestre $label";
            $periodes[] = "3eme trimestre $label";
        }

        return response()->json($periodes);
    }

}