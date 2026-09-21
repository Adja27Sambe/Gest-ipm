<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\PaiementPrestataire;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Models\Prestation;

class FacturationController extends Controller
{
    private function getPartenaires()
    {
        $praticiens = Praticien::all()->map(function($p) {
            $p->type = 'praticien';
            $p->id = $p->id_praticien;
            $p->value = 'praticien_' . $p->id;
            return $p;
        });
        
        $pharmacies = Pharmacie::all()->map(function($p) {
            $p->type = 'pharmacie';
            $p->id = $p->id_pharmacie;
            $p->value = 'pharmacie_' . $p->id;
            return $p;
        });

        return $praticiens->concat($pharmacies)->sortBy('nom');
    }

    /**
     * Affiche le tableau de bord des factures (impayées / en attente).
     */
    public function index(Request $request)
    {
        $partenaires = $this->getPartenaires();
        
        $query = Facture::with(['praticien', 'pharmacie', 'paiementPrestataires'])
            ->withSum('paiementPrestataires', 'montant');
            
        // Filtre de statut
        if ($request->has('statut') && $request->statut === 'impayees') {
            $query->whereIn('statut_paiement', ['en_attente', 'partiellement_payee']);
        } elseif ($request->has('statut') && $request->statut === 'payees') {
            $query->where('statut_paiement', 'payee');
        } else {
            // Par défaut, pas de filtre (on affiche toutes les factures)
        }
        
        // Recherche générale
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('numero_facture', 'like', "%{$searchTerm}%")
                  ->orWhere('id_facture', 'like', "%{$searchTerm}%")
                  ->orWhereHas('praticien', function($qp) use ($searchTerm) {
                      $qp->where('nom', 'like', "%{$searchTerm}%")
                         ->orWhere('NOMPRAT', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('pharmacie', function($qph) use ($searchTerm) {
                      $qph->where('nom', 'like', "%{$searchTerm}%")
                         ->orWhere('NOMPHARM', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Filtre Partenaire de santé (champ de saisie ou code partenaire)
        if ($request->filled('partenaire')) {
            $partenaireTerm = trim($request->partenaire);
            $parts = explode('_', $partenaireTerm);
            if (count($parts) == 2 && in_array($parts[0], ['praticien', 'pharmacie']) && is_numeric($parts[1])) {
                if ($parts[0] === 'praticien') {
                    $query->where('id_praticien', $parts[1]);
                } elseif ($parts[0] === 'pharmacie') {
                    $query->where('id_pharmacie', $parts[1]);
                }
            } else {
                $query->where(function($q) use ($partenaireTerm) {
                    $q->whereHas('praticien', function($qp) use ($partenaireTerm) {
                        $qp->where('nom', 'like', "%{$partenaireTerm}%")
                           ->orWhere('NOMPRAT', 'like', "%{$partenaireTerm}%");
                    })->orWhereHas('pharmacie', function($qph) use ($partenaireTerm) {
                        $qph->where('nom', 'like', "%{$partenaireTerm}%")
                           ->orWhere('NOMPHARM', 'like', "%{$partenaireTerm}%");
                    });
                });
            }
        }
        
        $perPage = $request->input('per_page', 10);
        $factures = $query->orderBy('date_facture', 'desc')->orderBy('id_facture', 'desc')->paginate($perPage)->withQueryString();
        
        // Calcul du total du
        $totalDu = (clone $query)->get()->sum(function($facture) {
            return ($facture->montant ?? 0) - ($facture->paiement_prestataires_sum_montant ?? 0);
        });

        return view('factures.index', compact('factures', 'partenaires', 'totalDu'));
    }

    /**
     * Affiche le formulaire de création d'une facture.
     */
    public function create(Request $request)
    {
        $partenaires = $this->getPartenaires();
        $prestationsNonFacturees = collect();
        $partenaireSelectionne = null;

        if ($request->has('partenaire') && $request->partenaire != '') {
            $parts = explode('_', $request->partenaire);
            if (count($parts) == 2) {
                if ($parts[0] === 'praticien') {
                    $partenaireSelectionne = Praticien::find($parts[1]);
                    if ($partenaireSelectionne) {
                        $partenaireSelectionne->type_partenaire = 'praticien';
                        $prestationsNonFacturees = Prestation::where('id_praticien', $parts[1])
                            ->whereDoesntHave('factures')
                            ->with(['demande.salarie', 'demande.ayantDroit', 'typePrestation'])
                            ->orderBy('date_prestation', 'desc')
                            ->get();
                    }
                } elseif ($parts[0] === 'pharmacie') {
                    $partenaireSelectionne = Pharmacie::find($parts[1]);
                    if ($partenaireSelectionne) {
                        $partenaireSelectionne->type_partenaire = 'pharmacie';
                        $prestationsNonFacturees = Prestation::where('id_pharmacie', $parts[1])
                            ->whereDoesntHave('factures')
                            ->with(['demande.salarie', 'demande.ayantDroit', 'typePrestation'])
                            ->orderBy('date_prestation', 'desc')
                            ->get();
                    }
                }
            }
        }

        return view('factures.create', compact('partenaires', 'prestationsNonFacturees', 'partenaireSelectionne'));
    }

    /**
     * Enregistre une nouvelle facture.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partenaire' => 'required|string',
            'numero_facture' => 'required|string|unique:facture,numero_facture|max:50',
            'date_facture' => 'required|date',
            'prestations' => 'required|array',
            'prestations.*' => 'exists:prestation,id_prestation'
        ], [
            'prestations.required' => 'Veuillez sélectionner au moins une prestation.'
        ]);

        $parts = explode('_', $request->partenaire);
        if (count($parts) != 2 || !in_array($parts[0], ['praticien', 'pharmacie'])) {
            return back()->withErrors(['partenaire' => 'Partenaire invalide.']);
        }

        $prestations = Prestation::whereIn('id_prestation', $request->prestations)->get();
        
        $montantFacture = $prestations->sum(function($p) {
            return $p->montant - $p->reste_a_charge;
        });

        $factureData = [
            'numero_facture' => $request->numero_facture,
            'date_facture' => $request->date_facture,
            'montant' => $montantFacture,
            'statut_paiement' => 'en_attente',
        ];

        if ($parts[0] === 'praticien') {
            $factureData['id_praticien'] = $parts[1];
        } else {
            $factureData['id_pharmacie'] = $parts[1];
        }

        $facture = Facture::create($factureData);
        $facture->prestations()->attach($request->prestations);

        return redirect()->route('factures.index')->with('success', 'Facture générée avec succès.');
    }

    /**
     * Affiche les détails d'une facture spécifique.
     */
    public function show($id)
    {
        $facture = Facture::with(['praticien', 'pharmacie', 'prestations.demande.salarie', 'prestations.typePrestation', 'paiementPrestataires'])->findOrFail($id);
        return view('factures.show', compact('facture'));
    }

    /**
     * Enregistre un nouveau paiement pour la facture.
     */
    public function storePaiement(Request $request, $id)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|string',
            'reference_transaction' => 'nullable|string'
        ]);

        $facture = Facture::findOrFail($id);
        
        PaiementPrestataire::create([
            'id_facture' => $facture->id_facture,
            'date_paiement' => now(),
            'montant' => $request->montant,
            'mode_paiement' => $request->mode_paiement,
            'reference' => $request->reference_transaction
        ]);

        return redirect()->route('factures.show', $id)->with('success', 'Paiement enregistré avec succès.');
    }
}
