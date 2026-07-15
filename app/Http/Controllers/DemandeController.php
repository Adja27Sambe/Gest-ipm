<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeRequest;
use App\Http\Requests\UpdateDemandeRequest;
use App\Models\Demande;
use App\Actions\ProcessDemandeAction;

class DemandeController extends Controller
{
    public function index()
    {
        // On récupère les demandes paginées. La policy s'assurera plus tard des restrictions.
        $demandes = Demande::latest()->paginate(10);
        return view('demandes.index', compact('demandes'));
    }

    public function create()
    {
        return view('demandes.create');
    }

    public function store(StoreDemandeRequest $request, ProcessDemandeAction $action)
    {
        // Magie Laravel : Les données sont déjà validées et autorisées par StoreDemandeRequest
        $action->execute($request->validated());
        return redirect()->route('demandes.index')->with('success', 'Demande créée avec succès.');
    }

    public function show(Demande $demande)
    {
        return view('demandes.show', compact('demande'));
    }

    public function edit(Demande $demande)
    {
        return view('demandes.edit', compact('demande'));
    }

    public function update(UpdateDemandeRequest $request, Demande $demande)
    {
        $demande->update($request->validated());
        return redirect()->route('demandes.index')->with('success', 'Demande mise à jour.');
    }

    public function destroy(Demande $demande)
    {
        $demande->delete();
        return redirect()->route('demandes.index')->with('success', 'Demande supprimée.');
    }
}
