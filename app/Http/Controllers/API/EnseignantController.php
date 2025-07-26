<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enseignant;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserEnseignantNotification;

class EnseignantController extends Controller
{
    public function index()
    {
        return response()->json(Enseignant::with('user')->get());
    }

    public function show($id)
    {
        return response()->json(Enseignant::with('user')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $incomingFields = $request->validate([
            'nom' => 'required|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'\s-]+$/|max:155',
            'prenom' => 'required|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'\s-]+$/|max:155',
            'email' => 'required|email|unique:users,email'
        ]);

        $password = User::generatePassword();

        $user = User::create([
            'nom' => $incomingFields['nom'],
            'prenom' => $incomingFields['prenom'],
            'email' => $incomingFields['email'],
            'password' => bcrypt($password),
            'role' => 'enseignant',
        ]);

        $enseignant = Enseignant::create([
            'user_id' => $user->id
        ]);

        // TODO : Envoi de mail avec les identifiants
        if ($user) {
            Mail::to($user->email)->send(new NewUserEnseignantNotification($user, $password)); 
        }

        return response()->json([
            'message' => 'Enseignant créé avec succès.',
            'user' => $user,
            'enseignant' => $enseignant
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $user = $enseignant->user;

        $incomingFields = $request->validate([
            'nom' => 'sometimes|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'\s-]+$/|max:155',
            'prenom' => 'sometimes|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\'\s-]+$/|max:155',
            'email' => 'sometimes|email|unique:users,email,' . $user->id
        ]);

        $user->update($incomingFields);

        return response()->json([
            'message' => 'Enseignant mis à jour avec succès.',
            'enseignant' => $enseignant->load('user')
        ]);
    }

    public function destroy($id)
    {
        $enseignant = Enseignant::findOrFail($id);
        $enseignant->user()->delete(); // supprime aussi le compte associé
        $enseignant->delete();

        return response()->json(['message' => 'Enseignant supprimé avec succès.']);
    }
}