<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Administration
            'gerer_roles',
            'voir_audit',
            'gerer_parametres_couverture',
            // Cœur de Métier
            'gerer_entreprises',
            'gerer_salaries',
            'gerer_prestataires',
            // Finances
            'gerer_cotisations',
            'Gérer la facturation', // Conservation du nom existant
            // Médical
            'consulter_dossier_medical', // Conservation du nom existant
            'gerer_prestations',
            'gerer_demandes',
            // Divers
            'gerer_medias',
            'gerer_pieces_jointes',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'libelle' => $perm
            ]);
        }
    }
}
