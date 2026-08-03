<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Gère la tentative de connexion.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        // Mappage pour Laravel Auth qui attend "password"
        $authCredentials = [
            'login' => $credentials['login'],
            'password' => $credentials['mot_de_passe']
        ];

        if (Auth::attempt($authCredentials)) {
            $user = Auth::user();

            // Vérification du statut
            if ($user->statut !== 'actif') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'login' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('reporting.index'));
        }

        return back()->withErrors([
            'login' => 'Les identifiants fournis sont incorrects.',
        ])->onlyInput('login');
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Ne pas invalider la session complète pour ne pas déconnecter le bénéficiaire (multi-guard)
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
