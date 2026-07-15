<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cotisation', function (Blueprint $table) {
            $table->id('id_cotisation');
            $table->string('periode', 50)->nullable();
            $table->decimal('salaire_base', 12)->nullable();
            $table->decimal('taux', 5)->nullable();
            $table->decimal('montant', 12)->nullable();
            $table->date('date_paiement')->nullable();
            $table->string('statut', 50)->nullable();
            $table->unsignedBigInteger('id_salarie');
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
