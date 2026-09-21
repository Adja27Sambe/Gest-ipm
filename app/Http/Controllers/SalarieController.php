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
        $query = Salarie::with(['entreprise', 'carteAssure']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('MATRICULE', 'like', "%{$search}%")
                  ->orWhere('NOM', 'like', "%{$search}%")
                  ->orWhere('PRENOM', 'like', "%{$search}%");
        }

        if ($request->filled('statut')) {
            $query->where('PARACTIF', $request->statut);
        }

        $perPage = $request->input('per_page', 5);
        $salaries = $query->latest('IDPARTICIPANT')->paginate($perPage)->withQueryString();

        return view('salaries.index', compact('salaries'));
    }

    public function create()
    {
        $entreprises = Entreprise::orderBy('ADHERANT')->get();
        return view('salaries.create', compact('entreprises'));
    }

    public function store(StoreSalarieRequest $request)
    {
        $data = $request->validated();
        unset($data['photo']);
        $salarie = Salarie::create($data);
        $this->handlePhotoUpload($request, $salarie);
        return redirect()->route('salaries.index')->with('success', 'Participant créé avec succès.');
    }

    public function show(Salarie $salarie)
    {
        $salarie->load(['entreprise', 'carteAssure', 'ayantsDroit.photo']);
        return view('salaries.show', compact('salarie'));
    }

    public function edit(Salarie $salarie)
    {
        $entreprises = Entreprise::orderBy('ADHERANT')->get();
        return view('salaries.edit', compact('salarie', 'entreprises'));
    }

    public function update(UpdateSalarieRequest $request, Salarie $salarie)
    {
        $data = $request->validated();
        unset($data['photo']);
        $salarie->update($data);
        $this->handlePhotoUpload($request, $salarie);
        return redirect()->route('salaries.index')->with('success', 'Participant mis à jour avec succès.');
    }

    protected function handlePhotoUpload(Request $request, $model)
    {
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            
            // Delete old photo if exists
            $oldPhoto = (string) ($model->getRawOriginal('PHOTO') ?? $model->getAttribute('photo') ?? '');
            if ($oldPhoto && str_starts_with($oldPhoto, 'photos_profil/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPhoto);
            }

            // Upload new photo and save path to PHOTO column
            $path = $file->store('photos_profil', 'public');
            $model->setAttribute('photo', $path);
            $model->save();
        }
    }

    public function destroy(Salarie $salarie)
    {
        if ($salarie->ayantsDroit()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce participant car il possède des ayants droit.');
        }

        $salarie->delete();
        return redirect()->route('salaries.index')->with('success', 'Participant supprimé avec succès.');
    }

    /**
     * Recherche de participant par matricule, nom ou prénom avec autocomplétion
     */
    public function searchMatricule(Request $request)
    {
        // Recherche spécifique par ID pour pré-remplissage
        if ($request->filled('id')) {
            $s = Salarie::with(['entreprise', 'ayantsDroit'])->find($request->id);
            if (!$s) {
                return response()->json(null, 404);
            }
            return response()->json([
                'id' => $s->id_salarie ?? $s->IDPARTICIPANT,
                'matricule' => $s->matricule ?? 'N/A',
                'nom' => $s->nom,
                'prenom' => $s->prenom,
                'nom_complet' => trim(($s->nom ?? '') . ' ' . ($s->prenom ?? '')),
                'entreprise' => $s->entreprise?->ADHERANT ?? $s->entreprise?->raison_sociale ?? 'Sans entreprise',
                'statut' => $s->statut,
                'ayants_droit' => $s->ayantsDroit->map(function($ad) {
                    return [
                        'id_ayant_droit' => $ad->id_ayant_droit,
                        'nom' => $ad->nom,
                        'prenom' => $ad->prenom,
                        'lien_parente' => $ad->lien_parente,
                        'date_naissance' => $ad->date_naissance,
                        'age' => $ad->age,
                        'is_eligible' => $ad->isEligible(),
                    ];
                }),
            ]);
        }

        $query = $request->input('q', $request->input('matricule', ''));
        if (empty($query) || strlen(trim($query)) < 1) {
            return response()->json([]);
        }

        $term = trim($query);

        $salaries = Salarie::with(['entreprise', 'ayantsDroit'])
            ->where(function($q) use ($term) {
                $q->where('MATRICULE', 'like', "%{$term}%")
                  ->orWhere('NOM', 'like', "%{$term}%")
                  ->orWhere('PRENOM', 'like', "%{$term}%")
                  ->orWhere('NOMPREN', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get()
            ->map(function($s) {
                return [
                    'id' => $s->id_salarie ?? $s->IDPARTICIPANT,
                    'matricule' => $s->matricule ?? 'N/A',
                    'nom' => $s->nom,
                    'prenom' => $s->prenom,
                    'nom_complet' => trim(($s->nom ?? '') . ' ' . ($s->prenom ?? '')),
                    'entreprise' => $s->entreprise?->ADHERANT ?? $s->entreprise?->raison_sociale ?? 'Sans entreprise',
                    'statut' => $s->statut,
                    'ayants_droit' => $s->ayantsDroit->map(function($ad) {
                        return [
                            'id_ayant_droit' => $ad->id_ayant_droit,
                            'nom' => $ad->nom,
                            'prenom' => $ad->prenom,
                            'lien_parente' => $ad->lien_parente,
                            'date_naissance' => $ad->date_naissance,
                            'age' => $ad->age,
                            'is_eligible' => $ad->isEligible(),
                        ];
                    }),
                ];
            });

        return response()->json($salaries);
    }
}
