<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('piece_jointe', function (Blueprint $table) {
            if (!Schema::hasColumn('piece_jointe', 'sha256')) {
                $table->string('sha256', 64)->nullable()->index()->after('chemin_fichier');
            }
            if (!Schema::hasColumn('piece_jointe', 'taille')) {
                $table->unsignedBigInteger('taille')->nullable()->after('sha256');
            }
            if (!Schema::hasColumn('piece_jointe', 'cloud_synced')) {
                $table->boolean('cloud_synced')->default(false)->index()->after('lien_dossier');
            }
            if (!Schema::hasColumn('piece_jointe', 'cloud_synced_at')) {
                $table->timestamp('cloud_synced_at')->nullable()->after('cloud_synced');
            }
            if (!Schema::hasColumn('piece_jointe', 'cloud_path')) {
                $table->string('cloud_path')->nullable()->after('cloud_synced_at');
            }
            if (!Schema::hasColumn('piece_jointe', 'cloud_id')) {
                $table->string('cloud_id', 100)->nullable()->after('cloud_path');
            }
            if (!Schema::hasColumn('piece_jointe', 'cloud_driver')) {
                $table->string('cloud_driver', 50)->default('drive')->after('cloud_id');
            }
            if (!Schema::hasColumn('piece_jointe', 'cloud_url')) {
                $table->text('cloud_url')->nullable()->after('cloud_driver');
            }
        });

        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'sha256')) {
                $table->string('sha256', 64)->nullable()->index()->after('chemin_fichier');
            }
            if (!Schema::hasColumn('media', 'largeur')) {
                $table->unsignedInteger('largeur')->nullable()->after('taille');
            }
            if (!Schema::hasColumn('media', 'hauteur')) {
                $table->unsignedInteger('hauteur')->nullable()->after('largeur');
            }
            if (!Schema::hasColumn('media', 'cloud_synced')) {
                $table->boolean('cloud_synced')->default(false)->index()->after('texte_alternatif');
            }
            if (!Schema::hasColumn('media', 'cloud_synced_at')) {
                $table->timestamp('cloud_synced_at')->nullable()->after('cloud_synced');
            }
            if (!Schema::hasColumn('media', 'cloud_path')) {
                $table->string('cloud_path')->nullable()->after('cloud_synced_at');
            }
            if (!Schema::hasColumn('media', 'cloud_id')) {
                $table->string('cloud_id', 100)->nullable()->after('cloud_path');
            }
            if (!Schema::hasColumn('media', 'cloud_driver')) {
                $table->string('cloud_driver', 50)->default('drive')->after('cloud_id');
            }
            if (!Schema::hasColumn('media', 'cloud_url')) {
                $table->text('cloud_url')->nullable()->after('cloud_driver');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piece_jointe', function (Blueprint $table) {
            $table->dropColumn([
                'sha256',
                'taille',
                'cloud_synced',
                'cloud_synced_at',
                'cloud_path',
                'cloud_id',
                'cloud_driver',
                'cloud_url',
            ]);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'sha256',
                'largeur',
                'hauteur',
                'cloud_synced',
                'cloud_synced_at',
                'cloud_path',
                'cloud_id',
                'cloud_driver',
                'cloud_url',
            ]);
        });
    }
};
