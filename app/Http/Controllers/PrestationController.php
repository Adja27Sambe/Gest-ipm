<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestation;
use App\Models\TypePrestation;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Models\Demande;
use App\Services\PlafondService;
use App\Exceptions\PlafondDepasseException;
use App\Exports\PrestationExport;
use Maatwebsite\Excel\Facades\Excel;

class PrestationController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestation::whereHas('demande', function($dq) {
            $dq->valides();
        })->with(['typePrestation.parametreCouverture', 'praticien', 'pharmacie', 'demande.salarie', 'demande.ayantDroit', 'factures']);

        // Recherche dynamique
        if ($request->filled('search')) {
            $search = $request->input('search');
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
                ->orWhereHas('typePrestation', function($tq) use ($search) {
                    $tq->where('libelle', 'like', "%{$search}%");
                })
                ->orWhereHas('praticien', function($pq) use ($search) {
                    $pq->where('NOMPRAT', 'like', "%{$search}%");
                })
                ->orWhereHas('pharmacie', function($phq) use ($search) {
                    $phq->where('NOMPHARM', 'like', "%{$search}%");
                });
            });
        }

        // Filtrage par praticien
        if ($request->filled('id_praticien')) {
            $query->where('id_praticien', $request->id_praticien);
        }

        // Filtrage par pharmacie
        if ($request->filled('id_pharmacie')) {
            $query->where('id_pharmacie', $request->id_pharmacie);
        }

        // Filtrage par type d'acte
        if ($request->filled('id_type_prestation')) {
            $query->where('id_type_prestation', $request->id_type_prestation);
        }

        $perPage = $request->input('per_page', 10);
        $prestations = $query->latest('date_prestation')->latest('id_prestation')->paginate($perPage)->withQueryString();
        
        $typesPrestation = TypePrestation::with('parametreCouverture')->orderBy('id_type_prestation')->get();
        $praticiens = Praticien::orderBy('NOMPRAT')->get();
        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();

        $demandes = Demande::valides()
            ->with(['salarie', 'ayantDroit', 'typeDemande', 'bonCommande', 'lettreGarantie', 'praticien', 'pharmacie'])
            ->latest('date_demande')
            ->get();

        // Statistiques globales basées sur les prises en charge valides
        $validPrestations = Prestation::whereHas('demande', fn($q) => $q->valides());
        $stats = [
            'total_prestations' => (clone $validPrestations)->count(),
            'montant_total' => (clone $validPrestations)->sum('montant') ?? 0,
            'part_ipm' => (clone $validPrestations)->selectRaw('SUM(montant - reste_a_charge) as total')->value('total') ?? 0,
            'reste_a_charge' => (clone $validPrestations)->sum('reste_a_charge') ?? 0,
        ];

        return view('prestations.index', compact('prestations', 'typesPrestation', 'praticiens', 'pharmacies', 'demandes', 'stats'));
    }

    /**
     * Redirige vers la vue index avec ouverture automatique du modal de création
     */
    public function create()
    {
        return redirect()->route('prestations.index', ['create' => 1]);
    }

    public function store(Request $request, PlafondService $plafondService)
    {
        $validated = $request->validate([
            'date_prestation' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'taux_prise_charge' => 'nullable|numeric|min:0|max:100',
            'id_type_prestation' => 'nullable|exists:type_prestation,id_type_prestation',
            'id_demande' => 'required|exists:demande,id_demande',
            'id_praticien' => 'nullable|exists:PRATICIE,PRCLEUNIK',
            'id_pharmacie' => 'nullable|exists:PHARMACI,PHCLEUNIK',
            'details_articles' => 'nullable|array',
            'details_articles.*.nom' => 'nullable|string|max:255',
            'details_articles.*.quantite' => 'nullable|numeric|min:0',
            'details_articles.*.montant' => 'nullable|numeric|min:0',
            'details_articles.*.taux' => 'nullable|numeric|min:0|max:100',
            'details_articles.*.id_type_prestation' => 'nullable|exists:type_prestation,id_type_prestation',
        ]);

        $demande = Demande::find($validated['id_demande']);
        if (!$demande || !$demande->est_valide) {
            return redirect()->back()->withInput()->with('error', 'La prise en charge sélectionnée n\'est pas valide ou n\'a pas encore été approuvée.');
        }

        // Auto-assignation du prestataire depuis la demande si non spécifié
        if (empty($validated['id_praticien']) && empty($validated['id_pharmacie']) && $demande) {
            $validated['id_praticien'] = $demande->id_praticien;
            $validated['id_pharmacie'] = $demande->id_pharmacie;
        }

        // Traitement et recalcul des articles et actes avec taux par acte
        if (!empty($validated['details_articles'])) {
            $articles = [];
            $totalArticles = 0;
            $totalPartIpm = 0;
            $firstTypePrestation = null;

            foreach ($validated['details_articles'] as $art) {
                $nom = trim($art['nom'] ?? '');
                $qte = (float)($art['quantite'] ?? 1);
                if ($qte <= 0) $qte = 1;
                $pu = (float)($art['montant'] ?? 0);
                $taux = isset($art['taux']) && $art['taux'] !== '' ? (float)$art['taux'] : (float)($validated['taux_prise_charge'] ?? 75);
                $idType = !empty($art['id_type_prestation']) ? (int)$art['id_type_prestation'] : null;

                if ($idType && !$firstTypePrestation) {
                    $firstTypePrestation = $idType;
                }

                if ($nom !== '' || $pu > 0) {
                    $totalLigne = round($qte * $pu, 2);
                    $partIpmLigne = round($totalLigne * ($taux / 100), 2);
                    $resteLigne = max(0, $totalLigne - $partIpmLigne);

                    $articles[] = [
                        'nom' => $nom,
                        'quantite' => $qte,
                        'montant' => $pu,
                        'taux' => $taux,
                        'id_type_prestation' => $idType,
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
                $validated['reste_a_charge'] = max(0, $totalArticles - $totalPartIpm);
                $validated['taux_prise_charge'] = round(($totalPartIpm / $totalArticles) * 100, 1);
            }
            if ($firstTypePrestation && empty($validated['id_type_prestation'])) {
                $validated['id_type_prestation'] = $firstTypePrestation;
            }
        } else {
            $validated['details_articles'] = null;
        }

        // Si id_type_prestation n'est toujours pas défini, déduire intelligemment
        if (empty($validated['id_type_prestation'])) {
            if ($demande->is_bon_commande || $demande->id_pharmacie) {
                $phType = TypePrestation::where('libelle', 'like', '%Pharmacie%')->value('id_type_prestation');
                $validated['id_type_prestation'] = $phType ?: TypePrestation::first()?->id_type_prestation;
            } else {
                $validated['id_type_prestation'] = TypePrestation::first()?->id_type_prestation;
            }
        }

        try {
            // Vérification des plafonds si un type de prestation est défini
            if (!empty($validated['id_type_prestation'])) {
                $plafondService->checkPlafonds(
                    (float) $validated['montant'], 
                    (int) $validated['id_type_prestation'], 
                    (int) $validated['id_demande']
                );
            }

            Prestation::create($validated);

            return redirect()->route('prestations.index')->with('success', 'Prestation médicale enregistrée avec succès.');
        } catch (PlafondDepasseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    /**
     * Formulaire d'édition d'une prestation
     */
    public function edit(Prestation $prestation)
    {
        $prestation->load(['typePrestation.parametreCouverture', 'praticien', 'pharmacie', 'demande.salarie', 'demande.ayantDroit', 'demande.typeDemande', 'demande.bonCommande', 'demande.lettreGarantie']);
        
        $typesPrestation = TypePrestation::with('parametreCouverture')->orderBy('id_type_prestation')->get();
        $praticiens = Praticien::orderBy('NOMPRAT')->get();
        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();
        $demandes = Demande::where(function($q) use ($prestation) {
                $q->valides()
                  ->orWhere('id_demande', $prestation->id_demande);
            })
            ->with(['salarie', 'ayantDroit', 'typeDemande', 'bonCommande', 'lettreGarantie', 'praticien', 'pharmacie'])
            ->latest('date_demande')
            ->get();

        return view('prestations.edit', compact('prestation', 'typesPrestation', 'praticiens', 'pharmacies', 'demandes'));
    }

    /**
     * Mise à jour d'une prestation existante avec recalcul et contrôle des plafonds
     */
    public function update(Request $request, Prestation $prestation, PlafondService $plafondService)
    {
        $validated = $request->validate([
            'date_prestation' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'taux_prise_charge' => 'nullable|numeric|min:0|max:100',
            'id_type_prestation' => 'nullable|exists:type_prestation,id_type_prestation',
            'id_demande' => 'required|exists:demande,id_demande',
            'id_praticien' => 'nullable|exists:PRATICIE,PRCLEUNIK',
            'id_pharmacie' => 'nullable|exists:PHARMACI,PHCLEUNIK',
            'details_articles' => 'nullable|array',
            'details_articles.*.nom' => 'nullable|string|max:255',
            'details_articles.*.quantite' => 'nullable|numeric|min:0',
            'details_articles.*.montant' => 'nullable|numeric|min:0',
            'details_articles.*.taux' => 'nullable|numeric|min:0|max:100',
            'details_articles.*.id_type_prestation' => 'nullable|exists:type_prestation,id_type_prestation',
        ]);

        $demande = Demande::find($validated['id_demande']);
        if (!$demande || (!$demande->est_valide && (int)$demande->id_demande !== (int)$prestation->id_demande)) {
            return redirect()->back()->withInput()->with('error', 'La prise en charge sélectionnée n\'est pas valide ou n\'a pas encore été approuvée.');
        }

        // Auto-assignation du prestataire depuis la demande si non spécifié
        if (empty($validated['id_praticien']) && empty($validated['id_pharmacie']) && $demande) {
            $validated['id_praticien'] = $demande->id_praticien;
            $validated['id_pharmacie'] = $demande->id_pharmacie;
        }

        // Traitement et recalcul des articles / actes avec taux par acte
        if (!empty($validated['details_articles'])) {
            $articles = [];
            $totalArticles = 0;
            $totalPartIpm = 0;
            $firstTypePrestation = null;

            foreach ($validated['details_articles'] as $art) {
                $nom = trim($art['nom'] ?? '');
                $qte = (float)($art['quantite'] ?? 1);
                if ($qte <= 0) $qte = 1;
                $pu = (float)($art['montant'] ?? 0);
                $taux = isset($art['taux']) && $art['taux'] !== '' ? (float)$art['taux'] : (float)($validated['taux_prise_charge'] ?? $prestation->taux_prise_charge ?? 75);
                $idType = !empty($art['id_type_prestation']) ? (int)$art['id_type_prestation'] : null;

                if ($idType && !$firstTypePrestation) {
                    $firstTypePrestation = $idType;
                }

                if ($nom !== '' || $pu > 0) {
                    $totalLigne = round($qte * $pu, 2);
                    $partIpmLigne = round($totalLigne * ($taux / 100), 2);
                    $resteLigne = max(0, $totalLigne - $partIpmLigne);

                    $articles[] = [
                        'nom' => $nom,
                        'quantite' => $qte,
                        'montant' => $pu,
                        'taux' => $taux,
                        'id_type_prestation' => $idType,
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
                $validated['reste_a_charge'] = max(0, $totalArticles - $totalPartIpm);
                $validated['taux_prise_charge'] = round(($totalPartIpm / $totalArticles) * 100, 1);
            }
            if ($firstTypePrestation && empty($validated['id_type_prestation'])) {
                $validated['id_type_prestation'] = $firstTypePrestation;
            }
        } else {
            $validated['details_articles'] = null;
        }

        if (empty($validated['id_type_prestation'])) {
            $validated['id_type_prestation'] = $prestation->id_type_prestation ?: TypePrestation::first()?->id_type_prestation;
        }

        try {
            // Vérification des plafonds en excluant la prestation courante pour éviter le faux dépassement
            if (!empty($validated['id_type_prestation'])) {
                $plafondService->checkPlafonds(
                    (float) $validated['montant'], 
                    (int) $validated['id_type_prestation'], 
                    (int) $validated['id_demande'],
                    $prestation->id_prestation
                );
            }

            $prestation->update($validated);

            return redirect()->route('prestations.index')->with('success', 'Prestation #' . $prestation->id_prestation . ' mise à jour avec succès.');
        } catch (PlafondDepasseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Suppression d'une prestation
     */
    public function destroy(Prestation $prestation)
    {
        try {
            $id = $prestation->id_prestation;
            $prestation->delete();
            return redirect()->route('prestations.index')->with('success', 'Prestation #' . $id . ' supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Impossible de supprimer cette prestation : ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $export = new PrestationExport(
            $request->id_prestataire,
            $request->date_debut,
            $request->date_fin
        );

        $filename = 'export_prestations_' . date('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
}
