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
        // Alteration de la table facture
        Schema::table('facture', function (Blueprint $table) {
            if (Schema::hasColumn('facture', 'id_prestataire')) {
                try {
                    $table->dropForeign(['id_prestataire']);
                } catch (\Exception $e) {}
                $table->dropColumn('id_prestataire');
            }

            if (!Schema::hasColumn('facture', 'id_praticien')) {
                $table->unsignedBigInteger('id_praticien')->nullable()->after('statut_paiement');
                $table->foreign('id_praticien')->references('id_praticien')->on('praticien')->onDelete('restrict');
            }

            if (!Schema::hasColumn('facture', 'id_pharmacie')) {
                $table->unsignedBigInteger('id_pharmacie')->nullable()->after('id_praticien');
                $table->foreign('id_pharmacie')->references('id_pharmacie')->on('pharmacie')->onDelete('restrict');
            }
        });

        // Alteration de la table prestation
        Schema::table('prestation', function (Blueprint $table) {
            if (Schema::hasColumn('prestation', 'id_prestataire')) {
                try {
                    $table->dropForeign(['id_prestataire']);
                } catch (\Exception $e) {}
                $table->dropColumn('id_prestataire');
            }

            if (!Schema::hasColumn('prestation', 'id_praticien')) {
                $table->unsignedBigInteger('id_praticien')->nullable()->after('id_type_prestation');
                $table->foreign('id_praticien')->references('id_praticien')->on('praticien')->onDelete('restrict');
            }

            if (!Schema::hasColumn('prestation', 'id_pharmacie')) {
                $table->unsignedBigInteger('id_pharmacie')->nullable()->after('id_praticien');
                $table->foreign('id_pharmacie')->references('id_pharmacie')->on('pharmacie')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestation', function (Blueprint $table) {
            $table->dropForeign(['id_praticien']);
            $table->dropColumn('id_praticien');
            $table->dropForeign(['id_pharmacie']);
            $table->dropColumn('id_pharmacie');
            $table->unsignedBigInteger('id_prestataire')->nullable();
            $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
        });

        Schema::table('facture', function (Blueprint $table) {
            $table->dropForeign(['id_praticien']);
            $table->dropColumn('id_praticien');
            $table->dropForeign(['id_pharmacie']);
            $table->dropColumn('id_pharmacie');
            $table->unsignedBigInteger('id_prestataire')->nullable();
            $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
        });
    }
};
