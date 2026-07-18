<?php

namespace App\Http\Controllers;

use App\Models\AyantDroit;
use App\Models\Salarie;
use App\Http\Requests\StoreAyantDroitRequest;
use Illuminate\Http\Request;

class AyantDroitController extends Controller
{
    public function store(StoreAyantDroitRequest $request, Salarie $salarie)
    {
        // On s'assure que l'id_salarie correspond bien
        $data = $request->validated();
        unset($data['photo']);
        $data['id_salarie'] = $salarie->id_salarie;
        $data['statut'] = 'actif'; // Actif par défaut

        $ayantDroit = AyantDroit::create($data);
        $this->handlePhotoUpload($request, $ayantDroit);

        return back()->with('success', 'Ayant droit ajouté avec succès au dossier de la famille.');
    }

    public function edit(AyantDroit $ayantDroit)
    {
        $salarie = $ayantDroit->salarie;
        return view('ayants-droit.edit', compact('ayantDroit', 'salarie'));
    }

    public function update(\App\Http\Requests\UpdateAyantDroitRequest $request, AyantDroit $ayantDroit)
    {
        $data = $request->validated();
        unset($data['photo']);
        $ayantDroit->update($data);
        $this->handlePhotoUpload($request, $ayantDroit);
        return redirect()->route('salaries.show', $ayantDroit->id_salarie)
                         ->with('success', 'Ayant droit mis à jour avec succès.');
    }

    protected function handlePhotoUpload(Request $request, $model)
    {
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            
            // Delete old photo
            if ($model->id_photo_media) {
                $oldMedia = $model->photo;
                if ($oldMedia) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldMedia->chemin_fichier);
                    $oldMedia->delete();
                }
            }

            // Upload new photo
            $path = $file->store('photos_profil', 'public');
            $media = \App\Models\Media::create([
                'titre' => 'Photo de profil Ayant-droit',
                'nom_fichier_original' => $file->getClientOriginalName(),
                'chemin_fichier' => $path,
                'type_mime' => $file->getClientMimeType(),
                'taille' => $file->getSize(),
                'id_utilisateur' => \Illuminate\Support\Facades\Auth::id() ?? 1,
            ]);

            $model->id_photo_media = $media->id_media;
            $model->save();
        }
    }

    public function destroy(AyantDroit $ayantDroit)
    {
        $ayantDroit->delete();
        return back()->with('success', 'Ayant droit supprimé avec succès.');
    }
}
