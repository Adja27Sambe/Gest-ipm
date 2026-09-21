<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enrichissement de la table 'permission'
        Schema::table('permission', function (Blueprint $table) {
            if (!Schema::hasColumn('permission', 'code')) {
                $table->string('code', 100)->nullable()->unique()->after('libelle');
            }
            if (!Schema::hasColumn('permission', 'categorie')) {
                $table->string('categorie', 100)->nullable()->after('code');
            }
            if (!Schema::hasColumn('permission', 'description')) {
                $table->text('description')->nullable()->after('categorie');
            }
        });

        // 2. Enrichissement de la table 'role'
        Schema::table('role', function (Blueprint $table) {
            if (!Schema::hasColumn('role', 'code')) {
                $table->string('code', 100)->nullable()->after('libelle');
            }
            if (!Schema::hasColumn('role', 'categorie')) {
                $table->string('categorie', 100)->nullable()->after('code');
            }
            if (!Schema::hasColumn('role', 'description')) {
                $table->text('description')->nullable()->after('categorie');
            }
        });

        // 3. Définition du catalogue exhaustif des permissions par fonctionnalité
        $permissionsCatalog = [
            // Module 1 : Prises en Charge & Demandes
            [
                'code' => 'gerer_demandes',
                'libelle' => 'Gérer les prises en charge',
                'categorie' => 'Prises en Charge & Demandes',
                'description' => 'Création, modification et consultation des demandes de prise en charge, bons et feuilles de maladie.'
            ],
            [
                'code' => 'valider_demandes',
                'libelle' => 'Valider les prises en charge',
                'categorie' => 'Prises en Charge & Demandes',
                'description' => 'Approbation ou rejet formel des demandes de prise en charge soumises.'
            ],

            // Module 2 : Prestations & Actes Médicaux
            [
                'code' => 'gerer_prestations',
                'libelle' => 'Gérer les prestations médicales',
                'categorie' => 'Prestations Médicales',
                'description' => 'Saisie, mise à jour, suivi des quotas et plafonds, et export des actes médicaux.'
            ],

            // Module 3 : Facturation Prestataires
            [
                'code' => 'gerer_facturation',
                'libelle' => 'Gérer la facturation prestataires',
                'categorie' => 'Facturation & Règlements',
                'description' => 'Réception des factures des prestataires de santé, vérification et liquidation.'
            ],
            [
                'code' => 'Gérer la facturation', // Alias exact pour le middleware existant
                'libelle' => 'Gérer la facturation (Compatibilité)',
                'categorie' => 'Facturation & Règlements',
                'description' => 'Autorisation globale sur le module facturation (alias système).'
            ],

            // Module 4 : Adhérents & Entreprises
            [
                'code' => 'gerer_entreprises',
                'libelle' => 'Gérer les entreprises adhérentes',
                'categorie' => 'Adhérents & Entreprises',
                'description' => 'Création, modification et gestion des entreprises partenaires et adhérentes.'
            ],

            // Module 5 : Salariés & Participants
            [
                'code' => 'gerer_salaries',
                'libelle' => 'Gérer les participants & salariés',
                'categorie' => 'Participants & Salariés',
                'description' => 'Gestion des salariés, ayants droit rattachés et génération des cartes assurés.'
            ],

            // Module 6 : Réseau Médical (Praticiens & Pharmacies)
            [
                'code' => 'gerer_prestataires',
                'libelle' => 'Gérer le réseau médical & conventions',
                'categorie' => 'Réseau Médical & Prestataires',
                'description' => 'Gestion des praticiens, cliniques, pharmacies et conventions tarifaires agréées.'
            ],

            // Module 7 : Cotisations IPM
            [
                'code' => 'gerer_cotisations',
                'libelle' => 'Gérer les cotisations',
                'categorie' => 'Cotisations & Recouvrement',
                'description' => 'Appels de cotisations, enregistrement des règlements des entreprises et historique.'
            ],

            // Module 8 : Dossier Médical
            [
                'code' => 'consulter_dossier_medical',
                'libelle' => 'Consulter le dossier médical',
                'categorie' => 'Dossier Médical',
                'description' => 'Accès confidentiel à l’historique médical des demandes et pathologies du patient.'
            ],

            // Module 9 : Gestion Documentaire & Médias
            [
                'code' => 'gerer_pieces_jointes',
                'libelle' => 'Gérer les pièces jointes',
                'categorie' => 'Documents & Médias',
                'description' => 'Téléversement, consultation et suppression des justificatifs et documents scannés.'
            ],
            [
                'code' => 'gerer_medias',
                'libelle' => 'Gérer la médiathèque',
                'categorie' => 'Documents & Médias',
                'description' => 'Gestion des photos de profil, logos et fichiers multimédias.'
            ],

            // Module 10 : Paramètres de Couverture
            [
                'code' => 'gerer_parametres_couverture',
                'libelle' => 'Gérer les paramètres de couverture',
                'categorie' => 'Paramétrage & Plafonds',
                'description' => 'Configuration des taux de prise en charge et des plafonds annuels par type d’acte.'
            ],

            // Module 11 : Audit & Journal
            [
                'code' => 'voir_audit',
                'libelle' => 'Consulter le journal d’audit',
                'categorie' => 'Sécurité & Audit',
                'description' => 'Traçabilité et consultation de l’ensemble des opérations utilisateurs dans le système.'
            ],

            // Module 12 : Administration & Rôles
            [
                'code' => 'gerer_roles',
                'libelle' => 'Gérer les rôles et utilisateurs',
                'categorie' => 'Administration Système',
                'description' => 'Création des comptes, attribution des profils et gestion des permissions.'
            ]
        ];

        $now = now();
        $permissionIdMap = [];

        foreach ($permissionsCatalog as $perm) {
            $existing = DB::table('permission')
                ->where('code', $perm['code'])
                ->orWhere('libelle', $perm['libelle'])
                ->orWhere('libelle', $perm['code'])
                ->first();

            if ($existing) {
                DB::table('permission')
                    ->where('id_permission', $existing->id_permission)
                    ->update([
                        'code' => $perm['code'],
                        'libelle' => $perm['libelle'],
                        'categorie' => $perm['categorie'],
                        'description' => $perm['description'],
                        'updated_at' => $now
                    ]);
                $permissionIdMap[$perm['code']] = $existing->id_permission;
            } else {
                $id = DB::table('permission')->insertGetId([
                    'code' => $perm['code'],
                    'libelle' => $perm['libelle'],
                    'categorie' => $perm['categorie'],
                    'description' => $perm['description'],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                $permissionIdMap[$perm['code']] = $id;
            }
        }

        // 4. Définition des Rôles / Profils par Fonctionnalité
        $rolesCatalog = [
            [
                'code' => 'Administrateur',
                'libelle' => 'Administrateur',
                'categorie' => 'Système & Direction',
                'description' => 'Accès global et supervision intégrale de toutes les fonctionnalités de l’IPM.',
                'permissions' => array_keys($permissionIdMap) // TOUTES les permissions
            ],
            [
                'code' => 'role_demandes',
                'libelle' => 'Gestionnaire Prises en Charge',
                'categorie' => 'Soins & Demandes',
                'description' => 'Dédié au pôle accueil et prises en charge (création, validation et impression).',
                'permissions' => ['gerer_demandes', 'valider_demandes', 'gerer_pieces_jointes']
            ],
            [
                'code' => 'role_prestations',
                'libelle' => 'Agent Saisie Prestations',
                'categorie' => 'Soins & Prestations',
                'description' => 'Dédié à la saisie, tarification et suivi des actes et ordonnances médicales.',
                'permissions' => ['gerer_prestations', 'gerer_demandes']
            ],
            [
                'code' => 'role_facturation',
                'libelle' => 'Gestionnaire Facturation',
                'categorie' => 'Finances & Comptabilité',
                'description' => 'Contrôle et validation des factures prestataires, enregistrement des règlements.',
                'permissions' => ['gerer_facturation', 'Gérer la facturation', 'gerer_cotisations', 'gerer_pieces_jointes']
            ],
            [
                'code' => 'role_entreprises',
                'libelle' => 'Gestionnaire Adhérents',
                'categorie' => 'Affiliations & Adhérents',
                'description' => 'Gestion des contrats d’entreprises adhérentes et des cotisations associées.',
                'permissions' => ['gerer_entreprises', 'gerer_cotisations', 'gerer_pieces_jointes']
            ],
            [
                'code' => 'role_salaries',
                'libelle' => 'Gestionnaire Bénéficiaires',
                'categorie' => 'Affiliations & Bénéficiaires',
                'description' => 'Gestion des salariés, conjoints, enfants et émission des cartes assurés.',
                'permissions' => ['gerer_salaries', 'gerer_pieces_jointes', 'gerer_medias']
            ],
            [
                'code' => 'role_prestataires',
                'libelle' => 'Gestionnaire Réseau Médical',
                'categorie' => 'Partenariats & Conventions',
                'description' => 'Gestion des prestataires de santé (médecins, cliniques, pharmacies) et conventions.',
                'permissions' => ['gerer_prestataires', 'gerer_parametres_couverture']
            ],
            [
                'code' => 'role_medical',
                'libelle' => 'Médecin Conseil',
                'categorie' => 'Service Médical',
                'description' => 'Contrôle médical confidentiel, validation des devis et historique médical.',
                'permissions' => ['consulter_dossier_medical', 'gerer_demandes', 'valider_demandes']
            ],
            [
                'code' => 'role_audit',
                'libelle' => 'Auditeur & Contrôle Interne',
                'categorie' => 'Audit & Conformité',
                'description' => 'Consultation en lecture seule du journal d’audit et des rapports de conformité.',
                'permissions' => ['voir_audit']
            ],
            [
                'code' => 'role_couverture',
                'libelle' => 'Gestionnaire des Couvertures',
                'categorie' => 'Paramétrage',
                'description' => 'Configuration des barèmes, plafonds et actes conventionnés.',
                'permissions' => ['gerer_parametres_couverture']
            ],
            [
                'code' => 'role_documents',
                'libelle' => 'Gestionnaire Documentaire',
                'categorie' => 'Documentation & Médias',
                'description' => 'Centralisation, archivage et classement des pièces jointes et médias.',
                'permissions' => ['gerer_pieces_jointes', 'gerer_medias']
            ]
        ];

        foreach ($rolesCatalog as $rData) {
            $role = DB::table('role')
                ->where('libelle', $rData['libelle'])
                ->orWhere('libelle', $rData['code'])
                ->first();

            if ($role) {
                DB::table('role')
                    ->where('id_role', $role->id_role)
                    ->update([
                        'code' => $rData['code'],
                        'libelle' => $rData['libelle'],
                        'categorie' => $rData['categorie'],
                        'description' => $rData['description'],
                        'updated_at' => $now
                    ]);
                $roleId = $role->id_role;
            } else {
                $roleId = DB::table('role')->insertGetId([
                    'code' => $rData['code'],
                    'libelle' => $rData['libelle'],
                    'categorie' => $rData['categorie'],
                    'description' => $rData['description'],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            // Synchroniser les permissions du rôle
            foreach ($rData['permissions'] as $permCode) {
                if (isset($permissionIdMap[$permCode])) {
                    $permId = $permissionIdMap[$permCode];
                    $exists = DB::table('role_permission')
                        ->where('id_role', $roleId)
                        ->where('id_permission', $permId)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permission')->insert([
                            'id_role' => $roleId,
                            'id_permission' => $permId,
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->dropColumn(['code', 'categorie', 'description']);
        });

        Schema::table('role', function (Blueprint $table) {
            $table->dropColumn(['code', 'categorie', 'description']);
        });
    }
};
