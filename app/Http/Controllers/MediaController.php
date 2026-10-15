<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    protected \App\Services\MediaManagerService $mediaManager;
    protected \App\Services\CloudBackupService $cloudBackup;

    public function __construct(\App\Services\MediaManagerService $mediaManager, \App\Services\CloudBackupService $cloudBackup)
    {
        $this->mediaManager = $mediaManager;
        $this->cloudBackup = $cloudBackup;
    }

    /**
     * Affiche la galerie de médias
     */
    public function index(Request $request)
    {
        $query = Media::with('utilisateur')->latest('id_media');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('nom_fichier_original', 'like', "%{$search}%")
                  ->orWhere('type_mime', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cloud_synced')) {
            $query->where('cloud_synced', $request->boolean('cloud_synced'));
        }

        $perPage = $request->input('per_page', 5);
        $medias = $query->paginate($perPage)->withQueryString();
        $cloudStats = $this->cloudBackup->getSyncStats();

        return view('medias.index', compact('medias', 'cloudStats'));
    }

    /**
     * Upload et optimise des nouveaux médias dans la médiathèque
     */
    public function store(Request $request)
    {
        $request->validate([
            'fichiers' => 'required|array',
            'fichiers.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg,webp,pdf,mp4|max:20480', // 20MB Max
            'titre' => 'nullable|string|max:255',
            'texte_alternatif' => 'nullable|string|max:255',
        ]);

        $uploadedMedias = [];

        foreach ($request->file('fichiers') as $file) {
            $media = $this->mediaManager->storeMedia($file, [
                'titre' => $request->titre,
                'texte_alternatif' => $request->texte_alternatif,
            ]);

            $uploadedMedias[] = $media;
        }

        return back()->with('success', count($uploadedMedias) . ' média(s) optimisé(s) et sauvegardé(s) avec succès.');
    }

    /**
     * Synchroniser un média individuel vers le Cloud
     */
    public function syncCloud($id)
    {
        $media = Media::findOrFail($id);
        $result = $this->cloudBackup->backupMedia($media, true);

        if ($result['success']) {
            return back()->with('success', "Média #{$media->id_media} sauvegardé sur le Cloud ({$media->cloud_driver}).");
        }

        return back()->with('error', "Échec de la sauvegarde Cloud : " . ($result['message'] ?? 'Erreur'));
    }

    /**
     * Synchroniser tous les médias en attente vers le Cloud
     */
    public function syncAllCloud()
    {
        $result = $this->cloudBackup->backupAllMedias();
        return back()->with('success', "Synchronisation Cloud terminée : {$result['synced']} média(s) sauvegardé(s).");
    }

    /**
     * Supprime un média du disque et de la BDD
     */
    public function destroy(Media $media)
    {
        // Supprimer le fichier physique
        if (Storage::disk('public')->exists($media->chemin_fichier)) {
            Storage::disk('public')->delete($media->chemin_fichier);
        }

        // Supprimer l'enregistrement
        $media->delete();

        return back()->with('success', 'Média supprimé avec succès.');
    }
}
