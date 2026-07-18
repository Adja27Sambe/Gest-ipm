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
        Schema::table('cotisation', function (Blueprint $table) {
            $table->unsignedBigInteger('id_salarie')->nullable()->after('id_entreprise');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('cascade');
            
            $table->decimal('salaire_base', 12, 2)->nullable()->after('masse_salariale');
        });

        // Rendre id_entreprise nullable
        DB::statement('ALTER TABLE cotisation MODIFY id_entreprise BIGINT UNSIGNED NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotisation', function (Blueprint $table) {
            $table->dropForeign(['id_salarie']);
            $table->dropColumn('id_salarie');
            $table->dropColumn('salaire_base');
        });

        DB::statement('ALTER TABLE cotisation MODIFY id_entreprise BIGINT UNSIGNED NOT NULL;');
    }
};
