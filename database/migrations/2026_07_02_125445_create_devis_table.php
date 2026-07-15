<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id('id_devis');
            $table->date('date_devis')->nullable();
            $table->text('description')->nullable();
            $table->decimal('montant', 12)->nullable();
            $table->string('statut', 50)->nullable();
            $table->unsignedBigInteger('id_prestataire');
            $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
            $table->unsignedBigInteger('id_beneficiaire');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
