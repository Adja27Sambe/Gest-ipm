<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CloudBackupService;
use App\Services\Cloud\GoogleDriveClient;

class CloudBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloud:backup 
                            {--force : Forcer la re-synchronisation de tous les fichiers}
                            {--type=all : Types à sauvegarder : all, documents, medias}
                            {--test : Tester la connexion Google Drive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sauvegarde et synchronise tous les documents et médias vers le Cloud (Google Drive & Coffre-fort Miroir)';

    /**
     * Execute the console command.
     */
    public function handle(CloudBackupService $backupService, GoogleDriveClient $driveClient): int
    {
        $this->info('===========================================================');
        $this->info('  IPM Mbaarum Koolute - Système de Sauvegarde Cloud (Drive)');
        $this->info('===========================================================');

        // Test de connexion
        if ($this->option('test')) {
            $this->info("Vérification de la connexion Google Drive...");
            $quota = $driveClient->getStorageQuota();
            if ($quota['connected']) {
                $this->info(" Connexion Google Drive active !");
                $this->line(" Utilisateur : " . ($quota['user'] ?? 'N/A'));
                $this->line(" Email : " . ($quota['email'] ?? 'N/A'));
                $this->line(" Espace Utilisé : " . ($quota['used_readable'] ?? 'N/A') . " / " . ($quota['total_readable'] ?? 'Illimité') . " ({$quota['used_percentage']}%)");
                return Command::SUCCESS;
            } else {
                $this->warn(" Google Drive non configuré ou hors ligne : " . ($quota['message'] ?? 'Erreur inconnue'));
                $this->line(" Le coffre-fort miroir local est actif.");
                return Command::FAILURE;
            }
        }

        $force = (bool) $this->option('force');
        $type = strtolower($this->option('type') ?? 'all');

        $this->line("Mode de sauvegarde : " . ($force ? "Forcé (tous les fichiers)" : "Incrémental (fichiers non synchronisés)"));

        if ($type === 'all' || $type === 'documents') {
            $this->info("\n--- Synchronisation des Documents (Pièces Jointes) ---");
            $docResult = $backupService->backupAllDocuments($force);
            $this->info("Documents traités : {$docResult['total']} | Synchronisés : {$docResult['synced']} | Échecs : {$docResult['errors']}");
        }

        if ($type === 'all' || $type === 'medias') {
            $this->info("\n--- Synchronisation des Médias ---");
            $mediaResult = $backupService->backupAllMedias($force);
            $this->info("Médias traités : {$mediaResult['total']} | Synchronisés : {$mediaResult['synced']} | Échecs : {$mediaResult['errors']}");
        }

        $stats = $backupService->getSyncStats();
        $this->newLine();
        $this->table(
            ['Statistique', 'Valeur'],
            [
                ['Total fichiers enregistrés', $stats['total_files']],
                ['Total sauvegardés sur le Cloud', $stats['total_synced']],
                ['En attente de synchronisation', $stats['total_pending']],
                ['Volume total des données', $stats['total_size_readable']],
                ['Statut Google Drive', $stats['drive_connected'] ? 'Connecté (' . $stats['drive_user'] . ')' : 'Coffre-fort Miroir actif'],
            ]
        );

        $this->info("\n Sauvegarde Cloud terminée avec succès !");
        return Command::SUCCESS;
    }
}
