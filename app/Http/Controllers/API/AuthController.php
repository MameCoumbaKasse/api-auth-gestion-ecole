<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserAdminNotification;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // Un seul admin autorisé
        if (User::where('role', 'admin')->exists()) {
            return response()->json(['message' => 'Un administrateur existe déjà.'], 403);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
        ]);

        $token = JWTAuth::fromUser($user);

        // Tu peux ici envoyer l’email à l’admin
        Mail::to($user->email)->send(new NewUserAdminNotification($user));

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('login', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Login ou mot de passe invalide'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth()->user()->load(['enseignant', 'eleve'])
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me()
    {
        return response()->json(auth()->user()->load(['enseignant', 'eleve']));
    }
}