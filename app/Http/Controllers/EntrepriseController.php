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
        if (empty($entreprise->code_adherent)) {
            return response()->json(['matricule' => '']);
        }
        
        // Supprimer le préfixe ADH ou ADH- s'il existe
        $codeAdherent = preg_replace('/^ADH-?/i', '', $entreprise->code_adherent);
        
        $lastSalarie = \App\Models\Salarie::where('IDADHERANT', $entreprise->id_entreprise)
            ->whereNotNull('MATRICULE')
            ->where('MATRICULE', 'like', $codeAdherent . '%')
            ->orderByRaw('LENGTH(MATRICULE) DESC')
            ->orderBy('MATRICULE', 'desc')
            ->first();

        if ($lastSalarie && $lastSalarie->matricule !== $codeAdherent) {
            $lastMatricule = $lastSalarie->matricule;
            $numberPart = substr($lastMatricule, strlen($codeAdherent));
            
            if (is_numeric($numberPart)) {
                $nextNumber = intval($numberPart) + 1;
                $padLength = strlen($numberPart);
                if ($padLength > 0 && $numberPart[0] === '0') {
                    $matricule = $codeAdherent . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                } else {
                    $matricule = $codeAdherent . $nextNumber;
                }
            } else {
                $count = \App\Models\Salarie::where('IDADHERANT', $entreprise->id_entreprise)->count();
                $matricule = $codeAdherent . ($count + 1);
            }
        } else {
            $matricule = $codeAdherent . '1';
        }

        return response()->json(['matricule' => $matricule]);
    }
}
