<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestation', function (Blueprint $table) {
            $table->unsignedBigInteger('id_type_prestation')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('prestation', function (Blueprint $table) {
            $table->unsignedBigInteger('id_type_prestation')->nullable(false)->change();
        });
    }
};
