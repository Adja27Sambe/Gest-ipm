<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parametre_couverture', function (Blueprint $table) {
            $table->string('type_calcul', 50)->nullable()->comment('POURCENTAGE, PLAFOND, FORFAIT, TARIF_JOURNALIER, EXCLUSION');
            $table->decimal('montant_plafond', 12, 2)->nullable();
            $table->decimal('montant_forfait', 12, 2)->nullable();
            $table->decimal('tarif_journalier', 12, 2)->nullable();
            $table->decimal('taux_participant', 5, 2)->nullable();
            $table->string('conditions_particulieres', 255)->nullable();
            $table->boolean('est_exclusion')->default(false);
            $table->string('motif_exclusion', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parametre_couverture', function (Blueprint $table) {
            $table->dropColumn([
                'type_calcul',
                'montant_plafond',
                'montant_forfait',
                'tarif_journalier',
                'taux_participant',
                'conditions_particulieres',
                'est_exclusion',
                'motif_exclusion'
            ]);
        });
    }
};
