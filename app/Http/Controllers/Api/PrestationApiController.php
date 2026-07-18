<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prestation;
use App\Models\Demande;
use App\Services\PlafondService;
use App\Exceptions\PlafondDepasseException;
use App\Exports\PrestationExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PrestationApiController extends Controller
{
    /**
     * Enregistrer une nouvelle prestation
     */
    public function store(Request $request, PlafondService $plafondService)
    {
        $validated = $request->validate([
            'date_prestation' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'taux_prise_charge' => 'required|numeric|min:0|max:100',
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation',
            'id_prestataire' => 'required|exists:prestataire,id_prestataire',
            'id_demande' => 'required|exists:demande,id_demande',
        ]);

        try {
            // 1. Vérification des plafonds AVANT enregistrement (laisse passer ou jette une PlafondDepasseException)
            $plafondService->checkPlafonds(
                $validated['montant'], 
                $validated['id_type_prestation'], 
                $validated['id_demande']
            );

            // 2. Création (l'Observer calculera et remplira le reste_a_charge automatiquement)
            $prestation = Prestation::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Prestation enregistrée avec succès.',
                'data' => $prestation
            ], 201);

        } catch (PlafondDepasseException $e) {
            // L'exception se formatte elle-même grâce à sa méthode render()
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Historique d'un bénéficiaire
     */
    public function history(Request $request)
    {
        $request->validate([
            'id_salarie' => 'nullable|exists:salarie,id_salarie',
            'id_ayant_droit' => 'nullable|exists:ayant_droit,id_ayant_droit',
        ]);

        if (!$request->id_salarie && !$request->id_ayant_droit) {
            return response()->json(['message' => 'Veuillez spécifier id_salarie ou id_ayant_droit'], 400);
        }

        $annee = $request->input('annee', date('Y'));

        $query = Prestation::with(['typePrestation', 'prestataire'])
            ->whereYear('date_prestation', $annee)
            ->whereHas('demande', function ($q) use ($request) {
                if ($request->id_ayant_droit) {
                    $q->where('id_ayant_droit', $request->id_ayant_droit);
                } else {
                    $q->where('id_salarie', $request->id_salarie)
                      ->whereNull('id_ayant_droit');
                }
            });

        // Calcul du cumul
        $cumulAnnuel = $query->sum('montant');
        
        // Liste détaillée
        $prestations = $query->latest('date_prestation')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'annee' => $annee,
            'cumul_annuel' => $cumulAnnuel,
            'data' => $prestations
        ]);
    }

    /**
     * Export Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'id_prestataire' => 'nullable|exists:prestataire,id_prestataire',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $export = new PrestationExport(
            $request->id_prestataire,
            $request->date_debut,
            $request->date_fin
        );

        $filename = 'export_prestations_' . date('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
}
