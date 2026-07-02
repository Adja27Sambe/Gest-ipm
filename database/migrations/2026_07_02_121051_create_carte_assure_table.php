<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carte_assure', function (Blueprint $table) {
            $table->id('id_carte');
            $table->string('numero_carte', 50)->unique();
            $table->string('matricule', 50)->nullable();
            $table->date('date_emission')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('statut', 50)->nullable();
            $table->integer('id_salarie')->unique();
            $table->foreign('id_salarie')->references('id_salarie')->on('salarie')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
