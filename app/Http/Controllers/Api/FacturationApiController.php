<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\PaiementPrestataire;
use App\Exports\PaiementsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturationApiController extends Controller
{
    /**
     * Tableau de bord des factures impayées / en retard.
     */
    public function facturesImpayees(Request $request)
    {
        $query = Facture::with('prestataire')
            ->whereIn('statut_paiement', ['en_attente', 'partiellement_payee']);

        if ($request->has('id_prestataire')) {
            $query->where('id_prestataire', $request->id_prestataire);
        }

        $factures = $query->orderBy('date_facture', 'asc')->paginate($request->input('per_page', 15));

        // Calcul du total dû global sur les factures impayées correspondant aux filtres
        // Note: On récupère la collection pour utiliser l'accessor
        $toutesImpayees = (clone $query)->get();
        $totalDu = $toutesImpayees->sum('soldeRestant');

        // Ajout explicite du soldeRestant dans la réponse JSON pour chaque facture
        $factures->getCollection()->transform(function ($facture) {
            $facture->solde_restant = $facture->soldeRestant;
            return $facture;
        });

        return response()->json([
            'success' => true,
            'total_restant_du' => $totalDu,
            'data' => $factures
        ]);
    }

    /**
     * Export comptable des paiements.
     */
    public function exportPaiements(Request $request)
    {
        $idPrestataire = $request->input('id_prestataire');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $format = $request->input('format', 'csv'); // csv, pdf, excel

        $export = new PaiementsExport($idPrestataire, $dateDebut, $dateFin);
        $timestamp = date('Ymd_His');

        if ($format === 'pdf') {
            $query = PaiementPrestataire::with(['facture.prestataire']);
            if ($idPrestataire) {
                $query->whereHas('facture', function ($q) use ($idPrestataire) {
                    $q->where('id_prestataire', $idPrestataire);
                });
            }
            if ($dateDebut) $query->where('date_paiement', '>=', $dateDebut);
            if ($dateFin) $query->where('date_paiement', '<=', $dateFin);
            
            $paiements = $query->orderBy('date_paiement', 'desc')->get();
            $pdf = Pdf::loadView('exports.paiements', compact('paiements'));
            return $pdf->download("paiements_export_{$timestamp}.pdf");
        } elseif ($format === 'excel') {
            return Excel::download($export, "paiements_export_{$timestamp}.xlsx");
        }

        // Par défaut, CSV
        return Excel::download($export, "paiements_export_{$timestamp}.csv", \Maatwebsite\Excel\Excel::CSV);
    }
}
