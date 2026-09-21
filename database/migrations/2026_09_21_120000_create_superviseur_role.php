<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        // 1. Vérifier ou insérer le rôle Superviseur
        $role = DB::table('role')
            ->where('code', 'superviseur')
            ->orWhere('libelle', 'Superviseur')
            ->first();

        $roleData = [
            'code' => 'superviseur',
            'libelle' => 'Superviseur',
            'categorie' => 'Supervision & Contrôle',
            'description' => 'Profil de supervision globale avec vue d\'ensemble sur l\'ensemble des modules (adhérents, participants, prises en charge, prestations, facturation, cotisations, réseau médical, audit) et droits complets de génération et de téléchargement de documents (cartes assurés, bons de prise en charge, feuilles de maladie, lettres de garantie, pièces jointes, exports et rapports).',
            'updated_at' => $now,
        ];

        if ($role) {
            DB::table('role')
                ->where('id_role', $role->id_role)
                ->update($roleData);
            $roleId = $role->id_role;
        } else {
            $roleData['created_at'] = $now;
            $roleId = DB::table('role')->insertGetId($roleData);
        }

        // 2. Liste des permissions opérationnelles accordées au profil Superviseur
        $supervisorPermissions = [
            'gerer_demandes',             // Vue d'ensemble et gestion des prises en charge + génération PDF
            'valider_demandes',           // Validation des prises en charge
            'gerer_prestations',          // Vue d'ensemble des prestations médicales + exportations
            'gerer_facturation',          // Facturation prestataires
            'Gérer la facturation',       // Compatibilité facturation
            'gerer_entreprises',          // Vue d'ensemble des entreprises adhérentes
            'gerer_salaries',             // Vue d'ensemble des participants, génération et téléchargement des cartes assurés
            'gerer_prestataires',         // Vue d'ensemble des praticiens et pharmacies
            'gerer_cotisations',          // Vue d'ensemble des cotisations et recouvrements
            'consulter_dossier_medical',   // Consultation et historique médical
            'gerer_pieces_jointes',       // Gestion et téléchargement des documents GED
            'gerer_medias',               // Gestion de la médiathèque
            'gerer_parametres_couverture',// Consultation et barèmes de couverture
            'voir_audit',                 // Consultation du journal d'audit et export
        ];

        $permissions = DB::table('permission')
            ->whereIn('code', $supervisorPermissions)
            ->orWhereIn('libelle', $supervisorPermissions)
            ->pluck('id_permission');

        foreach ($permissions as $permId) {
            $exists = DB::table('role_permission')
                ->where('id_role', $roleId)
                ->where('id_permission', $permId)
                ->exists();

            if (!$exists) {
                DB::table('role_permission')->insert([
                    'id_role' => $roleId,
                    'id_permission' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Cache::forget('permissions_all');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = DB::table('role')
            ->where('code', 'superviseur')
            ->orWhere('libelle', 'Superviseur')
            ->first();

        if ($role) {
            DB::table('role_permission')->where('id_role', $role->id_role)->delete();
            DB::table('role')->where('id_role', $role->id_role)->delete();
        }

        Cache::forget('permissions_all');
    }
};
