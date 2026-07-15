<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('validation_devis', function (Blueprint $table) {
            $table->id('id_validation');
            $table->date('date_validation')->nullable();
            $table->string('decision', 50)->nullable();
            $table->text('commentaire')->nullable();
            $table->unsignedBigInteger('id_devis');
            $table->foreign('id_devis')->references('id_devis')->on('devis')->onDelete('cascade');
            $table->unsignedBigInteger('id_utilisateur');
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
