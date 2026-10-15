<?php

namespace App\Services;

use App\Models\PieceJointe;
use App\Models\Media;
use App\Services\Cloud\GoogleDriveClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CloudBackupService
{
    protected GoogleDriveClient $driveClient;
    protected string $vaultDisk;

    public function __construct(GoogleDriveClient $driveClient)
    {
        $this->driveClient = $driveClient;
        $this->vaultDisk = config('cloud.vault_disk', 'cloud');
    }

    /**
     * Sauvegarde une pièce jointe (Document) sur le Cloud (Google Drive + Vault Miroir)
     */
    public function backupDocument(PieceJointe $piece, bool $force = false): array
    {
        if (!$force && $piece->cloud_synced) {
            return ['success' => true, 'message' => 'Déjà synchronisé sur le Cloud', 'piece' => $piece];
        }

        if (!Storage::exists($piece->chemin_fichier)) {
            return ['success' => false, 'message' => 'Fichier source introuvable sur le disque local'];
        }

        $localPath = Storage::path($piece->chemin_fichier);
        $fileName = $piece->nom_fichier ?? basename($piece->chemin_fichier);
        $mimeType = $piece->type_fichier ?? 'application/octet-stream';
        $cloudPath = 'documents/' . date('Y/m', strtotime($piece->date_ajout ?? now())) . '/' . basename($piece->chemin_fichier);

        // 1. Sauvegarde dans le coffre-fort miroir Cloud (garantie de redondance immédiate)
        try {
            $stream = fopen($localPath, 'r');
            Storage::disk($this->vaultDisk)->put($cloudPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        } catch (\Throwable $e) {
            Log::warning("Erreur miroir coffre-fort document #{$piece->id_piece}: " . $e->getMessage());
        }

        // 2. Sauvegarde sur Google Drive si configuré
        $driveResult = null;
        if ($this->driveClient->isConfigured()) {
            $driveResult = $this->driveClient->upload($localPath, $fileName, $mimeType, 'Documents');
        }

        // 3. Mise à jour de l'enregistrement
        $piece->cloud_synced = true;
        $piece->cloud_synced_at = Carbon::now();
        $piece->cloud_path = $cloudPath;
        $piece->cloud_driver = $driveResult ? 'google_drive' : 'cloud_vault';
        if ($driveResult) {
            $piece->cloud_id = $driveResult['id'] ?? null;
            $piece->cloud_url = $driveResult['webViewLink'] ?? null;
        }
        $piece->save();

        return [
            'success' => true,
            'driver' => $piece->cloud_driver,
            'cloud_id' => $piece->cloud_id,
            'cloud_url' => $piece->cloud_url,
            'piece' => $piece,
        ];
    }

    /**
     * Sauvegarde un média de la galerie sur le Cloud (Google Drive + Vault Miroir)
     */
    public function backupMedia(Media $media, bool $force = false): array
    {
        if (!$force && $media->cloud_synced) {
            return ['success' => true, 'message' => 'Média déjà synchronisé sur le Cloud', 'media' => $media];
        }

        if (!Storage::disk('public')->exists($media->chemin_fichier)) {
            return ['success' => false, 'message' => 'Fichier média introuvable sur le disque public'];
        }

        $localPath = Storage::disk('public')->path($media->chemin_fichier);
        $fileName = $media->nom_fichier_original ?? basename($media->chemin_fichier);
        $mimeType = $media->type_mime ?? 'application/octet-stream';
        $cloudPath = 'medias/' . date('Y/m', strtotime($media->created_at ?? now())) . '/' . basename($media->chemin_fichier);

        // 1. Sauvegarde dans le coffre-fort miroir Cloud
        try {
            $stream = fopen($localPath, 'r');
            Storage::disk($this->vaultDisk)->put($cloudPath, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        } catch (\Throwable $e) {
            Log::warning("Erreur miroir coffre-fort média #{$media->id_media}: " . $e->getMessage());
        }

        // 2. Sauvegarde sur Google Drive si configuré
        $driveResult = null;
        if ($this->driveClient->isConfigured()) {
            $driveResult = $this->driveClient->upload($localPath, $fileName, $mimeType, 'Medias');
        }

        // 3. Mise à jour de l'enregistrement
        $media->cloud_synced = true;
        $media->cloud_synced_at = Carbon::now();
        $media->cloud_path = $cloudPath;
        $media->cloud_driver = $driveResult ? 'google_drive' : 'cloud_vault';
        if ($driveResult) {
            $media->cloud_id = $driveResult['id'] ?? null;
            $media->cloud_url = $driveResult['webViewLink'] ?? null;
        }
        $media->save();

        return [
            'success' => true,
            'driver' => $media->cloud_driver,
            'cloud_id' => $media->cloud_id,
            'cloud_url' => $media->cloud_url,
            'media' => $media,
        ];
    }

    /**
     * Sauvegarde toutes les pièces jointes non synchronisées
     */
    public function backupAllDocuments(bool $force = false): array
    {
        $query = PieceJointe::query();
        if (!$force) {
            $query->where('cloud_synced', false);
        }

        $pieces = $query->get();
        $successCount = 0;
        $errorCount = 0;

        foreach ($pieces as $piece) {
            $res = $this->backupDocument($piece, $force);
            if ($res['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'total' => $pieces->count(),
            'synced' => $successCount,
            'errors' => $errorCount,
        ];
    }

    /**
     * Sauvegarde tous les médias non synchronisés
     */
    public function backupAllMedias(bool $force = false): array
    {
        $query = Media::query();
        if (!$force) {
            $query->where('cloud_synced', false);
        }

        $medias = $query->get();
        $successCount = 0;
        $errorCount = 0;

        foreach ($medias as $media) {
            $res = $this->backupMedia($media, $force);
            if ($res['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'total' => $medias->count(),
            'synced' => $successCount,
            'errors' => $errorCount,
        ];
    }

    /**
     * Sauvegarde globale de tous les documents et médias
     */
    public function backupAll(bool $force = false): array
    {
        $docs = $this->backupAllDocuments($force);
        $medias = $this->backupAllMedias($force);

        return [
            'documents' => $docs,
            'medias' => $medias,
            'total_synced' => $docs['synced'] + $medias['synced'],
            'total_errors' => $docs['errors'] + $medias['errors'],
        ];
    }

    /**
     * Restaure un fichier local depuis le Cloud si supprimé accidentellement
     */
    public function restoreFile(string $type, int $id): bool
    {
        if ($type === 'piece_jointe') {
            $piece = PieceJointe::find($id);
            if (!$piece || empty($piece->cloud_path)) {
                return false;
            }

            // Vérifier Google Drive
            if (!empty($piece->cloud_id) && $this->driveClient->isConfigured()) {
                $content = $this->driveClient->download($piece->cloud_id);
                if ($content !== null) {
                    Storage::put($piece->chemin_fichier, $content);
                    return true;
                }
            }

            // Fallback Coffre-fort Miroir
            if (Storage::disk($this->vaultDisk)->exists($piece->cloud_path)) {
                $content = Storage::disk($this->vaultDisk)->get($piece->cloud_path);
                Storage::put($piece->chemin_fichier, $content);
                return true;
            }
        } elseif ($type === 'media') {
            $media = Media::find($id);
            if (!$media || empty($media->cloud_path)) {
                return false;
            }

            if (!empty($media->cloud_id) && $this->driveClient->isConfigured()) {
                $content = $this->driveClient->download($media->cloud_id);
                if ($content !== null) {
                    Storage::disk('public')->put($media->chemin_fichier, $content);
                    return true;
                }
            }

            if (Storage::disk($this->vaultDisk)->exists($media->cloud_path)) {
                $content = Storage::disk($this->vaultDisk)->get($media->cloud_path);
                Storage::disk('public')->put($media->chemin_fichier, $content);
                return true;
            }
        }

        return false;
    }

    /**
     * Statistiques globales sur la synchronisation Cloud
     */
    public function getSyncStats(): array
    {
        $totalDocs = PieceJointe::count();
        $syncedDocs = PieceJointe::where('cloud_synced', true)->count();
        $pendingDocs = $totalDocs - $syncedDocs;

        $totalMedias = Media::count();
        $syncedMedias = Media::where('cloud_synced', true)->count();
        $pendingMedias = $totalMedias - $syncedMedias;

        $totalDocsSize = (int) PieceJointe::sum('taille');
        $totalMediasSize = (int) Media::sum('taille');

        $driveQuota = $this->driveClient->getStorageQuota();

        return [
            'total_files' => $totalDocs + $totalMedias,
            'total_synced' => $syncedDocs + $syncedMedias,
            'total_pending' => $pendingDocs + $pendingMedias,
            'documents' => [
                'total' => $totalDocs,
                'synced' => $syncedDocs,
                'pending' => $pendingDocs,
                'size' => $this->formatBytes($totalDocsSize),
            ],
            'medias' => [
                'total' => $totalMedias,
                'synced' => $syncedMedias,
                'pending' => $pendingMedias,
                'size' => $this->formatBytes($totalMediasSize),
            ],
            'total_size_readable' => $this->formatBytes($totalDocsSize + $totalMediasSize),
            'drive_connected' => $driveQuota['connected'] ?? false,
            'drive_user' => $driveQuota['user'] ?? null,
            'drive_quota' => $driveQuota,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
