<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;

class AuthController extends Controller
{
    /**
     * Authentification de l'utilisateur (Génération du token)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'mot_de_passe' => 'required|string',
        ]);

        $user = Utilisateur::where('login', $request->login)->first();

        // Vérification de l'utilisateur et du mot de passe
        if (!$user || !Hash::check($request->mot_de_passe, $user->mot_de_passe)) {
            return response()->json([
                'message' => 'Identifiants incorrects.'
            ], 401);
        }

        // Vérification du statut
        if ($user->statut !== 'actif') {
            return response()->json([
                'message' => 'Votre compte est désactivé.'
            ], 403);
        }

        // Génération du token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('role.permissions')
        ]);
    }

    /**
     * Déconnexion de l'utilisateur (Révocation du token)
     */
    public function logout(Request $request)
    {
        // Révocation du token utilisé pour s'authentifier
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }
}
