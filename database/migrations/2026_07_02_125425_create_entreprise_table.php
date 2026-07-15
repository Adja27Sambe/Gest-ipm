<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('entreprise', function (Blueprint $table) {
            $table->id('id_entreprise');
            $table->string('code_adherent', 50)->nullable();
            $table->string('code_comptable', 50)->nullable();
            $table->string('raison_sociale', 255);
            $table->text('adresse')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('date_adhesion')->nullable();
            $table->string('statut', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
