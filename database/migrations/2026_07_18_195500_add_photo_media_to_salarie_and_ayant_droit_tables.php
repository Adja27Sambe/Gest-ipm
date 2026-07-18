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
        Schema::table('salarie', function (Blueprint $table) {
            $table->unsignedBigInteger('id_photo_media')->nullable()->after('id_entreprise');
            
            // $table->foreign('id_photo_media')->references('id_media')->on('media')->nullOnDelete();
        });

        Schema::table('ayant_droit', function (Blueprint $table) {
            $table->unsignedBigInteger('id_photo_media')->nullable()->after('id_salarie');
            
            // $table->foreign('id_photo_media')->references('id_media')->on('media')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salarie', function (Blueprint $table) {
            // $table->dropForeign(['id_photo_media']);
            $table->dropColumn('id_photo_media');
        });

        Schema::table('ayant_droit', function (Blueprint $table) {
            // $table->dropForeign(['id_photo_media']);
            $table->dropColumn('id_photo_media');
        });
    }
};
