<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historique_mouvement', function (Blueprint $table) {
            $table->id('id_historique');
            $table->date('date_heure')->nullable();
            $table->string('module', 100)->nullable();
            $table->string('action', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('adresse_ip', 45)->nullable();
            $table->text('ancienne_valeur')->nullable();
            $table->text('nouvelle_valeur')->nullable();
            $table->integer('id_utilisateur')->nullable();
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('set');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
