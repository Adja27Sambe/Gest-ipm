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
        Schema::table('cotisation', function (Blueprint $table) {
            $table->dropForeign(['id_salarie']);
            $table->dropIndex(['id_salarie']);
            $table->dropColumn('id_salarie');
            $table->dropColumn('salaire_base');
            
            $table->unsignedBigInteger('id_entreprise');
            $table->foreign('id_entreprise')->references('id_entreprise')->on('entreprise')->onDelete('cascade');
            $table->decimal('masse_salariale', 12, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotisation', function (Blueprint $table) {
            $table->dropForeign(['id_entreprise']);
            $table->dropColumn('id_entreprise');
            $table->dropColumn('masse_salariale');
            
            $table->unsignedBigInteger('id_salarie');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('restrict');
            $table->decimal('salaire_base', 12)->nullable();
            
            $table->index('id_salarie');
        });
    }
};
