<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('demande', function (Blueprint $table) {
            $table->id('id_demande');
            $table->date('date_demande');
            $table->text('motif')->nullable();
            $table->string('statut', 50)->nullable();
            $table->unsignedBigInteger('id_type_demande');
            $table->foreign('id_type_demande')->references('id_type_demande')->on('type_demande')->onDelete('restrict');
            $table->unsignedBigInteger('id_salarie');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('restrict');
            $table->unsignedBigInteger('id_ayant_droit')->nullable();
            $table->foreign('id_ayant_droit')->references('id_ayant_droit')->on('ayant_droit')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
