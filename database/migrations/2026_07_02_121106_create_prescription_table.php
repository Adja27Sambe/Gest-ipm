<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prescription', function (Blueprint $table) {
            $table->id('id_prescription');
            $table->string('medicament', 255);
            $table->text('posologie')->nullable();
            $table->string('duree', 50)->nullable();
            $table->integer('id_historique_medical');
            $table->foreign('id_historique_medical')->references('id_historique_medical')->on('historique_medical')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
