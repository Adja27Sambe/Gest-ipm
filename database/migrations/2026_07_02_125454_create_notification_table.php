<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id('id_notification');
            $table->string('titre', 255);
            $table->text('message')->nullable();
            $table->date('date_envoi')->nullable();
            $table->boolean('lu')->nullable()->default(false);
            $table->string('canal', 50)->nullable();
            $table->unsignedBigInteger('id_utilisateur');
            $table->foreign('id_utilisateur')->references('id_utilisateur')->on('utilisateur')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
