<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use Exception;
use App\Models\User;
use App\Models\Eleve;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserEleveNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EleveController extends Controller
{
    public function index()
    {
        // Charger user et classe pour tout le monde
        $relations = ['user', 'classe'];

        // Ajouter les bulletins si c'est un admin
        if (auth()->user()->isAdmin()) {
            $relations[] = 'bulletins';
        }

        return response()->json(Eleve::with($relations)->get());
    }

    public function show($id)
    {
        // Charger user et classe pour tout le monde
        $relations = ['user', 'classe'];

        // Ajouter les bulletins si c'est un admin
        if (auth()->user()->isAdmin()) {
            $relations[] = 'bulletins';
        }

        return response()->json(Eleve::with($relations)->findOrFail($id));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Action réservée à l\'administration'], 403);
        }

        $incomingFields = $request->validate([
            'nom' => 'required|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'\s-]+$/|max:155',
            'prenom' => 'required|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'\s-]+$/|max:155',
            'date_naissance' => 'required|date',
            'nom_prenom_parent' => 'required|string',
            'email_parent' => 'required|email',
            'document_justificatif' => 'required|file',
            'classe_id' => 'required|exists:classes,id',
        ]);

        $password = User::generatePassword();

        $user = User::create([
            'nom' => $incomingFields['nom'],
            'prenom' => $incomingFields['prenom'],
            'role' => 'eleve',
            'password' => bcrypt($password),
        ]);

        $path = $request->file('document_justificatif')->store('documents_administratifs', 's3');

        $url = Storage::disk('s3')->url($path);

        $eleve = Eleve::create([
            'user_id' => $user->id,
            'date_naissance' => $incomingFields['date_naissance'],
            'nom_prenom_parent' => $incomingFields['nom_prenom_parent'],
            'email_parent' => $incomingFields['email_parent'],
            'document_justificatif' => $url,
            'classe_id' => $incomingFields['classe_id'],
        ]);

        // TODO : Envoyer un mail au parent ici avec les identifiants (si nécessaire)
        if($eleve) { Mail::to($incomingFields['email_parent'])->send(new NewUserEleveNotification($user, $password)); }

        return response()->json([
            'message' => 'Élève créé avec succès.',
            'user' => $user,
            'eleve' => $eleve
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Action réservée à l\'administration'], 403);
        }
        
        try {
            $eleve = Eleve::with('user')->findOrFail($id); // Inclure la relation user

            $incomingFields = $request->validate([
                'nom' => 'sometimes|string|max:155',
                'prenom' => 'sometimes|string|max:155',
                'date_naissance' => 'sometimes|date',
                'nom_prenom_parent' => 'sometimes|string|max:155',
                'email_parent' => 'sometimes|email',
                'document_justificatif' => 'nullable|file',
                'classe_id' => 'sometimes|exists:classes,id',
            ]);

            // 1. Mise à jour du user (nom, prénom)
            if ($request->has('nom')) {
                $eleve->user->nom = $request->nom;
            }
            if ($request->has('prenom')) {
                $eleve->user->prenom = $request->prenom;
            }
            $eleve->user->save();

            // 2. Gestion du document justificatif
            if ($request->hasFile('document_justificatif')) {
                $oldPath = parse_url($eleve->document_justificatif, PHP_URL_PATH);
                $relativePath = ltrim(str_replace("/mon-compartiment-app-gestion-ecole/", "", $oldPath), "/");

                Storage::disk('s3')->delete($relativePath);

                $newPath = $request->file('document_justificatif')->store('documents_administratifs', 's3');
                $incomingFields['document_justificatif'] = Storage::disk('s3')->url($newPath);
            }

            // 3. Mise à jour des champs Eleve
            $eleve->update($incomingFields);

            return response()->json([
                'message' => 'Élève et utilisateur mis à jour avec succès.',
                'eleve' => $eleve->load('user', 'classe')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation échouée',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error("Erreur mise à jour élève : " . $e->getMessage());
            return response()->json([
                'error' => 'Erreur serveur lors de la mise à jour',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Action réservée à l\'administration'], 403);
        }
        
        $eleve = Eleve::findOrFail($id);
        $eleve->user()->delete();
        $eleve->delete();

        return response()->json(['message' => 'Élève supprimé avec succès.']);
    }
}