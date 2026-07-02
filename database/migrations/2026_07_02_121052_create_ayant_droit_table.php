<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ayant_droit', function (Blueprint $table) {
            $table->id('id_ayant_droit');
            $table->string('nom', 100);
            $table->string('prenom', 100)->nullable();
            $table->string('lien_parente', 50)->nullable();
            $table->date('date_naissance')->nullable();
            $table->date('date_mariage')->nullable();
            $table->char('sexe', 1)->nullable();
            $table->string('statut', 50)->nullable();
            $table->integer('id_salarie');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
