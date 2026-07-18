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
        Schema::create('media', function (Blueprint $table) {
            $table->id('id_media');
            $table->string('titre')->nullable();
            $table->string('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->string('type_mime');
            $table->unsignedBigInteger('taille');
            $table->text('texte_alternatif')->nullable();
            $table->unsignedBigInteger('id_utilisateur')->nullable();
            $table->timestamps();

            // Clé étrangère
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
