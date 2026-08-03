<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Affiche la liste des rôles.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $roles = Role::with('permissions', 'utilisateurs')->latest('id_role')->paginate($perPage)->withQueryString();
        $permissions = \Illuminate\Support\Facades\Cache::rememberForever('permissions_all', function () {
            return Permission::all();
        });
        
        return view('roles.index', compact('roles', 'permissions'));
    }

    /**
     * Enregistre un nouveau rôle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:100|unique:role,libelle',
            'permissions' => 'array',
            'permissions.*' => 'exists:permission,id_permission'
        ]);

        $role = Role::create([
            'libelle' => $request->libelle
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Met à jour un rôle existant.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'libelle' => 'required|string|max:100|unique:role,libelle,' . $role->id_role . ',id_role',
            'permissions' => 'array',
            'permissions.*' => 'exists:permission,id_permission'
        ]);

        $role->update([
            'libelle' => $request->libelle
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Supprime un rôle.
     */
    public function destroy(Role $role)
    {
        if ($role->utilisateurs()->exists()) {
            return redirect()->route('roles.index')->with('error', 'Impossible de supprimer ce rôle car il est attribué à des utilisateurs.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rôle supprimé avec succès.');
    }
}
