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
        if (Schema::hasTable('parametre_couverture') && Schema::hasColumn('parametre_couverture', 'ticket_moderateur')) {
            // Étendre la colonne ticket_moderateur de decimal(5,2) à decimal(12,2)
            // pour permettre le stockage de montants en FCFA (ex: 4 800 FCFA, 50 000 FCFA)
            DB::statement("ALTER TABLE `parametre_couverture` MODIFY `ticket_moderateur` DECIMAL(12, 2) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('parametre_couverture') && Schema::hasColumn('parametre_couverture', 'ticket_moderateur')) {
            DB::statement("ALTER TABLE `parametre_couverture` MODIFY `ticket_moderateur` DECIMAL(5, 2) NULL");
        }
    }
};
