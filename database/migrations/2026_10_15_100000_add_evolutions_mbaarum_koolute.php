<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table ADHERANT & entreprise
        if (Schema::hasTable('ADHERANT')) {
            Schema::table('ADHERANT', function (Blueprint $table) {
                if (!Schema::hasColumn('ADHERANT', 'ACTIVITE')) {
                    $table->string('ACTIVITE', 255)->nullable()->after('ADHERANT');
                }
                if (!Schema::hasColumn('ADHERANT', 'DATE_ADHESION')) {
                    $table->date('DATE_ADHESION')->nullable()->after('ACTIVITE');
                }
                if (!Schema::hasColumn('ADHERANT', 'LETTRE_CLE')) {
                    $table->string('LETTRE_CLE', 10)->nullable()->after('CODEADHERANT');
                }
                if (!Schema::hasColumn('ADHERANT', 'PREFIXE')) {
                    $table->string('PREFIXE', 20)->nullable()->after('LETTRE_CLE');
                }
            });
        }

        if (Schema::hasTable('entreprise')) {
            Schema::table('entreprise', function (Blueprint $table) {
                if (!Schema::hasColumn('entreprise', 'activite')) {
                    $table->string('activite', 255)->nullable()->after('raison_sociale');
                }
                if (!Schema::hasColumn('entreprise', 'lettre_cle')) {
                    $table->string('lettre_cle', 10)->nullable()->after('code_adherent');
                }
                if (!Schema::hasColumn('entreprise', 'prefixe')) {
                    $table->string('prefixe', 20)->nullable()->after('lettre_cle');
                }
            });
        }

        // 2. Table PRATICIE & praticien
        if (Schema::hasTable('PRATICIE')) {
            Schema::table('PRATICIE', function (Blueprint $table) {
                if (!Schema::hasColumn('PRATICIE', 'A_CONVENTION')) {
                    $table->boolean('A_CONVENTION')->default(false)->after('COMPTE_COMPTABLE');
                }
                if (!Schema::hasColumn('PRATICIE', 'DATE_ENREGISTREMENT')) {
                    $table->date('DATE_ENREGISTREMENT')->nullable()->after('A_CONVENTION');
                }
            });
        }

        if (Schema::hasTable('praticien')) {
            Schema::table('praticien', function (Blueprint $table) {
                if (!Schema::hasColumn('praticien', 'code_comptable')) {
                    $table->string('code_comptable', 50)->nullable()->after('code_praticien');
                }
                if (!Schema::hasColumn('praticien', 'a_convention')) {
                    $table->boolean('a_convention')->default(false)->after('code_comptable');
                }
                if (!Schema::hasColumn('praticien', 'date_enregistrement')) {
                    $table->date('date_enregistrement')->nullable()->after('a_convention');
                }
            });
        }

        // 3. Table bon_commande (nom_ordonnateur)
        if (Schema::hasTable('bon_commande')) {
            Schema::table('bon_commande', function (Blueprint $table) {
                if (!Schema::hasColumn('bon_commande', 'nom_ordonnateur')) {
                    $table->string('nom_ordonnateur', 255)->nullable()->after('date_ordonnance');
                }
            });
        }

        // 4. Table demande (sous_couvert, regularisation)
        if (Schema::hasTable('demande')) {
            Schema::table('demande', function (Blueprint $table) {
                if (!Schema::hasColumn('demande', 'sous_couvert')) {
                    $table->boolean('sous_couvert')->default(false)->after('statut');
                }
                if (!Schema::hasColumn('demande', 'is_regularisation')) {
                    $table->boolean('is_regularisation')->default(false)->after('sous_couvert');
                }
                if (!Schema::hasColumn('demande', 'motif_regularisation')) {
                    $table->text('motif_regularisation')->nullable()->after('is_regularisation');
                }
                if (!Schema::hasColumn('demande', 'situation_initiale')) {
                    $table->text('situation_initiale')->nullable()->after('motif_regularisation');
                }
            });
        }

        // 5. Table prestation (sous_couvert, is_regularisation, id_type_acte)
        if (Schema::hasTable('prestation')) {
            Schema::table('prestation', function (Blueprint $table) {
                if (!Schema::hasColumn('prestation', 'sous_couvert')) {
                    $table->boolean('sous_couvert')->default(false)->after('taux_prise_charge');
                }
                if (!Schema::hasColumn('prestation', 'is_regularisation')) {
                    $table->boolean('is_regularisation')->default(false)->after('sous_couvert');
                }
                if (!Schema::hasColumn('prestation', 'id_type_acte')) {
                    $table->unsignedBigInteger('id_type_acte')->nullable()->after('id_type_prestation');
                }
            });
        }

        // 6. Table type_prestation (description, statut, code)
        if (Schema::hasTable('type_prestation')) {
            Schema::table('type_prestation', function (Blueprint $table) {
                if (!Schema::hasColumn('type_prestation', 'code')) {
                    $table->string('code', 50)->nullable()->after('libelle');
                }
                if (!Schema::hasColumn('type_prestation', 'description')) {
                    $table->text('description')->nullable()->after('code');
                }
                if (!Schema::hasColumn('type_prestation', 'statut')) {
                    $table->string('statut', 20)->default('actif')->after('description');
                }
            });
        }

        // 7. Table type_acte
        if (!Schema::hasTable('type_acte')) {
            Schema::create('type_acte', function (Blueprint $table) {
                $table->id('id_type_acte');
                $table->unsignedBigInteger('id_type_prestation');
                $table->string('libelle', 255);
                $table->text('description')->nullable();
                $table->string('statut', 20)->default('actif');
                $table->timestamps();

                $table->foreign('id_type_prestation')
                      ->references('id_type_prestation')
                      ->on('type_prestation')
                      ->onDelete('cascade');
                
                $table->index(['id_type_prestation', 'libelle']);
            });
        }

        // 8. Table parametre_couverture (montant_referentiel et rendre taux nullable)
        if (Schema::hasTable('parametre_couverture')) {
            Schema::table('parametre_couverture', function (Blueprint $table) {
                if (!Schema::hasColumn('parametre_couverture', 'montant_referentiel')) {
                    $table->decimal('montant_referentiel', 12, 2)->nullable()->after('ticket_moderateur');
                }
            });

            // Rendre taux_prise_charge nullable
            try {
                DB::statement("ALTER TABLE `parametre_couverture` MODIFY `taux_prise_charge` DECIMAL(5,2) NULL");
            } catch (\Throwable $e) {
                // Ignore if already nullable or fails on specific dialect
            }

            // Migrer ticket_moderateur existant vers montant_referentiel si non vide
            DB::statement("UPDATE `parametre_couverture` SET `montant_referentiel` = `ticket_moderateur` WHERE `montant_referentiel` IS NULL AND `ticket_moderateur` IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('type_acte')) {
            Schema::dropIfExists('type_acte');
        }

        if (Schema::hasTable('type_prestation')) {
            Schema::table('type_prestation', function (Blueprint $table) {
                $table->dropColumn(['code', 'description', 'statut']);
            });
        }

        if (Schema::hasTable('prestation')) {
            Schema::table('prestation', function (Blueprint $table) {
                $table->dropColumn(['sous_couvert', 'is_regularisation', 'id_type_acte']);
            });
        }

        if (Schema::hasTable('demande')) {
            Schema::table('demande', function (Blueprint $table) {
                $table->dropColumn(['sous_couvert', 'is_regularisation', 'motif_regularisation', 'situation_initiale']);
            });
        }

        if (Schema::hasTable('bon_commande')) {
            Schema::table('bon_commande', function (Blueprint $table) {
                $table->dropColumn(['nom_ordonnateur']);
            });
        }

        if (Schema::hasTable('PRATICIE')) {
            Schema::table('PRATICIE', function (Blueprint $table) {
                $table->dropColumn(['A_CONVENTION', 'DATE_ENREGISTREMENT']);
            });
        }

        if (Schema::hasTable('ADHERANT')) {
            Schema::table('ADHERANT', function (Blueprint $table) {
                $table->dropColumn(['ACTIVITE', 'DATE_ADHESION', 'LETTRE_CLE', 'PREFIXE']);
            });
        }
    }
};
