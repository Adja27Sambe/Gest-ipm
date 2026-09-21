<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fks = [
            'ayant_droit' => ['id_salarie'],
            'carte_assure' => ['id_salarie'],
            'cotisation' => ['id_salarie', 'id_entreprise'],
            'demande' => ['id_salarie', 'id_pharmacie', 'id_praticien'],
            'convention' => ['id_pharmacie', 'id_praticien'],
            'facture' => ['id_pharmacie', 'id_praticien'],
            'prestation' => ['id_pharmacie', 'id_praticien'],
            'relance' => ['id_entreprise'],
        ];

        foreach ($fks as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($columns as $column) {
                try {
                    $fkName = "{$tableName}_{$column}_foreign";
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkName}`");
                } catch (\Exception $e) {
                    // Ignore if does not exist
                }
            }
        }
    }

    public function down(): void
    {
    }
};
