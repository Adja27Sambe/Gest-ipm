<?php

namespace App\Services\Cloud;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleDriveClient
{
    protected ?string $folderId;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $refreshToken;

    public function __construct()
    {
        $this->folderId = config('cloud.google_drive.folder_id');
        $this->clientId = config('cloud.google_drive.client_id');
        $this->clientSecret = config('cloud.google_drive.client_secret');
        $this->refreshToken = config('cloud.google_drive.refresh_token');
    }

    /**
     * Vérifie si l'API Google Drive est configurée avec des identifiants valides
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->refreshToken);
    }

    /**
     * Obtient un jeton d'accès OAuth2 valide (avec mise en cache de 50 minutes)
     */
    public function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        return Cache::remember('google_drive_access_token', 3000, function () {
            try {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                }

                Log::error('Erreur lors du renouvellement du token Google Drive : ' . $response->body());
                return null;
            } catch (\Throwable $e) {
                Log::error('Exception renouvellement token Google Drive : ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Upload un fichier sur Google Drive (Multipart Upload)
     *
     * @param string $localFilePath Chemin absolu du fichier local
     * @param string $remoteFilename Nom souhaité sur Google Drive
     * @param string $mimeType Type MIME du fichier
     * @param string|null $subfolder Dossier spécifique ('Documents' ou 'Medias')
     * @return array|null Informations du fichier uploadé (id, webViewLink, etc.)
     */
    public function upload(string $localFilePath, string $remoteFilename, string $mimeType = 'application/octet-stream', ?string $subfolder = null): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        if (!file_exists($localFilePath) || !is_readable($localFilePath)) {
            Log::warning("Fichier local introuvable pour upload Drive: {$localFilePath}");
            return null;
        }

        // Déterminer le dossier cible
        $targetFolderId = $this->folderId;
        if ($subfolder && $this->folderId) {
            $targetFolderId = $this->findOrCreateFolder($subfolder, $this->folderId) ?? $this->folderId;
        }

        $metadata = [
            'name' => $remoteFilename,
        ];
        if (!empty($targetFolderId)) {
            $metadata['parents'] = [$targetFolderId];
        }

        try {
            $fileContent = file_get_contents($localFilePath);
            $boundary = '-------314159265358979323846';
            $delimiter = "\r\n--" . $boundary . "\r\n";
            $closeDelimiter = "\r\n--" . $boundary . "--";

            $multipartBody = $delimiter .
                "Content-Type: application/json; charset=UTF-8\r\n\r\n" .
                json_encode($metadata) .
                $delimiter .
                "Content-Type: {$mimeType}\r\n\r\n" .
                $fileContent .
                $closeDelimiter;

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => "multipart/related; boundary={$boundary}",
            ])->withBody($multipartBody, "multipart/related; boundary={$boundary}")
              ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink,size,mimeType');

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Fichier uploadé sur Google Drive avec succès : {$remoteFilename} (ID: {$data['id']})");
                return $data;
            }

            Log::error("Erreur upload Google Drive pour {$remoteFilename} : " . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error("Exception upload Google Drive ({$remoteFilename}) : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Recherche ou crée un sous-dossier dans Google Drive
     */
    public function findOrCreateFolder(string $folderName, ?string $parentFolderId = null): ?string
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $cacheKey = 'gdrive_folder_' . md5($folderName . '_' . ($parentFolderId ?? 'root'));
        return Cache::remember($cacheKey, 86400, function () use ($accessToken, $folderName, $parentFolderId) {
            try {
                // 1. Recherche du dossier
                $query = "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
                if ($parentFolderId) {
                    $query .= " and '{$parentFolderId}' in parents";
                }

                $searchResponse = Http::withToken($accessToken)
                    ->get('https://www.googleapis.com/drive/v3/files', [
                        'q' => $query,
                        'fields' => 'files(id, name)',
                        'spaces' => 'drive',
                    ]);

                if ($searchResponse->successful()) {
                    $files = $searchResponse->json('files', []);
                    if (!empty($files)) {
                        return $files[0]['id'];
                    }
                }

                // 2. Création si introuvable
                $createPayload = [
                    'name' => $folderName,
                    'mimeType' => 'application/vnd.google-apps.folder',
                ];
                if ($parentFolderId) {
                    $createPayload['parents'] = [$parentFolderId];
                }

                $createResponse = Http::withToken($accessToken)
                    ->post('https://www.googleapis.com/drive/v3/files?fields=id,name', $createPayload);

                if ($createResponse->successful()) {
                    return $createResponse->json('id');
                }

                return null;
            } catch (\Throwable $e) {
                Log::error("Erreur findOrCreateFolder Google Drive ({$folderName}): " . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Télécharge un fichier depuis Google Drive par son identifiant
     */
    public function download(string $fileId): ?string
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media");

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (\Throwable $e) {
            Log::error("Erreur download Google Drive (ID: {$fileId}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère le statut et l'espace de stockage Google Drive
     */
    public function getStorageQuota(): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'connected' => false,
                'message' => 'Non configuré ou identifiants manquants dans le fichier .env',
            ];
        }

        try {
            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/drive/v3/about?fields=storageQuota,user');

            if ($response->successful()) {
                $data = $response->json();
                $quota = $data['storageQuota'] ?? [];
                $limit = (int)($quota['limit'] ?? 0);
                $usage = (int)($quota['usage'] ?? 0);

                return [
                    'connected' => true,
                    'user' => $data['user']['displayName'] ?? 'Utilisateur Google',
                    'email' => $data['user']['emailAddress'] ?? '',
                    'total_bytes' => $limit,
                    'used_bytes' => $usage,
                    'used_percentage' => $limit > 0 ? round(($usage / $limit) * 100, 1) : 0,
                    'total_readable' => $limit > 0 ? round($limit / (1024 * 1024 * 1024), 2) . ' GB' : 'Illimité',
                    'used_readable' => round($usage / (1024 * 1024 * 1024), 2) . ' GB',
                ];
            }

            return [
                'connected' => false,
                'message' => 'Impossible de joindre Google Drive: ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
}
