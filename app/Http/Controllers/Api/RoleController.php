<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Liste des rôles avec leurs permissions.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    /**
     * Liste toutes les permissions disponibles.
     */
    public function permissions()
    {
        $permissions = \Illuminate\Support\Facades\Cache::rememberForever('permissions_all', function () {
            return Permission::all();
        });
        return response()->json($permissions);
    }

    /**
     * Création d'un rôle.
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

        return response()->json($role->load('permissions'), 201);
    }

    /**
     * Affichage d'un rôle.
     */
    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    /**
     * Mise à jour d'un rôle.
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
        }

        return response()->json($role->load('permissions'));
    }

    /**
     * Suppression d'un rôle.
     */
    public function destroy(Role $role)
    {
        if ($role->utilisateurs()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce rôle car il est attribué à des utilisateurs.'
            ], 403);
        }

        $role->delete();

        return response()->json(null, 204);
    }
}
