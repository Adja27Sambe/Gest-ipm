<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    /**
     * Affiche la liste des rôles et profils métiers avec leurs permissions.
     */
    public function index(Request $request)
    {
        $query = Role::with(['permissions', 'utilisateurs']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('categorie', 'like', "%{$search}%")
                  ->orWhereHas('permissions', function ($pq) use ($search) {
                      $pq->where('libelle', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        $perPage = $request->input('per_page', 12);
        $roles = $query->orderBy('id_role', 'asc')->paginate($perPage)->withQueryString();

        $allPermissions = Cache::rememberForever('permissions_all', function () {
            return Permission::orderBy('categorie')->orderBy('libelle')->get();
        });

        $permissionsByCategory = $allPermissions->groupBy('categorie');
        $categories = Role::whereNotNull('categorie')->distinct()->pluck('categorie');

        $stats = [
            'total_roles' => Role::count(),
            'total_utilisateurs' => \App\Models\Utilisateur::count(),
            'total_permissions' => $allPermissions->count(),
        ];

        return view('roles.index', compact('roles', 'permissionsByCategory', 'allPermissions', 'categories', 'stats'));
    }

    /**
     * Enregistre un nouveau profil / rôle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:100|unique:role,libelle',
            'categorie' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permission,id_permission'
        ]);

        $role = Role::create([
            'libelle' => $request->libelle,
            'code' => \Illuminate\Support\Str::slug($request->libelle, '_'),
            'categorie' => $request->categorie ?? 'Personnalisé',
            'description' => $request->description
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        Cache::forget('permissions_all');

        return redirect()->route('roles.index')->with('success', 'Nouveau profil "' . $role->libelle . '" créé avec succès.');
    }

    /**
     * Met à jour un profil / rôle existant et ses permissions.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'libelle' => 'required|string|max:100|unique:role,libelle,' . $role->id_role . ',id_role',
            'categorie' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permission,id_permission'
        ]);

        $role->update([
            'libelle' => $request->libelle,
            'categorie' => $request->categorie ?? $role->categorie,
            'description' => $request->description
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        Cache::forget('permissions_all');

        return redirect()->route('roles.index')->with('success', 'Profil "' . $role->libelle . '" mis à jour avec succès.');
    }

    /**
     * Supprime un rôle avec contrôle de sécurité.
     */
    public function destroy(Role $role)
    {
        // Protection du rôle Administrateur système
        if ($role->libelle === 'Administrateur' || $role->code === 'Administrateur') {
            return redirect()->route('roles.index')->with('error', 'Le profil Administrateur est un profil système fondamental et ne peut pas être supprimé.');
        }

        if ($role->utilisateurs()->exists()) {
            return redirect()->route('roles.index')->with('error', 'Impossible de supprimer ce profil car il est attribué à ' . $role->utilisateurs()->count() . ' utilisateur(s). Veuillez d\'abord réaffecter ces utilisateurs.');
        }

        $role->permissions()->detach();
        $role->delete();

        Cache::forget('permissions_all');

        return redirect()->route('roles.index')->with('success', 'Profil supprimé avec succès.');
    }
}
