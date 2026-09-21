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
        $officialActes = [
            // 1. Médecine Générale : 75%
            [
                'id' => 1,
                'libelle' => 'Médecine Générale',
                'taux' => 75,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'CMG',
            ],
            // 2. Spécialité Médicale : 70%
            [
                'id' => 2,
                'libelle' => 'Spécialité Médicale',
                'taux' => 70,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'CSP',
            ],
            // 3. Consultation + Soins Dentaires : 50%
            [
                'id' => 3,
                'libelle' => 'Consultation + Soins Dentaires',
                'taux' => 50,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'DEN',
            ],
            // 4. Consultation Ophtalmologie : 50%
            [
                'id' => 4,
                'libelle' => 'Consultation Ophtalmologie',
                'taux' => 50,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'OPH',
            ],
            // 5. Analyses Médicales : 65% (détaché)
            [
                'id' => 5,
                'libelle' => 'Analyses Médicales',
                'taux' => 65,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'BIO',
            ],
            // 6. Radiologie : 65% (détaché)
            [
                'id' => 6,
                'libelle' => 'Radiologie',
                'taux' => 65,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'RAD',
            ],
            // 7. Pharmacie : 75%
            [
                'id' => 7,
                'libelle' => 'Pharmacie',
                'taux' => 75,
                'plafond_acte' => null,
                'plafond_annuel' => null,
                'code' => 'PHA',
            ],
            // 8. Hospitalisation : 70% (20 000 FCFA plafond par jour)
            [
                'id' => 8,
                'libelle' => 'Hospitalisation',
                'taux' => 70,
                'plafond_acte' => 20000,
                'plafond_annuel' => null,
                'code' => 'HOS',
            ],
            // 9. Optique Médicale : 70% (30 000 FCFA plafond par paire de lunettes)
            [
                'id' => 9,
                'libelle' => 'Optique Médicale',
                'taux' => 70,
                'plafond_acte' => 30000,
                'plafond_annuel' => null,
                'code' => 'OPT',
            ],
            // 10. Maternité / Accouchement : 70% (50 000 FCFA plafond)
            [
                'id' => 10,
                'libelle' => 'Maternité / Accouchement',
                'taux' => 70,
                'plafond_acte' => 50000,
                'plafond_annuel' => null,
                'code' => 'MAT',
            ],
        ];

        $validIds = collect($officialActes)->pluck('id')->toArray();

        // 1. Nettoyage des anciens types surnuméraires non utilisés
        ParametreCouverture::whereNotIn('id_type_prestation', $validIds)->delete();
        TypePrestation::whereNotIn('id_type_prestation', $validIds)->delete();
        if (Schema::hasTable('Acte')) {
            DB::table('Acte')->whereNotIn('IDActe', $validIds)->delete();
        }

        // 2. Mise à jour / Création des types d'acte officiels
        foreach ($officialActes as $item) {
            $typePrestation = TypePrestation::updateOrCreate(
                ['id_type_prestation' => $item['id']],
                [
                    'libelle' => $item['libelle'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

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

            if (Schema::hasTable('Acte')) {
                DB::table('Acte')->updateOrInsert(
                    ['IDActe' => $item['id']],
                    [
                        'Acte' => substr($item['libelle'], 0, 50),
                        'Taux' => (float)$item['taux'],
                        'Plafond' => $item['plafond_acte'] ?? 0,
                        'CodeActe' => substr($item['code'], 0, 3),
                        'MONTANT' => $item['plafond_acte'] ?? 0,
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
        // Ne pas détruire en production pour préserver l'intégrité référentielle
    }
};
