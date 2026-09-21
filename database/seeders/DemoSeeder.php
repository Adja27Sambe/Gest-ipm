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
        // Types de Demande officiels
        \App\Models\TypeDemande::updateOrCreate(['id_type_demande' => 1], ['libelle' => 'Bon de Commande']);
        \App\Models\TypeDemande::updateOrCreate(['id_type_demande' => 2], ['libelle' => 'Feuille de Maladie']);
        \App\Models\TypeDemande::updateOrCreate(['id_type_demande' => 3], ['libelle' => 'Lettre de Garantie']);

        // Types de Prestation et Paramètres de Couverture officiels
        $defaultTypes = [
            ['id' => 1, 'libelle' => 'Médecine Générale', 'taux' => 75, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'CMG'],
            ['id' => 2, 'libelle' => 'Spécialité Médicale', 'taux' => 70, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'CSP'],
            ['id' => 3, 'libelle' => 'Consultation + Soins Dentaires', 'taux' => 50, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'DEN'],
            ['id' => 4, 'libelle' => 'Consultation Ophtalmologie', 'taux' => 50, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'OPH'],
            ['id' => 5, 'libelle' => 'Analyses Médicales', 'taux' => 65, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'BIO'],
            ['id' => 6, 'libelle' => 'Radiologie', 'taux' => 65, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'RAD'],
            ['id' => 7, 'libelle' => 'Pharmacie', 'taux' => 75, 'plafond' => null, 'plafond_annuel' => null, 'code' => 'PHA'],
            ['id' => 8, 'libelle' => 'Hospitalisation', 'taux' => 70, 'plafond' => 20000, 'plafond_annuel' => null, 'code' => 'HOS'],
            ['id' => 9, 'libelle' => 'Optique Médicale', 'taux' => 70, 'plafond' => 30000, 'plafond_annuel' => null, 'code' => 'OPT'],
            ['id' => 10, 'libelle' => 'Maternité / Accouchement', 'taux' => 70, 'plafond' => 50000, 'plafond_annuel' => null, 'code' => 'MAT'],
        ];
        $validIds = collect($defaultTypes)->pluck('id')->toArray();
        \App\Models\ParametreCouverture::whereNotIn('id_type_prestation', $validIds)->delete();
        \App\Models\TypePrestation::whereNotIn('id_type_prestation', $validIds)->delete();
        if (\Illuminate\Support\Facades\Schema::hasTable('Acte')) {
            \Illuminate\Support\Facades\DB::table('Acte')->whereNotIn('IDActe', $validIds)->delete();
        }
        foreach ($defaultTypes as $dt) {
            $tp = \App\Models\TypePrestation::updateOrCreate(
                ['id_type_prestation' => $dt['id']],
                ['libelle' => $dt['libelle']]
            );
            \App\Models\ParametreCouverture::updateOrCreate(
                ['id_type_prestation' => $tp->id_type_prestation],
                [
                    'taux_prise_charge' => $dt['taux'],
                    'plafond_par_acte' => $dt['plafond'],
                    'plafond_annuel' => $dt['plafond_annuel'],
                    'ticket_moderateur' => 100 - $dt['taux']
                ]
            );
            if (\Illuminate\Support\Facades\Schema::hasTable('Acte')) {
                \Illuminate\Support\Facades\DB::table('Acte')->updateOrInsert(
                    ['IDActe' => $dt['id']],
                    [
                        'Acte' => substr($dt['libelle'], 0, 50),
                        'Taux' => (float)$dt['taux'],
                        'Plafond' => $dt['plafond'] ?? 0,
                        'CodeActe' => substr($dt['code'], 0, 3),
                        'MONTANT' => $dt['plafond'] ?? 0,
                        'Forfait' => 0
                    ]
                );
            }
        }

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
