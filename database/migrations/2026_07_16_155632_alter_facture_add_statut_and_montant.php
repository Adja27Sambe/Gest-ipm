<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facture', function (Blueprint $table) {
            if (!Schema::hasColumn('facture', 'statut_paiement')) {
                $table->enum('statut_paiement', ['en_attente', 'partiellement_payee', 'soldee'])->default('en_attente')->after('date_facture');
            }
            if (!Schema::hasColumn('facture', 'montant')) {
                $table->decimal('montant', 15, 2)->default(0)->after('numero_facture');
            }
        });
    }

    public function down(): void
    {
        Schema::table('facture', function (Blueprint $table) {
            if (Schema::hasColumn('facture', 'statut_paiement')) {
                $table->dropColumn('statut_paiement');
            }
            if (Schema::hasColumn('facture', 'montant')) {
                $table->dropColumn('montant');
            }
        });
    }
};
