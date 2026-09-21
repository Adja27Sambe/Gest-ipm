<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\TypePrestation;
use App\Models\ParametreCouverture;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $typesData = [
            // Consultations & Soins Médicaux
            [
                'id' => 1,
                'libelle' => 'Consultation Médecine Générale',
                'taux' => 80,
                'plafond_acte' => 15000,
                'plafond_annuel' => 150000,
                'code' => 'CMG',
                'categorie' => 'Consultations & Soins Courants',
            ],
            [
                'id' => 2,
                'libelle' => 'Consultation Spécialisée',
                'taux' => 80,
                'plafond_acte' => 25000,
                'plafond_annuel' => 200000,
                'code' => 'CSP',
                'categorie' => 'Consultations & Soins Courants',
            ],
            [
                'id' => 3,
                'libelle' => 'Visite Médicale à Domicile',
                'taux' => 80,
                'plafond_acte' => 20000,
                'plafond_annuel' => 100000,
                'code' => 'VMD',
                'categorie' => 'Consultations & Soins Courants',
            ],
            [
                'id' => 4,
                'libelle' => 'Soins Infirmiers & Actes Externes',
                'taux' => 80,
                'plafond_acte' => 20000,
                'plafond_annuel' => 100000,
                'code' => 'SIF',
                'categorie' => 'Consultations & Soins Courants',
            ],

            // Pharmacie & Optique
            [
                'id' => 5,
                'libelle' => 'Pharmacie & Médicaments (Bon de Commande)',
                'taux' => 80,
                'plafond_acte' => 60000,
                'plafond_annuel' => 350000,
                'code' => 'PHA',
                'categorie' => 'Pharmacie & Optique',
            ],
            [
                'id' => 6,
                'libelle' => 'Optique Médicale / Verres & Montures',
                'taux' => 70,
                'plafond_acte' => 70000,
                'plafond_annuel' => 150000,
                'code' => 'OPT',
                'categorie' => 'Pharmacie & Optique',
            ],

            // Examens & Diagnostics
            [
                'id' => 7,
                'libelle' => 'Analyses Médicales & Biologie',
                'taux' => 80,
                'plafond_acte' => 50000,
                'plafond_annuel' => 300000,
                'code' => 'BIO',
                'categorie' => 'Examens & Imagerie Médicale',
            ],
            [
                'id' => 8,
                'libelle' => 'Examens Radiologiques & Échographie',
                'taux' => 80,
                'plafond_acte' => 80000,
                'plafond_annuel' => 350000,
                'code' => 'RAD',
                'categorie' => 'Examens & Imagerie Médicale',
            ],
            [
                'id' => 9,
                'libelle' => 'Scanner / IRM / Imagerie Spécialisée',
                'taux' => 80,
                'plafond_acte' => 150000,
                'plafond_annuel' => 450000,
                'code' => 'IRM',
                'categorie' => 'Examens & Imagerie Médicale',
            ],

            // Hospitalisation & Chirurgie
            [
                'id' => 10,
                'libelle' => 'Hospitalisation Médicale ou Chirurgicale',
                'taux' => 80,
                'plafond_acte' => 300000,
                'plafond_annuel' => 1500000,
                'code' => 'HOS',
                'categorie' => 'Hospitalisation & Interventions',
            ],
            [
                'id' => 11,
                'libelle' => 'Chirurgie Ambulatoire',
                'taux' => 80,
                'plafond_acte' => 150000,
                'plafond_annuel' => 600000,
                'code' => 'CHI',
                'categorie' => 'Hospitalisation & Interventions',
            ],
            [
                'id' => 12,
                'libelle' => 'Maternité & Accouchement',
                'taux' => 80,
                'plafond_acte' => 200000,
                'plafond_annuel' => 500000,
                'code' => 'MAT',
                'categorie' => 'Hospitalisation & Interventions',
            ],

            // Spécialités & Rééducation
            [
                'id' => 13,
                'libelle' => 'Soins Dentaires & Prothèses',
                'taux' => 70,
                'plafond_acte' => 80000,
                'plafond_annuel' => 300000,
                'code' => 'DEN',
                'categorie' => 'Dentaire & Rééducation',
            ],
            [
                'id' => 14,
                'libelle' => 'Kinésithérapie & Rééducation Fonctionnelle',
                'taux' => 80,
                'plafond_acte' => 35000,
                'plafond_annuel' => 150000,
                'code' => 'KIN',
                'categorie' => 'Dentaire & Rééducation',
            ],
        ];

        foreach ($typesData as $item) {
            // 1. Table type_prestation
            $typePrestation = TypePrestation::updateOrCreate(
                ['id_type_prestation' => $item['id']],
                [
                    'libelle' => $item['libelle'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // 2. Table parametre_couverture
            ParametreCouverture::updateOrCreate(
                ['id_type_prestation' => $typePrestation->id_type_prestation],
                [
                    'taux_prise_charge' => $item['taux'],
                    'plafond_par_acte' => $item['plafond_acte'],
                    'plafond_annuel' => $item['plafond_annuel'],
                    'ticket_moderateur' => 100 - $item['taux'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // 3. Sync legacy Acte table if exists
            if (Schema::hasTable('Acte')) {
                DB::table('Acte')->updateOrInsert(
                    ['IDActe' => $item['id']],
                    [
                        'Acte' => substr($item['libelle'], 0, 50),
                        'Taux' => (float)$item['taux'],
                        'Plafond' => $item['plafond_acte'],
                        'CodeActe' => $item['code'],
                        'MONTANT' => $item['plafond_acte'],
                        'Forfait' => 0,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne pas détruire en production pour éviter les orphelins de prestations
    }
};
