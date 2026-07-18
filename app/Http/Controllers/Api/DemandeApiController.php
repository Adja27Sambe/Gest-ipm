<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDemandeRequest;
use App\Services\DemandeService;
use App\Models\Demande;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DemandeApiController extends Controller
{
    protected $demandeService;

    public function __construct(DemandeService $demandeService)
    {
        $this->demandeService = $demandeService;
    }

    /**
     * Crée une demande (Bon, Feuille, Lettre)
     */
    public function store(StoreDemandeRequest $request)
    {
        try {
            $result = $this->demandeService->traiterDemande($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Demande générée avec succès.',
                'data' => $result
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Historique des demandes par salarié ou entreprise
     */
    public function history(Request $request)
    {
        $query = Demande::with(['typeDemande', 'bonCommande', 'feuilleMaladie', 'lettreGarantie']);

        if ($request->has('id_salarie')) {
            $query->where('id_salarie', $request->id_salarie);
        } elseif ($request->has('id_entreprise')) {
            // Filtrer via l'entreprise du salarié
            $query->whereHas('salarie', function($q) use ($request) {
                $q->where('id_entreprise', $request->id_entreprise);
            });
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        $demandes = $query->orderBy('date_demande', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $demandes
        ]);
    }

    /**
     * Générer le PDF de la demande (Bon, Feuille, Lettre)
     */
    public function generatePdf($id_demande)
    {
        $demande = Demande::with(['typeDemande', 'salarie', 'ayantDroit', 'bonCommande', 'feuilleMaladie', 'lettreGarantie'])->findOrFail($id_demande);
        
        $libelle = strtolower($demande->typeDemande->libelle);

        $view = '';
        $fileName = '';

        if (str_contains($libelle, 'bon de commande')) {
            $view = 'pdf.bon_commande';
            $fileName = 'bon_commande_' . ($demande->bonCommande->numero_bon ?? $demande->id_demande) . '.pdf';
        } elseif (str_contains($libelle, 'feuille de maladie')) {
            $view = 'pdf.feuille_maladie';
            $fileName = 'feuille_maladie_' . ($demande->feuilleMaladie->numero_feuille ?? $demande->id_demande) . '.pdf';
        } elseif (str_contains($libelle, 'lettre de garantie')) {
            $view = 'pdf.lettre_garantie';
            $fileName = 'lettre_garantie_' . ($demande->lettreGarantie->numero_lettre ?? $demande->id_demande) . '.pdf';
        } else {
            return response()->json(['error' => 'Type de document inconnu pour la génération PDF.'], 400);
        }

        $pdf = Pdf::loadView($view, compact('demande'));
        return $pdf->download($fileName);
    }
}
