<?php

namespace App\Services;

use App\Models\Media;
use App\Models\PieceJointe;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaManagerService
{
    protected CloudBackupService $cloudBackup;

    public function __construct(CloudBackupService $cloudBackup)
    {
        $this->cloudBackup = $cloudBackup;
    }

    /**
     * Traite, optimise et enregistre un document (Pièce Jointe)
     */
    public function storeDocument(UploadedFile $file, array $attributes = []): PieceJointe
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $size = $file->getSize();
        $originalName = $file->getClientOriginalName();
        $sanitizedName = $this->sanitizeFileName($originalName);
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getClientMimeType();

        // Vérification de déduplication (si déjà présent sur disque)
        $existing = PieceJointe::where('sha256', $hash)->first();
        if ($existing && config('cloud.optimization.deduplication', true) && Storage::exists($existing->chemin_fichier)) {
            // Réutilisation du chemin physique pour économiser le stockage
            $path = $existing->chemin_fichier;
        } else {
            // Organisation hiérarchique par date
            $dir = 'documents/' . date('Y/m');
            $fileName = substr($hash, 0, 16) . '_' . $sanitizedName;
            $path = $file->storeAs($dir, $fileName);
        }

        $piece = PieceJointe::create([
            'nom_fichier'      => $originalName,
            'type_fichier'     => $mimeType,
            'chemin_fichier'   => $path,
            'sha256'           => $hash,
            'taille'           => $size,
            'date_ajout'       => now(),
            'id_categorie'     => $attributes['id_categorie'] ?? null,
            'id_utilisateur'   => $attributes['id_utilisateur'] ?? Auth::id(),
            'attachable_type'  => $attributes['attachable_type'] ?? null,
            'attachable_id'    => $attributes['attachable_id'] ?? null,
            'lien_dossier'     => $attributes['lien_dossier'] ?? null,
        ]);

        // Déclenchement automatique de la sauvegarde Cloud (Google Drive)
        if (config('cloud.auto_backup', true)) {
            try {
                $this->cloudBackup->backupDocument($piece);
            } catch (\Throwable $e) {
                Log::warning("Erreur sauvegarde cloud auto document #{$piece->id_piece}: " . $e->getMessage());
            }
        }

        return $piece;
    }

    /**
     * Traite, optimise, compresse et enregistre un média dans la médiathèque
     */
    public function storeMedia(UploadedFile $file, array $attributes = []): Media
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $size = $file->getSize();
        $originalName = $file->getClientOriginalName();
        $sanitizedName = $this->sanitizeFileName($originalName);
        $mimeType = $file->getClientMimeType();

        $width = null;
        $height = null;

        // Détection et optimisation d'image si applicable
        if (str_starts_with($mimeType, 'image/') && function_exists('getimagesize')) {
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }

            // Compression / Redimensionnement intelligent si image trop grande
            if (config('cloud.optimization.compress_images', true)) {
                $this->optimizeImage($file->getRealPath(), $mimeType);
                $size = filesize($file->getRealPath());
            }
        }

        // Vérification de déduplication
        $existing = Media::where('sha256', $hash)->first();
        if ($existing && config('cloud.optimization.deduplication', true) && Storage::disk('public')->exists($existing->chemin_fichier)) {
            $path = $existing->chemin_fichier;
        } else {
            $dir = 'medias/' . date('Y/m');
            $fileName = substr($hash, 0, 16) . '_' . $sanitizedName;
            $path = $file->storeAs($dir, $fileName, 'public');
        }

        $media = Media::create([
            'titre'                => $attributes['titre'] ?? pathinfo($originalName, PATHINFO_FILENAME),
            'nom_fichier_original' => $originalName,
            'chemin_fichier'       => $path,
            'sha256'               => $hash,
            'type_mime'            => $mimeType,
            'taille'               => $size,
            'largeur'              => $width,
            'hauteur'              => $height,
            'texte_alternatif'     => $attributes['texte_alternatif'] ?? null,
            'id_utilisateur'       => $attributes['id_utilisateur'] ?? Auth::id(),
        ]);

        // Déclenchement automatique de la sauvegarde Cloud (Google Drive)
        if (config('cloud.auto_backup', true)) {
            try {
                $this->cloudBackup->backupMedia($media);
            } catch (\Throwable $e) {
                Log::warning("Erreur sauvegarde cloud auto média #{$media->id_media}: " . $e->getMessage());
            }
        }

        return $media;
    }

    /**
     * Optimise et compresse une image sans altérer la clarté visuelle
     */
    protected function optimizeImage(string $filePath, string $mimeType): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        $maxWidth = config('cloud.optimization.max_image_width', 1920);
        $maxHeight = config('cloud.optimization.max_image_height', 1920);
        $quality = config('cloud.optimization.image_quality', 85);

        try {
            $src = null;
            if ($mimeType === 'image/jpeg') {
                $src = @imagecreatefromjpeg($filePath);
            } elseif ($mimeType === 'image/png') {
                $src = @imagecreatefrompng($filePath);
            } elseif ($mimeType === 'image/webp') {
                $src = @imagecreatefromwebp($filePath);
            }

            if (!$src) {
                return;
            }

            $origWidth = imagesx($src);
            $origHeight = imagesy($src);

            if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
                $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
                $newWidth = (int) round($origWidth * $ratio);
                $newHeight = (int) round($origHeight * $ratio);

                $dst = imagecreatetruecolor($newWidth, $newHeight);

                // Préservation de la transparence pour PNG
                if ($mimeType === 'image/png') {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                    imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
                }

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

                if ($mimeType === 'image/jpeg') {
                    imagejpeg($dst, $filePath, $quality);
                } elseif ($mimeType === 'image/png') {
                    imagepng($dst, $filePath, 8);
                } elseif ($mimeType === 'image/webp') {
                    imagewebp($dst, $filePath, $quality);
                }

                imagedestroy($dst);
            }

            imagedestroy($src);
        } catch (\Throwable $e) {
            Log::debug("Optimisation image non critique ignorée: " . $e->getMessage());
        }
    }

    /**
     * Nettoie et sécurise le nom de fichier pour le stockage
     */
    protected function sanitizeFileName(string $filename): string
    {
        $info = pathinfo($filename);
        $name = Str::slug($info['filename'] ?? 'file');
        $ext = isset($info['extension']) ? '.' . strtolower($info['extension']) : '';
        return substr($name, 0, 50) . $ext;
    }
}
