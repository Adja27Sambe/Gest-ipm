<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the participant login form.
     */
    public function showLoginForm()
    {
        // Exécuter la migration depuis le web (contourne le problème de terminal Herd)
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            \Log::error("Impossible de lancer la migration depuis le web: " . $e->getMessage());
        }

        return view('participant.auth.login');
    }

    /**
     * Handle the participant login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'matricule' => 'required|string',
            'code_securite' => 'required|string',
        ]);

        // Laravel's Auth expects 'password' by default for the attempt method.
        // Since we overrode getAuthPassword() in the Salarie model, we map code_securite to password here
        $attemptCredentials = [
            'matricule' => $credentials['matricule'],
            'password' => $credentials['code_securite'],
        ];

        if (Auth::guard('participant')->attempt($attemptCredentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('participant.dashboard'));
        }

        // Debug: why did it fail?
        $user = \App\Models\Salarie::where('matricule', $credentials['matricule'])->first();
        if (!$user) {
            $msg = "Erreur: Matricule introuvable dans la base de données.";
        } else {
            // Si le code est null, on l'initialise avec ce que l'utilisateur a tapé (utile pour la démo)
            if (is_null($user->code_securite)) {
                $user->code_securite = \Illuminate\Support\Facades\Hash::make($credentials['code_securite']);
                $user->save();
            }

            $check = \Illuminate\Support\Facades\Hash::check($credentials['code_securite'], $user->code_securite);
            
            if ($check) {
                Auth::guard('participant')->login($user);
                $request->session()->regenerate();
                return redirect()->intended(route('participant.dashboard'));
            } else {
                $msg = "Erreur: Code de sécurité incorrect.";
            }
        }

        return back()->withErrors([
            'matricule' => $msg,
        ])->onlyInput('matricule');
    }

    /**
     * Log the participant out of the application.
     */
    public function logout(Request $request)
    {
        Auth::guard('participant')->logout();

        // Ne pas invalider la session complète pour ne pas déconnecter l'administrateur
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return redirect()->route('participant.login');
    }
}
