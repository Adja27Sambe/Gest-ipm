<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historique_medical', function (Blueprint $table) {
            $table->id('id_historique_medical');
            $table->date('date_consultation')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('traitement')->nullable();
            $table->text('observation')->nullable();
            $table->integer('id_beneficiaire');
            $table->integer('--')->nullable();
            $table->foreign('--')->references('id_prestataire')->on('prestataire')->onDelete('set');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
