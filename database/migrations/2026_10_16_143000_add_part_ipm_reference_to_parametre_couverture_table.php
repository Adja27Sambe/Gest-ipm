<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parametre_couverture', function (Blueprint $table) {
            if (!Schema::hasColumn('parametre_couverture', 'part_ipm_reference')) {
                $table->decimal('part_ipm_reference', 12, 2)->nullable()->after('montant_referentiel');
            }
            if (!Schema::hasColumn('parametre_couverture', 'id_type_acte')) {
                $table->unsignedBigInteger('id_type_acte')->nullable()->after('id_type_prestation');
                $table->foreign('id_type_acte')->references('id_type_acte')->on('type_acte')->nullOnDelete();
            }
        });

        // Remplissage automatique de la Part IPM de référence uniquement pour les paramètres de type MONTANT_REFERENTIEL :
        // Part IPM de référence = Montant référentiel × (Taux IPM / 100)
        DB::statement("
            UPDATE `parametre_couverture`
            SET `part_ipm_reference` = ROUND(`montant_referentiel` * (`taux_prise_charge` / 100), 2)
            WHERE `type_calcul` = 'MONTANT_REFERENTIEL'
              AND `montant_referentiel` IS NOT NULL
              AND `taux_prise_charge` IS NOT NULL
              AND `taux_prise_charge` > 0
              AND `part_ipm_reference` IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parametre_couverture', function (Blueprint $table) {
            if (Schema::hasColumn('parametre_couverture', 'id_type_acte')) {
                $table->dropForeign(['id_type_acte']);
                $table->dropColumn('id_type_acte');
            }
            if (Schema::hasColumn('parametre_couverture', 'part_ipm_reference')) {
                $table->dropColumn('part_ipm_reference');
            }
        });
    }
};
