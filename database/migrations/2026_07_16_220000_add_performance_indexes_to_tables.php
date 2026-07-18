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
        // Table demande
        Schema::table('demande', function (Blueprint $table) {
            $table->index('date_demande');
            $table->index('statut');
            $table->index('id_salarie');
        });

        // Table prestation
        Schema::table('prestation', function (Blueprint $table) {
            $table->index('date_prestation');
            $table->index('id_prestataire');
            $table->index('id_demande');
        });

        // Table cotisation
        Schema::table('cotisation', function (Blueprint $table) {
            $table->index('periode');
            $table->index('statut');
            $table->index('id_salarie');
        });

        // Table facture
        Schema::table('facture', function (Blueprint $table) {
            $table->index('statut_paiement');
            $table->index('date_facture');
        });

        // Table historique_mouvement
        Schema::table('historique_mouvement', function (Blueprint $table) {
            $table->index('action');
            $table->index('module');
            $table->index('id_utilisateur');
            $table->index('date_heure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande', function (Blueprint $table) {
            $table->dropIndex(['date_demande']);
            $table->dropIndex(['statut']);
            $table->dropIndex(['id_salarie']);
        });

        Schema::table('prestation', function (Blueprint $table) {
            $table->dropIndex(['date_prestation']);
            $table->dropIndex(['id_prestataire']);
            $table->dropIndex(['id_demande']);
        });

        Schema::table('cotisation', function (Blueprint $table) {
            $table->dropIndex(['periode']);
            $table->dropIndex(['statut']);
            $table->dropIndex(['id_salarie']);
        });

        Schema::table('facture', function (Blueprint $table) {
            $table->dropIndex(['statut_paiement']);
            $table->dropIndex(['date_facture']);
        });

        Schema::table('historique_mouvement', function (Blueprint $table) {
            $table->dropIndex(['action']);
            $table->dropIndex(['module']);
            $table->dropIndex(['id_utilisateur']);
            $table->dropIndex(['date_heure']);
        });
    }
};
