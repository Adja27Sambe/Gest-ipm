<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('paiement_prestataire', function (Blueprint $table) {
            $table->id('id_paiement');
            $table->date('date_paiement')->nullable();
            $table->decimal('montant', 12)->nullable();
            $table->string('mode_paiement', 50)->nullable();
            $table->string('reference', 100)->nullable();
            $table->unsignedBigInteger('id_facture');
            $table->foreign('id_facture')->references('id_facture')->on('facture')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
