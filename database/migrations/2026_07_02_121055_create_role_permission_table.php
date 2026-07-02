<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permission', function (Blueprint $table) {
            $table->integer('id_role')->nullable();
            $table->foreign('id_role')->references('id_role')->on('role')->onDelete('cascade');
            $table->integer('id_permission')->nullable();
            $table->foreign('id_permission')->references('id_permission')->on('permission')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {{
        Schema::dropIfExists('{table_name}');
    }
}};
