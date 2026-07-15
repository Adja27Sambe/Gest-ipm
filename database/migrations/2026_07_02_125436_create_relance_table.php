<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('relance', function (Blueprint $table) {
            $table->id('id_relance');
            $table->date('date_relance');
            $table->integer('niveau_relance')->nullable();
            $table->text('commentaire')->nullable();
            $table->unsignedBigInteger('id_entreprise');
            $table->foreign('id_entreprise')->references('id_entreprise')->on('entreprise')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
