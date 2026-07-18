<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cotisation;
use Illuminate\Support\Facades\DB;

class CotisationApiController extends Controller
{
    /**
     * Suivi de statut de paiement par salarié / période
     */
    public function statut(Request $request)
    {
        $request->validate([
            'id_salarie' => 'nullable|exists:salarie,id_salarie',
            'periode' => 'nullable|string', // ex: 2023-10
        ]);

        $query = Cotisation::with('salarie');

        if ($request->has('id_salarie') && $request->id_salarie != '') {
            $query->where('id_salarie', $request->id_salarie);
        }

        if ($request->has('periode') && $request->periode != '') {
            $query->where('periode', $request->periode);
        }

        $cotisations = $query->orderBy('periode', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $cotisations
        ]);
    }

    /**
     * Rapport de recouvrement (taux payé/impayé) par entreprise, requête agrégée avec groupBy
     */
    public function recouvrement(Request $request)
    {
        $rapports = DB::table('cotisation')
            ->join('salarie', 'cotisation.id_salarie', '=', 'salarie.id_salarie')
            ->join('entreprise', 'salarie.id_entreprise', '=', 'entreprise.id_entreprise')
            ->select(
                'entreprise.id_entreprise',
                'entreprise.raison_sociale as nom_entreprise',
                DB::raw('SUM(cotisation.montant) as total_du'),
                DB::raw("SUM(CASE WHEN cotisation.statut = 'payee' THEN cotisation.montant ELSE 0 END) as total_paye"),
                DB::raw("SUM(CASE WHEN cotisation.statut = 'impayee' THEN cotisation.montant ELSE 0 END) as total_impaye")
            )
            ->groupBy('entreprise.id_entreprise', 'entreprise.raison_sociale')
            ->get();

        // Calcul des pourcentages de recouvrement
        $rapports = $rapports->map(function ($rapport) {
            $tauxPaye = $rapport->total_du > 0 ? round(($rapport->total_paye / $rapport->total_du) * 100, 2) : 0;
            $tauxImpaye = $rapport->total_du > 0 ? round(($rapport->total_impaye / $rapport->total_du) * 100, 2) : 0;
            
            $rapport->taux_paye = $tauxPaye . '%';
            $rapport->taux_impaye = $tauxImpaye . '%';
            return $rapport;
        });

        return response()->json([
            'success' => true,
            'data' => $rapports
        ]);
    }
}
