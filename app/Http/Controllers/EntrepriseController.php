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
            $query->where('raison_sociale', 'like', '%' . $request->search . '%')
                  ->orWhere('code_adherent', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $entreprises = $query->latest()->paginate(10);
        
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
                ->with('error', 'Impossible de supprimer cette entreprise car des salariés y sont rattachés.');
        }

        $entreprise->delete();
        return redirect()->route('entreprises.index')->with('success', 'Entreprise supprimée.');
    }
}
