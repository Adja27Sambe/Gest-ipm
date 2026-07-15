<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salarie', function (Blueprint $table) {
            $table->id('id_salarie');
            $table->string('matricule', 50)->nullable();
            $table->string('nom', 100);
            $table->string('prenom', 100)->nullable();
            $table->date('date_naissance')->nullable();
            $table->char('sexe', 1)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->text('adresse')->nullable();
            $table->decimal('salaire', 12)->nullable();
            $table->date('date_embauche')->nullable();
            $table->string('statut', 50)->nullable();
            $table->unsignedBigInteger('id_entreprise');
            $table->foreign('id_entreprise')->references('id_entreprise')->on('entreprise')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
