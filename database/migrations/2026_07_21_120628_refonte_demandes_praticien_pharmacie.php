<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table Praticien
        if (!Schema::hasTable('praticien')) {
            Schema::create('praticien', function (Blueprint $table) {
                $table->id('id_praticien');
                $table->string('code_praticien', 50)->unique();
                $table->string('nom');
                $table->string('adresse')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('specialite')->nullable();
                $table->timestamps();
            });
        }

        // 2. Table Pharmacie
        if (!Schema::hasTable('pharmacie')) {
            Schema::create('pharmacie', function (Blueprint $table) {
                $table->id('id_pharmacie');
                $table->string('code_pharmacie', 50)->unique();
                $table->string('nom');
                $table->string('adresse')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('nom_pharmacien')->nullable();
                $table->timestamps();
            });
        }

        // 3. Modifier la table Demande
        Schema::table('demande', function (Blueprint $table) {
            if (Schema::hasColumn('demande', 'id_prestataire')) {
                // Dans certains environnements, dropForeign peut échouer si la clé n'existe pas ou porte un autre nom.
                // On essaie de la supprimer, sinon on l'ignore.
                try {
                    $table->dropForeign(['id_prestataire']);
                } catch (\Exception $e) {}
                $table->dropColumn('id_prestataire');
            }

            if (!Schema::hasColumn('demande', 'id_praticien')) {
                $table->unsignedBigInteger('id_praticien')->nullable()->after('id_ayant_droit');
                $table->foreign('id_praticien')->references('id_praticien')->on('praticien')->onDelete('restrict');
            }

            if (!Schema::hasColumn('demande', 'id_pharmacie')) {
                $table->unsignedBigInteger('id_pharmacie')->nullable()->after('id_praticien');
                $table->foreign('id_pharmacie')->references('id_pharmacie')->on('pharmacie')->onDelete('restrict');
            }

            if (!Schema::hasColumn('demande', 'id_type_prestation')) {
                $table->unsignedBigInteger('id_type_prestation')->nullable()->after('id_pharmacie');
                $table->foreign('id_type_prestation')->references('id_type_prestation')->on('type_prestation')->onDelete('restrict');
            }
            
            if (!Schema::hasColumn('demande', 'numero_demande')) {
                $table->string('numero_demande', 50)->nullable()->unique()->after('id_demande');
            }
        });
    }

    public function down(): void
    {
        Schema::table('demande', function (Blueprint $table) {
            if (Schema::hasColumn('demande', 'id_praticien')) {
                $table->dropForeign(['id_praticien']);
                $table->dropColumn('id_praticien');
            }
            if (Schema::hasColumn('demande', 'id_pharmacie')) {
                $table->dropForeign(['id_pharmacie']);
                $table->dropColumn('id_pharmacie');
            }
            if (Schema::hasColumn('demande', 'id_type_prestation')) {
                $table->dropForeign(['id_type_prestation']);
                $table->dropColumn('id_type_prestation');
            }
            if (Schema::hasColumn('demande', 'numero_demande')) {
                $table->dropColumn('numero_demande');
            }
            if (!Schema::hasColumn('demande', 'id_prestataire')) {
                $table->unsignedBigInteger('id_prestataire')->nullable();
                $table->foreign('id_prestataire')->references('id_prestataire')->on('prestataire')->onDelete('restrict');
            }
        });

        Schema::dropIfExists('pharmacie');
        Schema::dropIfExists('praticien');
    }
};
