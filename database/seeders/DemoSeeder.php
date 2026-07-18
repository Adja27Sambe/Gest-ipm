<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Création des permissions
        $permissions = [
            'Gérer les rôles',
            'Gérer les demandes',
            'Gérer les entreprises',
            'Gérer les salariés'
        ];

        $createdPermissions = [];
        foreach ($permissions as $permLibelle) {
            $createdPermissions[] = Permission::firstOrCreate(['libelle' => $permLibelle]);
        }

        // 2. Création du rôle Administrateur
        $roleAdmin = Role::firstOrCreate(['libelle' => 'Administrateur']);
        
        // Assigner toutes les permissions à l'Administrateur
        $roleAdmin->permissions()->sync(collect($createdPermissions)->pluck('id_permission'));

        // 3. Création du rôle Agent (avec moins de droits)
        $roleAgent = Role::firstOrCreate(['libelle' => 'Agent de saisie']);
        
        // L'Agent ne gère pas les rôles, uniquement le reste
        $agentPermissions = collect($createdPermissions)->filter(function ($perm) {
            return $perm->libelle !== 'Gérer les rôles';
        });
        $roleAgent->permissions()->sync($agentPermissions->pluck('id_permission'));

        // 4. Création du compte Administrateur
        Utilisateur::updateOrCreate(
            ['login' => 'admin'],
            [
                'nom' => 'Administrateur',
                'prenom' => 'Super',
                'email' => 'admin@ipm.com',
                'mot_de_passe' => Hash::make('password'),
                'statut' => 'actif',
                'id_role' => $roleAdmin->id_role,
            ]
        );

        // 5. Création du compte Agent
        Utilisateur::updateOrCreate(
            ['login' => 'agent'],
            [
                'nom' => 'Saisie',
                'prenom' => 'Agent de',
                'email' => 'agent@ipm.com',
                'mot_de_passe' => Hash::make('password'),
                'statut' => 'actif',
                'id_role' => $roleAgent->id_role,
            ]
        );
    }
}
