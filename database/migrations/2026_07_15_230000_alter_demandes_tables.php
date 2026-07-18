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
            if (!Schema::hasColumn('demande', 'id_type_prestation')) {
                $table->unsignedBigInteger('id_type_prestation')->nullable()->after('id_type_demande');
                $table->foreign('id_type_prestation')->references('id_type_prestation')->on('type_prestation')->onDelete('set null');
            }
        });

        foreach (['bon_commande', 'feuille_maladie', 'lettre_garantie', 'type_demande', 'type_prestation', 'parametre_couverture'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'created_at')) {
                        $table->timestamps();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande', function (Blueprint $table) {
            if (Schema::hasColumn('demande', 'id_type_prestation')) {
                $table->dropForeign(['id_type_prestation']);
                $table->dropColumn('id_type_prestation');
            }
        });
    }
};
