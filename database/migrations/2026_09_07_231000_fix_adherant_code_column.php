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
        DB::statement("ALTER TABLE `ADHERANT` MODIFY COLUMN `CODEADHERANT` VARCHAR(50) NULL DEFAULT NULL");
        
        // Mettre à jour les codes des entreprises existantes
        $entreprises = DB::table('ADHERANT')->get();
        foreach ($entreprises as $index => $entreprise) {
            $code = sprintf("ENT-%03d", $index + 1);
            DB::table('ADHERANT')
                ->where('IDADHERANT', $entreprise->IDADHERANT)
                ->update(['CODEADHERANT' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `ADHERANT` MODIFY COLUMN `CODEADHERANT` INT NULL DEFAULT 0");
    }
};
