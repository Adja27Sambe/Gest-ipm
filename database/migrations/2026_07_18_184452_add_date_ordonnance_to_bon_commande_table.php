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
        Schema::table('bon_commande', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_commande', 'date_ordonnance')) {
                $table->date('date_ordonnance')->nullable()->after('date_emission');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bon_commande', function (Blueprint $table) {
            if (Schema::hasColumn('bon_commande', 'date_ordonnance')) {
                $table->dropColumn('date_ordonnance');
            }
        });
    }
};
