<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestation;
use App\Models\Demande;
use App\Models\Pharmacie;
use App\Models\TypePrestation;
use App\Services\PlafondService;

class FacturationPharmacieController extends Controller
{
    public function index(Request $request)
    {
        $pharmacieType = TypePrestation::where('libelle', 'like', '%Pharmacie%')->first();
        $idTypePharmacie = $pharmacieType?->id_type_prestation;

        $query = Prestation::where(function($q) use ($idTypePharmacie) {
            $q->whereNotNull('id_pharmacie');
            if ($idTypePharmacie) {
                $q->orWhere('id_type_prestation', $idTypePharmacie);
            }
        })->with(['demande.salarie', 'demande.ayantDroit', 'pharmacie', 'typePrestation.parametreCouverture', 'factures']);

        // Recherche par mot clé
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->whereHas('demande.salarie', function($sq) use ($search) {
                    $sq->where('NOM', 'like', "%{$search}%")
                       ->orWhere('PRENOM', 'like', "%{$search}%")
                       ->orWhere('MATRICULE', 'like', "%{$search}%");
                })
                ->orWhereHas('demande.ayantDroit', function($aq) use ($search) {
                    $aq->where('nom', 'like', "%{$search}%")
                       ->orWhere('prenom', 'like', "%{$search}%");
                })
                ->orWhereHas('pharmacie', function($phq) use ($search) {
                    $phq->where('NOMPHARM', 'like', "%{$search}%")
                       ->orWhere('nom', 'like', "%{$search}%");
                });
            });
        }

        // Filtre pharmacie
        if ($request->filled('id_pharmacie')) {
            $query->where('id_pharmacie', $request->id_pharmacie);
        }

        // Filtre date
        if ($request->filled('date_debut')) {
            $query->whereDate('date_prestation', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_prestation', '<=', $request->date_fin);
        }

        $perPage = $request->input('per_page', 15);
        $prestations = $query->latest('date_prestation')->latest('id_prestation')->paginate($perPage)->withQueryString();

        // Statistiques Pharmacie
        $statsQuery = clone $query;
        $totalMontant = (clone $statsQuery)->sum('montant') ?? 0;
        $resteACharge = (clone $statsQuery)->sum('reste_a_charge') ?? 0;
        $stats = [
            'total_dispensations' => (clone $statsQuery)->count(),
            'montant_total' => $totalMontant,
            'part_ipm' => max(0, $totalMontant - $resteACharge),
            'reste_a_charge' => $resteACharge,
        ];

        // Bons de commande éligibles (validés pour pharmacie)
        $bonsCommande = Demande::valides()
            ->where(function($q) {
                $q->whereNotNull('id_pharmacie')
                  ->orWhereHas('typeDemande', fn($tq) => $tq->where('libelle', 'like', '%bon%'));
            })
            ->with(['salarie', 'ayantDroit', 'bonCommande', 'pharmacie'])
            ->latest('date_demande')
            ->get();

        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();

        return view('facturation.pharmacie', compact('prestations', 'bonsCommande', 'pharmacies', 'stats', 'pharmacieType'));
    }

    public function store(Request $request, PlafondService $plafondService)
    {
        $validated = $request->validate([
            'date_prestation' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'taux_prise_charge' => 'nullable|numeric|min:0|max:100',
            'id_demande' => 'required|exists:demande,id_demande',
            'id_pharmacie' => 'nullable|exists:PHARMACI,PHCLEUNIK',
            'sous_couvert' => 'nullable|boolean',
            'details_articles' => 'nullable|array',
            'details_articles.*.nom' => 'nullable|string|max:255',
            'details_articles.*.quantite' => 'nullable|numeric|min:0',
            'details_articles.*.montant' => 'nullable|numeric|min:0',
            'details_articles.*.taux' => 'nullable|numeric|min:0|max:100',
        ]);

        $demande = Demande::find($validated['id_demande']);
        if (!$demande || !$demande->est_valide) {
            return redirect()->back()->withInput()->with('error', 'Le bon de commande sélectionné n\'est pas valide ou approuvé.');
        }

        // Auto-assignation de la pharmacie si non renseignée
        if (empty($validated['id_pharmacie']) && $demande->id_pharmacie) {
            $validated['id_pharmacie'] = $demande->id_pharmacie;
        }

        // Associer le type de prestation Pharmacie
        $pharmacieType = TypePrestation::where('libelle', 'like', '%Pharmacie%')->first();
        $validated['id_type_prestation'] = $pharmacieType?->id_type_prestation ?? TypePrestation::first()?->id_type_prestation;

        // Option Sous Couvert
        $isSousCouvert = $request->boolean('sous_couvert') || (!empty($demande) && !empty($demande->sous_couvert));
        if ($isSousCouvert) {
            $validated['sous_couvert'] = true;
            $validated['taux_prise_charge'] = 100.00;
            $validated['reste_a_charge'] = 0.00;
        }

        // Gestion des articles (désignation non obligatoire)
        if (!empty($validated['details_articles'])) {
            $articles = [];
            $totalArticles = 0;
            $totalPartIpm = 0;

            foreach ($validated['details_articles'] as $art) {
                $pu = (float)($art['montant'] ?? 0);
                $nom = trim($art['nom'] ?? '');
                if ($nom === '' && $pu > 0) {
                    $nom = 'Médicaments / Produits pharmaceutiques';
                }

                $qte = (float)($art['quantite'] ?? 1);
                if ($qte <= 0) $qte = 1;
                $taux = $isSousCouvert ? 100.00 : (isset($art['taux']) && $art['taux'] !== '' ? (float)$art['taux'] : (float)($validated['taux_prise_charge'] ?? 75));

                if ($nom !== '' || $pu > 0) {
                    $totalLigne = round($qte * $pu, 2);
                    $partIpmLigne = $isSousCouvert ? $totalLigne : round($totalLigne * ($taux / 100), 2);
                    $resteLigne = $isSousCouvert ? 0.00 : max(0, $totalLigne - $partIpmLigne);

                    $articles[] = [
                        'nom' => $nom,
                        'quantite' => $qte,
                        'montant' => $pu,
                        'taux' => $taux,
                        'id_type_prestation' => $validated['id_type_prestation'],
                        'total' => $totalLigne,
                        'part_ipm' => $partIpmLigne,
                        'reste_a_charge' => $resteLigne,
                    ];
                    $totalArticles += $totalLigne;
                    $totalPartIpm += $partIpmLigne;
                }
            }

            $validated['details_articles'] = !empty($articles) ? $articles : null;
            if ($totalArticles > 0) {
                $validated['montant'] = $totalArticles;
                $validated['reste_a_charge'] = $isSousCouvert ? 0.00 : max(0, $totalArticles - $totalPartIpm);
                $validated['taux_prise_charge'] = $isSousCouvert ? 100.00 : round(($totalPartIpm / $totalArticles) * 100, 1);
            }
        }

        try {
            Prestation::create($validated);
            return redirect()->route('facturation.pharmacie')->with('success', 'Dispensation pharmaceutique enregistrée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }
}
