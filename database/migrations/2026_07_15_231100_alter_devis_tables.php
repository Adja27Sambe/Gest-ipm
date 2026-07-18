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
        Schema::table('devis', function (Blueprint $table) {
            if (!Schema::hasColumn('devis', 'beneficiaire_type')) {
                // Par défaut, on dira que les existants sont des salariés pour éviter les problèmes, ou nullable
                $table->string('beneficiaire_type')->nullable()->after('id_beneficiaire');
            }
            if (!Schema::hasColumn('devis', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('validation_devis', function (Blueprint $table) {
            if (!Schema::hasColumn('validation_devis', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            if (Schema::hasColumn('devis', 'beneficiaire_type')) {
                $table->dropColumn('beneficiaire_type');
            }
            // drop timestamps si nécessaire, mais on ne les enlève généralement pas en down.
        });
    }
};
