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
        Schema::table('historique_medical', function (Blueprint $table) {
            if (!Schema::hasColumn('historique_medical', 'beneficiaire_type')) {
                $table->string('beneficiaire_type')->nullable()->after('id_beneficiaire');
            }
            if (!Schema::hasColumn('historique_medical', 'id_pathologie')) {
                // Permet de lier formellement le diagnostic à la table PATHOLOGIE
                $table->unsignedBigInteger('id_pathologie')->nullable();
                // Assumant que le type est INT dans la table pathologie. S'il n'est pas unsigned on peut avoir un problème de cast, mais ignorons pour l'instant la FK stricte, gardons-le comme entier de référence.
            }
            if (!Schema::hasColumn('historique_medical', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('prescription', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historique_medical', function (Blueprint $table) {
            if (Schema::hasColumn('historique_medical', 'beneficiaire_type')) {
                $table->dropColumn('beneficiaire_type');
            }
            if (Schema::hasColumn('historique_medical', 'id_pathologie')) {
                $table->dropColumn('id_pathologie');
            }
        });
    }
};
