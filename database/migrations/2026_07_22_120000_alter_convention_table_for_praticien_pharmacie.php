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
        Schema::table('convention', function (Blueprint $table) {
            // Supprimer l'ancienne colonne id_prestataire si elle existe
            if (Schema::hasColumn('convention', 'id_prestataire')) {
                try {
                    $table->dropForeign(['id_prestataire']);
                } catch (\Exception $e) {}
                $table->dropColumn('id_prestataire');
            }

            // Ajouter id_praticien
            if (!Schema::hasColumn('convention', 'id_praticien')) {
                $table->unsignedBigInteger('id_praticien')->nullable()->after('id_convention');
                $table->foreign('id_praticien')->references('id_praticien')->on('praticien')->onDelete('cascade');
            }

            // Ajouter id_pharmacie
            if (!Schema::hasColumn('convention', 'id_pharmacie')) {
                $table->unsignedBigInteger('id_pharmacie')->nullable()->after('id_praticien');
                $table->foreign('id_pharmacie')->references('id_pharmacie')->on('pharmacie')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convention', function (Blueprint $table) {
            if (Schema::hasColumn('convention', 'id_praticien')) {
                $table->dropForeign(['id_praticien']);
                $table->dropColumn('id_praticien');
            }

            if (Schema::hasColumn('convention', 'id_pharmacie')) {
                $table->dropForeign(['id_pharmacie']);
                $table->dropColumn('id_pharmacie');
            }

            // Restaurer id_prestataire (sans garantie que les anciennes données reviennent)
            if (!Schema::hasColumn('convention', 'id_prestataire')) {
                $table->unsignedBigInteger('id_prestataire')->nullable();
            }
        });
    }
};
