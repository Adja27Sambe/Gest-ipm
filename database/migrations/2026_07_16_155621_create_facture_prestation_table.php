<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facture_prestation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_facture');
            $table->unsignedBigInteger('id_prestation');
            $table->timestamps();

            $table->foreign('id_facture')->references('id_facture')->on('facture')->onDelete('cascade');
            $table->foreign('id_prestation')->references('id_prestation')->on('prestation')->onDelete('cascade');
            
            $table->unique(['id_facture', 'id_prestation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facture_prestation');
    }
};
