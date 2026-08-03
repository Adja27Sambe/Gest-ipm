<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUtilisateurRequest;
use App\Http\Requests\UpdateUtilisateurRequest;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    /**
     * Liste des utilisateurs avec filtres et pagination.
     */
    public function index(Request $request)
    {
        $query = Utilisateur::with('role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('login', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('id_role')) {
            $query->where('id_role', $request->id_role);
        }

        $perPage = $request->input('per_page', 10);
        $utilisateurs = $query->latest('id_utilisateur')->paginate($perPage)->withQueryString();
        $roles = Role::all();

        return view('utilisateurs.index', compact('utilisateurs', 'roles'));
    }

    /**
     * Formulaire de création d'un utilisateur.
     */
    public function create()
    {
        $roles = Role::all();
        return view('utilisateurs.create', compact('roles'));
    }

    /**
     * Enregistre un nouvel utilisateur.
     */
    public function store(StoreUtilisateurRequest $request)
    {
        $data = $request->validated();
        $data['mot_de_passe'] = Hash::make($data['mot_de_passe']);

        Utilisateur::create($data);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Formulaire d'édition d'un utilisateur.
     */
    public function edit(Utilisateur $utilisateur)
    {
        $roles = Role::all();
        return view('utilisateurs.edit', compact('utilisateur', 'roles'));
    }

    /**
     * Met à jour un utilisateur.
     */
    public function update(UpdateUtilisateurRequest $request, Utilisateur $utilisateur)
    {
        $data = $request->validated();

        if (!empty($data['mot_de_passe'])) {
            $data['mot_de_passe'] = Hash::make($data['mot_de_passe']);
        } else {
            unset($data['mot_de_passe']);
        }

        $utilisateur->update($data);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprime un utilisateur (avec protection contre l'auto-suppression).
     */
    public function destroy(Utilisateur $utilisateur)
    {
        if ($utilisateur->id_utilisateur === auth()->id()) {
            return redirect()->route('utilisateurs.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $utilisateur->delete();

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
