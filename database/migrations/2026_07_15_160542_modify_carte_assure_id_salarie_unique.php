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
        Schema::table('carte_assure', function (Blueprint $table) {
            $table->dropForeign(['id_salarie']);
            $table->dropUnique(['id_salarie']);
            $table->index('id_salarie');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carte_assure', function (Blueprint $table) {
            $table->dropForeign(['id_salarie']);
            $table->dropIndex(['id_salarie']);
            $table->unique('id_salarie');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('cascade');
        });
    }
};
