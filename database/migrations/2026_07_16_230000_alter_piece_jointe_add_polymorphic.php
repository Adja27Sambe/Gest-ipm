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
        Schema::table('piece_jointe', function (Blueprint $table) {
            $table->unsignedBigInteger('attachable_id')->nullable()->after('id_piece');
            $table->string('attachable_type')->nullable()->after('attachable_id');
            
            $table->index(['attachable_type', 'attachable_id'], 'piece_jointe_attachable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piece_jointe', function (Blueprint $table) {
            $table->dropIndex('piece_jointe_attachable_index');
            $table->dropColumn(['attachable_type', 'attachable_id']);
        });
    }
};
