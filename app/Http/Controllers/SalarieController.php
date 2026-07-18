<?php

namespace App\Http\Controllers;

use App\Models\Salarie;
use App\Models\Entreprise;
use App\Http\Requests\StoreSalarieRequest;
use App\Http\Requests\UpdateSalarieRequest;
use Illuminate\Http\Request;

class SalarieController extends Controller
{
    public function index(Request $request)
    {
        $query = Salarie::with(['entreprise', 'carteAssure', 'photo']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('matricule', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $salaries = $query->paginate(15)->withQueryString();

        return view('salaries.index', compact('salaries'));
    }

    public function create()
    {
        $entreprises = Entreprise::orderBy('raison_sociale')->get();
        return view('salaries.create', compact('entreprises'));
    }

    public function store(StoreSalarieRequest $request)
    {
        $data = $request->validated();
        unset($data['photo']);
        $salarie = Salarie::create($data);
        $this->handlePhotoUpload($request, $salarie);
        return redirect()->route('salaries.index')->with('success', 'Salarié créé avec succès.');
    }

    public function show(Salarie $salarie)
    {
        $salarie->load(['entreprise', 'carteAssure', 'ayantsDroit.photo', 'photo']);
        return view('salaries.show', compact('salarie'));
    }

    public function edit(Salarie $salarie)
    {
        $entreprises = Entreprise::orderBy('raison_sociale')->get();
        return view('salaries.edit', compact('salarie', 'entreprises'));
    }

    public function update(UpdateSalarieRequest $request, Salarie $salarie)
    {
        $data = $request->validated();
        unset($data['photo']);
        $salarie->update($data);
        $this->handlePhotoUpload($request, $salarie);
        return redirect()->route('salaries.index')->with('success', 'Salarié mis à jour avec succès.');
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
                'titre' => 'Photo de profil - ' . $model->matricule,
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

    public function destroy(Salarie $salarie)
    {
        if ($salarie->ayantsDroit()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce salarié car il possède des ayants droit.');
        }

        $salarie->delete();
        return redirect()->route('salaries.index')->with('success', 'Salarié supprimé avec succès.');
    }
}
