<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\PieceJointe;
use App\Models\CategorieDocument;
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
     * Affiche le centre unifié Médias & Documents
     */
    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'medias');

        // 1. Médias (Galerie & Fichiers visuels)
        $mediaQuery = Media::with('utilisateur')->latest('id_media');

        if ($request->filled('search') && $activeTab === 'medias') {
            $search = $request->input('search');
            $mediaQuery->where(function($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('nom_fichier_original', 'like', "%{$search}%")
                  ->orWhere('type_mime', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cloud_synced') && $activeTab === 'medias') {
            $mediaQuery->where('cloud_synced', $request->boolean('cloud_synced'));
        }

        $mediaPerPage = $request->input('per_page', 8);
        $medias = $mediaQuery->paginate($mediaPerPage, ['*'], 'medias_page')->withQueryString();

        // 2. Documents (Pièces Jointes & GED)
        $docQuery = PieceJointe::with(['categorie', 'utilisateur', 'attachable'])->latest('date_ajout');

        if ($request->filled('id_categorie')) {
            $docQuery->where('id_categorie', $request->id_categorie);
        }

        if ($request->filled('cloud_synced') && $activeTab === 'documents') {
            $docQuery->where('cloud_synced', $request->boolean('cloud_synced'));
        } elseif ($request->filled('doc_cloud_synced')) {
            $docQuery->where('cloud_synced', $request->boolean('doc_cloud_synced'));
        }

        $docSearch = null;
        if ($activeTab === 'documents' && $request->filled('search')) {
            $docSearch = $request->input('search');
        } elseif ($request->filled('doc_search')) {
            $docSearch = $request->input('doc_search');
        }

        if ($docSearch) {
            $docQuery->where(function($q) use ($docSearch) {
                $q->where('nom_fichier', 'like', "%{$docSearch}%")
                  ->orWhereHas('utilisateur', function($uq) use ($docSearch) {
                      $uq->where('login', 'like', "%{$docSearch}%")
                         ->orWhere('nom', 'like', "%{$docSearch}%");
                  });
            });
        }

        $docPerPage = $request->input('doc_per_page', $request->input('per_page', 10));
        $pieces = $docQuery->paginate($docPerPage, ['*'], 'docs_page')->withQueryString();

        $categories = CategorieDocument::orderBy('libelle')->get();
        $cloudStats = $this->cloudBackup->getSyncStats();

        return view('medias.index', compact('medias', 'pieces', 'categories', 'cloudStats', 'activeTab'));
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
     * Sauvegarde globale Cloud (Médias + Documents)
     */
    public function syncEverything(Request $request)
    {
        $result = $this->cloudBackup->backupAll($request->boolean('force'));
        return back()->with('success', "Sauvegarde Cloud intégrale réussie : {$result['total_synced']} fichier(s) sauvegardé(s) (Médias + Documents).");
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
