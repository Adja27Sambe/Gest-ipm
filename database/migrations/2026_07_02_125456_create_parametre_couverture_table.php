<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parametre_couverture', function (Blueprint $table) {
            $table->id('id_parametre');
            $table->unsignedBigInteger('id_type_prestation');
            $table->foreign('id_type_prestation')->references('id_type_prestation')->on('type_prestation')->onDelete('cascade');
            $table->char('taux_prise_charge', 5)->nullable();
            $table->decimal('plafond_annuel', 12)->nullable();
            $table->decimal('plafond_par_acte', 12)->nullable();
            $table->decimal('ticket_moderateur', 5)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
