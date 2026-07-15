<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prestataire', function (Blueprint $table) {
            $table->id('id_prestataire');
            $table->string('nom', 100);
            $table->string('specialite', 100)->nullable();
            $table->text('adresse')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('id_type');
            $table->foreign('id_type')->references('id_type')->on('type_prestataire')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
