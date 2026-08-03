<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    /**
     * Affiche la galerie de médias
     */
    public function index()
    {
        $perPage = request('per_page', 5);
        $medias = Media::with('utilisateur')->latest('id_media')->paginate($perPage)->withQueryString();
        return view('medias.index', compact('medias'));
    }

    /**
     * Upload un nouveau média dans le dossier public
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
            // Stocker dans le disque public (storage/app/public/medias)
            $path = $file->store('medias', 'public');

            $media = Media::create([
                'titre' => $request->titre ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'nom_fichier_original' => $file->getClientOriginalName(),
                'chemin_fichier' => $path,
                'type_mime' => $file->getClientMimeType(),
                'taille' => $file->getSize(),
                'texte_alternatif' => $request->texte_alternatif,
                'id_utilisateur' => Auth::id(),
            ]);

            $uploadedMedias[] = $media;
        }

        return back()->with('success', count($uploadedMedias) . ' média(s) uploadé(s) avec succès.');
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
