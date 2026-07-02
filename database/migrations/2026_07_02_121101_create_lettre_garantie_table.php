<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lettre_garantie', function (Blueprint $table) {
            $table->id('id_lettre');
            $table->string('numero_lettre', 50)->unique();
            $table->date('date_emission')->nullable();
            $table->char('taux_prise_charge', 5)->nullable();
            $table->date('date_validite')->nullable();
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
