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
        Schema::table('prestataire', function (Blueprint $table) {
            if (!Schema::hasColumn('prestataire', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('email');
            }
            if (!Schema::hasColumn('prestataire', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            
            // Si la table ne possédait pas les timestamps
            if (!Schema::hasColumn('prestataire', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('type_prestataire', function (Blueprint $table) {
            if (!Schema::hasColumn('type_prestataire', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('convention', function (Blueprint $table) {
            if (!Schema::hasColumn('convention', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestataire', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
