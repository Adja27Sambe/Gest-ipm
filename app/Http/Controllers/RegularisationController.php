<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Demande;
use App\Models\Salarie;
use App\Models\TypeDemande;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Models\HistoriqueMouvement;
use App\Services\DemandeService;
use Illuminate\Support\Facades\Auth;

class RegularisationController extends Controller
{
    /**
     * Affiche la liste des dossiers "À régulariser" et l'historique des régularisations.
     */
    public function index(Request $request)
    {
        $query = Demande::where('is_regularisation', true)
            ->with(['salarie.entreprise', 'ayantDroit', 'typeDemande', 'praticien', 'pharmacie', 'prestation']);

        // Recherche par mot-clé (participant, matricule, motif)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->whereHas('salarie', function($sq) use ($search) {
                    $sq->where('NOM', 'like', "%{$search}%")
                       ->orWhere('PRENOM', 'like', "%{$search}%")
                       ->orWhere('MATRICULE', 'like', "%{$search}%");
                })
                ->orWhereHas('ayantDroit', function($aq) use ($search) {
                    $aq->where('nom', 'like', "%{$search}%")
                       ->orWhere('prenom', 'like', "%{$search}%");
                })
                ->orWhere('motif_regularisation', 'like', "%{$search}%")
                ->orWhere('situation_initiale', 'like', "%{$search}%")
                ->orWhere('numero_demande', 'like', "%{$search}%");
            });
        }

        // Filtre de statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $perPage = $request->input('per_page', 15);
        $regularisations = $query->latest('date_demande')->latest('id_demande')->paginate($perPage)->withQueryString();

        // Statistiques régularisations
        $stats = [
            'total' => Demande::where('is_regularisation', true)->count(),
            'a_regulariser' => Demande::where('is_regularisation', true)->whereIn('statut', ['en_attente', 'transmise'])->count(),
            'regularisees' => Demande::where('is_regularisation', true)->where('statut', 'valide')->count(),
            'sous_couvert' => Demande::where('is_regularisation', true)->where('sous_couvert', true)->count(),
        ];

        // Participants, types de demande et prestataires pour la modale de création
        $salaries = Salarie::with(['ayantsDroit' => fn($q) => $q->where('statut', 'Actif')])
            ->where('PARACTIF', 1)
            ->orderBy('NOM')
            ->limit(100)
            ->get();

        $typesDemande = TypeDemande::orderBy('id_type_demande')->get();
        $praticiens = Praticien::orderBy('NOMPRAT')->get();
        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();

        return view('regularisations.index', compact('regularisations', 'stats', 'salaries', 'typesDemande', 'praticiens', 'pharmacies'));
    }

    /**
     * Enregistrement d'une régularisation avec traçabilité complète.
     */
    public function store(Request $request, DemandeService $service)
    {
        $validated = $request->validate([
            'id_salarie' => 'required|exists:PARTICIPANT,IDPARTICIPANT',
            'id_ayant_droit' => 'nullable|exists:ayant_droit,id_ayant_droit',
            'id_type_demande' => 'required|exists:type_demande,id_type_demande',
            'situation_initiale' => 'required|string|max:1000',
            'motif_regularisation' => 'required|string|max:1000',
            'date_demande' => 'required|date',
            'nom_ordonnateur' => 'nullable|string|max:255',
            'id_praticien' => 'nullable|exists:PRATICIE,PRCLEUNIK',
            'id_pharmacie' => 'nullable|exists:PHARMACI,PHCLEUNIK',
            'choix_acte' => 'nullable',
            'sous_couvert' => 'nullable|boolean',
            'observations' => 'nullable|string|max:1000',
        ]);

        $validated['is_regularisation'] = true;
        $validated['motif'] = "RÉGULARISATION : " . $validated['motif_regularisation'];
        
        // Par défaut, une régularisation est validée d'office pour permettre la facturation
        $validated['statut'] = 'valide';

        try {
            $demande = $service->traiterDemande($validated);

            // Audit manuel renforcé pour la traçabilité complète de l'opération
            HistoriqueMouvement::create([
                'id_user' => Auth::id(),
                'type_mouvement' => 'REGULARISATION',
                'description' => "Enregistrement d'un dossier de régularisation pour la demande #{$demande->id_demande}. Motif : {$validated['motif_regularisation']}",
                'ancien_statut' => 'Nouveau',
                'nouveau_statut' => 'Régularisé',
                'module' => 'Facturation / Régularisation',
                'valeurs_modifiees' => [
                    'situation_initiale' => $validated['situation_initiale'],
                    'motif_regularisation' => $validated['motif_regularisation'],
                    'sous_couvert' => !empty($validated['sous_couvert']),
                    'date_regularisation' => now()->toDateTimeString(),
                    'operateur' => Auth::user()->name ?? 'Utilisateur Système',
                ],
            ]);

            return redirect()->route('regularisations.index')->with('success', 'Dossier de régularisation enregistré avec succès avec traçabilité complète.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails et la traçabilité complète d'une régularisation.
     */
    public function show(Demande $demande)
    {
        $demande->load(['salarie.entreprise', 'ayantDroit', 'typeDemande', 'praticien', 'pharmacie', 'prestation', 'bonCommande', 'lettreGarantie']);

        $audits = HistoriqueMouvement::where(function($q) use ($demande) {
                $q->where('description', 'like', "%#{$demande->id_demande}%")
                  ->orWhere('description', 'like', "%{$demande->numero_demande}%");
            })
            ->latest('date_heure')
            ->latest('id_historique')
            ->get();

        return view('regularisations.show', compact('demande', 'audits'));
    }
}
