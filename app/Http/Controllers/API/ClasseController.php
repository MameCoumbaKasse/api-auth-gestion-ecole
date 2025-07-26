<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\Classe;

class ClasseController extends Controller
{
    public function index()
    {
        return response()->json(Classe::with('eleves', 'matieres')->get());
    }

    public function show($id)
    {
        return response()->json(Classe::with('eleves', 'matieres')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Action réservée à l\'administration'], 403);
        }

        $incomingFields = $request->validate([
            'nom' => 'required|string|max:100|unique:classes,nom',
            'niveau' => 'required|string|max:50',
        ]);

        // Normalisation du nom : trim, minuscule, suppression des espaces multiples
        $normalizedNom = strtolower(trim(preg_replace('/\s+/', ' ', $incomingFields['nom'])));

        // Vérifie l'existence d'une classe avec le même nom normalisé
        $classeExistante = Classe::all()->first(function ($classe) use ($normalizedNom) {
            $nomClasse = strtolower(trim(preg_replace('/\s+/', ' ', $classe->nom)));
            return $nomClasse === $normalizedNom;
        });

        if ($classeExistante) {
            return response()->json(['message' => 'Une classe avec ce nom existe déjà.'], 422);
        }

        $classe = Classe::create($incomingFields);

        return response()->json([
            'message' => 'Classe créée avec succès.',
            'classe' => $classe
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Action réservée à l\'administration'], 403);
        }

        $classe = Classe::findOrFail($id);

        $incomingFields = $request->validate([
            'nom' => ['sometimes', 'string', 'max:100', Rule::unique('classes')->ignore($classe->id)],
            'niveau' => 'sometimes|string|max:50',
        ]);

        if (isset($incomingFields['nom'])) {
            $normalizedNom = strtolower(trim(preg_replace('/\s+/', ' ', $incomingFields['nom'])));

            $classeExistante = Classe::where('id', '!=', $classe->id)->get()->first(function ($c) use ($normalizedNom) {
                $nomClasse = strtolower(trim(preg_replace('/\s+/', ' ', $c->nom)));
                return $nomClasse === $normalizedNom;
            });

            if ($classeExistante) {
                return response()->json(['message' => 'Une autre classe avec ce nom existe déjà.'], 422);
            }
        }

        $classe->update($incomingFields);

        return response()->json([
            'message' => 'Classe mise à jour avec succès.',
            'classe' => $classe
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Action réservée à l\'administration'], 403);
        }
        
        $classe = Classe::findOrFail($id);
        $classe->delete();

        return response()->json(['message' => 'Classe supprimée avec succès.']);
    }
}