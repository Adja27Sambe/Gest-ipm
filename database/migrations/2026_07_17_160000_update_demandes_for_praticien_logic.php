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
        Schema::table('demande', function (Blueprint $table) {
            if (Schema::hasColumn('demande', 'id_type_prestation')) {
                $table->dropForeign(['id_type_prestation']);
                $table->dropColumn('id_type_prestation');
            }
            if (!Schema::hasColumn('demande', 'id_prestataire')) {
                $table->unsignedBigInteger('id_prestataire')->nullable()->after('id_salarie');
                $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
            }
        });

        Schema::table('bon_commande', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_commande', 'nombre_articles')) {
                $table->integer('nombre_articles')->default(1)->after('id_demande');
            }
        });

        Schema::table('lettre_garantie', function (Blueprint $table) {
            if (!Schema::hasColumn('lettre_garantie', 'choix_acte')) {
                $table->string('choix_acte')->nullable()->after('id_demande');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande', function (Blueprint $table) {
            if (Schema::hasColumn('demande', 'id_prestataire')) {
                $table->dropForeign(['id_prestataire']);
                $table->dropColumn('id_prestataire');
            }
            if (!Schema::hasColumn('demande', 'id_type_prestation')) {
                $table->unsignedBigInteger('id_type_prestation')->nullable();
                $table->foreign('id_type_prestation')->references('id_type_prestation')->on('type_prestation')->onDelete('set null');
            }
        });

        Schema::table('bon_commande', function (Blueprint $table) {
            if (Schema::hasColumn('bon_commande', 'nombre_articles')) {
                $table->dropColumn('nombre_articles');
            }
        });

        Schema::table('lettre_garantie', function (Blueprint $table) {
            if (Schema::hasColumn('lettre_garantie', 'choix_acte')) {
                $table->dropColumn('choix_acte');
            }
        });
    }
};
