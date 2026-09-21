<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeRequest;
use App\Http\Requests\UpdateDemandeRequest;
use App\Services\DemandeService;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Salarie;
use App\Models\TypePrestation;
use App\Models\Praticien;
use App\Models\Pharmacie;

class DemandeController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 5);
        $demandes = Demande::with(['typeDemande', 'salarie', 'ayantDroit', 'bonCommande', 'feuilleMaladie', 'lettreGarantie'])
            ->orderByDesc('date_demande')
            ->orderByDesc('id_demande')
            ->paginate($perPage)
            ->withQueryString();
        $typesDemande = TypeDemande::select('id_type_demande', 'libelle')->get();
        $salaries = Salarie::where('PARACTIF', 1)
            ->with(['ayantsDroit' => function($q) {
                $q->select('id_ayant_droit', 'id_salarie', 'nom', 'prenom', 'lien_parente', 'date_naissance');
            }])
            ->select('IDPARTICIPANT', 'NOM', 'PRENOM', 'MATRICULE', 'Date_Naissance')
            ->orderBy('NOM')
            ->get();
        $typesPrestation = TypePrestation::select('id_type_prestation', 'libelle')->get();
        $praticiens = Praticien::orderBy('NOMPRAT')->get();
        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();
        
        // KPIs pour le Dashboard
        $stats = [
            'total' => Demande::count(),
            'approuvees' => Demande::where('statut', 'Approuvée')->count(),
            'en_cours' => Demande::where('statut', 'En cours')->count(),
            'en_attente' => Demande::where('statut', 'en_attente')->count(),
        ];

        return view('demandes.index', compact('demandes', 'typesDemande', 'salaries', 'typesPrestation', 'praticiens', 'pharmacies', 'stats'));
    }

    public function create()
    {
        $typesDemande = TypeDemande::all();
        $salaries = Salarie::where('PARACTIF', 1)
            ->select('IDPARTICIPANT', 'NOM', 'PRENOM', 'MATRICULE', 'Date_Naissance')
            ->with(['ayantsDroit' => function($q) {
                $q->where('statut', 'actif');
            }])
            ->orderBy('NOM')
            ->get();
        $praticiens = Praticien::orderBy('NOMPRAT')->get();
        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();

        return view('demandes.create', compact('typesDemande', 'salaries', 'praticiens', 'pharmacies'));
    }

    public function store(StoreDemandeRequest $request, DemandeService $service)
    {
        try {
            $service->traiterDemande($request->validated());
            return redirect()->route('demandes.index')->with('success', 'Prise en charge médicale générée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
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
        return redirect()->route('demandes.index')->with('success', 'Prise en charge médical mise à jour.');
    }

    public function destroy(Demande $demande)
    {
        $demande->delete();
        return redirect()->route('demandes.index')->with('success', 'Prise en charge médical supprimée.');
    }

    /**
     * Recherche un participant (Salarié) par son matricule.
     */
    public function rechercheParticipant(\Illuminate\Http\Request $request)
    {
        $matricule = $request->query('matricule');

        if (!$matricule) {
            return response()->json(['error' => 'Veuillez renseigner un matricule.'], 400);
        }

        $salarie = Salarie::with(['ayantsDroit' => function ($query) {
            $query->where('statut', 'Actif');
        }])->where('MATRICULE', $matricule)->first();

        if (!$salarie) {
            return response()->json(['error' => 'Participant non trouvé.'], 404);
        }

        return response()->json([
            'id_salarie' => $salarie->id_salarie,
            'nom_complet' => $salarie->nom . ' ' . $salarie->prenom,
            'ayants_droit' => $salarie->ayantsDroit
        ]);
    }
}
