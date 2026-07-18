<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeRequest;
use App\Http\Requests\UpdateDemandeRequest;
use App\Services\Demandes\DemandeService;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Salarie;
use App\Models\TypePrestation;

class DemandeController extends Controller
{
    public function index()
    {
        $demandes = Demande::with(['typeDemande', 'salarie:id_salarie,nom,prenom', 'ayantDroit:id_ayant_droit,nom,prenom', 'bonCommande', 'feuilleMaladie', 'lettreGarantie'])->latest('date_demande')->paginate(15);
        $typesDemande = TypeDemande::select('id_type_demande', 'libelle')->get();
        $salaries = Salarie::with(['ayantsDroit' => function($q) {
            $q->select('id_ayant_droit', 'id_salarie', 'nom', 'prenom');
        }])->select('id_salarie', 'nom', 'prenom', 'matricule')->get();
        $typesPrestation = TypePrestation::select('id_type_prestation', 'libelle')->get();
        $prestataires = \App\Models\Prestataire::with('type')->get();
        
        // KPIs pour le Dashboard
        $stats = [
            'total' => Demande::count(),
            'approuvees' => Demande::where('statut', 'Approuvée')->count(),
            'en_cours' => Demande::whereIn('statut', ['En cours', 'en_attente'])->count(),
        ];

        return view('demandes.index', compact('demandes', 'typesDemande', 'salaries', 'typesPrestation', 'prestataires', 'stats'));
    }

    public function create()
    {
        return view('demandes.create');
    }

    public function store(StoreDemandeRequest $request, DemandeService $service)
    {
        try {
            $service->createDemande($request->validated());
            return redirect()->route('demandes.index')->with('success', 'Document généré avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('demandes.index')->with('error', 'Erreur : ' . $e->getMessage());
        }
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
