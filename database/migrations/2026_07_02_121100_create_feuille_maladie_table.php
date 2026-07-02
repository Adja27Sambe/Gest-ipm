<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feuille_maladie', function (Blueprint $table) {
            $table->id('id_feuille');
            $table->string('numero_feuille', 50)->unique();
            $table->date('date_emission')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('observations')->nullable();
            $table->integer('id_demande')->unique();
            $table->foreign('id_demande')->references('id_demande')->on('demande')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
