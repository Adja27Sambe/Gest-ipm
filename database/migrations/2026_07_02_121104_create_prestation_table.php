<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prestation', function (Blueprint $table) {
            $table->id('id_prestation');
            $table->date('date_prestation')->nullable();
            $table->decimal('montant', 12)->nullable();
            $table->char('taux_prise_charge', 5)->nullable();
            $table->char('reste_a_charge', 12)->nullable();
            $table->integer('id_type_prestation');
            $table->foreign('id_type_prestation')->references('id_type_prestation')->on('type_prestation')->onDelete('restrict');
            $table->integer('id_prestataire');
            $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
            $table->integer('id_demande')->nullable();
            $table->foreign('id_demande')->references('id_demande')->on('demande')->onDelete('set');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
