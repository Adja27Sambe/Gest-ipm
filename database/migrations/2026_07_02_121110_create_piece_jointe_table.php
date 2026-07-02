<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('piece_jointe', function (Blueprint $table) {
            $table->id('id_piece');
            $table->string('nom_fichier', 255);
            $table->string('type_fichier', 50)->nullable();
            $table->text('chemin_fichier');
            $table->date('date_ajout')->nullable();
            $table->integer('id_categorie');
            $table->foreign('id_categorie')->references('id_categorie')->on('categorie_document')->onDelete('restrict');
            $table->integer('id_utilisateur')->nullable();
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('set');
            $table->text('lien_dossier')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
