<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEntrepriseRequest;
use App\Http\Requests\UpdateEntrepriseRequest;

class EntrepriseController extends Controller
{
    public function index(Request $request)
    {
        $query = Entreprise::withCount('salaries');

        if ($request->filled('search')) {
            $query->where('ADHERANT', 'like', '%' . $request->search . '%')
                  ->orWhere('CODEADHERANT', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('statut')) {
            $query->where('ADACTIF', $request->statut);
        }

        $perPage = $request->input('per_page', 5);
        $entreprises = $query->latest('IDADHERANT')->paginate($perPage)->withQueryString();
        
        return view('entreprises.index', compact('entreprises'));
    }

    public function create()
    {
        return view('entreprises.create');
    }

    public function store(StoreEntrepriseRequest $request)
    {
        Entreprise::create($request->validated());
        return redirect()->route('entreprises.index')->with('success', 'Entreprise ajoutée avec succès.');
    }

    public function show(Entreprise $entreprise)
    {
        $entreprise->loadCount('salaries');
        return view('entreprises.show', compact('entreprise'));
    }

    public function edit(Entreprise $entreprise)
    {
        return view('entreprises.edit', compact('entreprise'));
    }

    public function update(UpdateEntrepriseRequest $request, Entreprise $entreprise)
    {
        $entreprise->update($request->validated());
        return redirect()->route('entreprises.index')->with('success', 'Entreprise mise à jour avec succès.');
    }

    public function destroy(Entreprise $entreprise)
    {
        if ($entreprise->salaries()->exists()) {
            return redirect()->route('entreprises.index')
                ->with('error', 'Impossible de supprimer cette entreprise car des participants y sont rattachés.');
        }

        $entreprise->delete();
        return redirect()->route('entreprises.index')->with('success', 'Entreprise supprimée.');
    }

    public function getNextMatricule(Entreprise $entreprise)
    {
        if (empty($entreprise->code_adherent) && empty($entreprise->prefixe)) {
            return response()->json(['matricule' => '']);
        }
        
        // Retirer les préfixes pour obtenir le code propre ou utiliser le préfixe configuré
        $codeAdherent = !empty($entreprise->prefixe)
            ? trim($entreprise->prefixe)
            : preg_replace('/^(ADH|ENT|MAT)-?/i', '', $entreprise->code_adherent);
        $originalCode = $entreprise->code_adherent ?? $codeAdherent;
        
        $maxNumber = \App\Models\Salarie::where('IDADHERANT', $entreprise->id_entreprise)
            ->whereNotNull('MATRICULE')
            ->pluck('MATRICULE')
            ->map(function ($m) use ($codeAdherent, $originalCode) {
                // Enlever le code entreprise ou préfixe du début s'il est présent
                $compteurStr = preg_replace('/^(' . preg_quote((string)$originalCode, '/') . '|' . preg_quote((string)$codeAdherent, '/') . ')/i', '', $m);
                // Extraire uniquement le numéro de séquence
                return intval(preg_replace('/[^0-9]/', '', $compteurStr));
            })
            ->max() ?? 0;
        
        // Nouveau matricule : préfixe / code entreprise + compteur sur 4 chiffres
        $matricule = $codeAdherent . str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT);

        return response()->json(['matricule' => $matricule]);
    }
}
