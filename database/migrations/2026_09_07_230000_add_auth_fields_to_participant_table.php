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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `PARTICIPANT` MODIFY COLUMN `CODE_PARTICIPANT` INT NULL DEFAULT NULL");
        \Illuminate\Support\Facades\DB::statement("UPDATE `PARTICIPANT` SET `CODE_PARTICIPANT` = IDPARTICIPANT WHERE `CODE_PARTICIPANT` = 0 OR `CODE_PARTICIPANT` IS NULL");

        Schema::table('PARTICIPANT', function (Blueprint $table) {
            if (!Schema::hasColumn('PARTICIPANT', 'code_securite')) {
                $table->string('code_securite', 255)->nullable()->after('MATRICULE');
            }
            if (!Schema::hasColumn('PARTICIPANT', 'remember_token')) {
                $table->rememberToken()->nullable()->after('code_securite');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('PARTICIPANT', function (Blueprint $table) {
            if (Schema::hasColumn('PARTICIPANT', 'code_securite')) {
                $table->dropColumn('code_securite');
            }
            if (Schema::hasColumn('PARTICIPANT', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};
