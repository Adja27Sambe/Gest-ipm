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
        Schema::table('PRATICIE', function (Blueprint $table) {
            $table->string('SPECIALITE', 255)->nullable()->change();
        });
        
        // Mettre à jour les anciennes données (1 -> Médecine Générale, etc.)
        \Illuminate\Support\Facades\DB::statement("
            UPDATE PRATICIE p
            JOIN SPECIALI s ON p.SPECIALITE = s.SPCLEUNIK
            SET p.SPECIALITE = s.Specialite
            WHERE p.SPECIALITE REGEXP '^[0-9]+$'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('PRATICIE', function (Blueprint $table) {
            // Revenir en int n'est pas sûr car les données sont devenues des strings
            // Mais pour la forme :
            $table->integer('SPECIALITE')->nullable()->change();
        });
    }
};
