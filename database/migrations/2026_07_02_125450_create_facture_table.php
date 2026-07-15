<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('facture', function (Blueprint $table) {
            $table->id('id_facture');
            $table->string('numero_facture', 50)->unique();
            $table->date('date_facture')->nullable();
            $table->decimal('montant', 12)->nullable();
            $table->string('statut_paiement', 50)->nullable();
            $table->unsignedBigInteger('id_prestataire');
            $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
