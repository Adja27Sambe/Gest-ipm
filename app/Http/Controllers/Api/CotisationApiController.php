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
            'id_salarie' => 'nullable|exists:salarie,PACLEUNIK',
            'periode' => 'nullable|string', // ex: 2023-10
        ]);

        $query = \App\Models\CotisationParticipant::with('salarie');

        if ($request->has('id_salarie') && $request->id_salarie != '') {
            $query->where('PACLEUNIK', $request->id_salarie);
        }

        if ($request->has('periode') && $request->periode != '') {
            $parts = explode('-', $request->periode);
            if (count($parts) === 2) {
                $query->where('ANNEECOTISE', $parts[0])->where('MOISCOTISE', $parts[1]);
            }
        }

        $cotisations = $query->orderBy('ANNEECOTISE', 'desc')->orderBy('MOISCOTISE', 'desc')->paginate($request->input('per_page', 15));

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
        $rapports = DB::connection('mysql')->table('COTISE')
            ->join('ADHERANT', 'COTISE.ADCLEUNIK', '=', 'ADHERANT.IDADHERANT')
            ->select(
                'ADHERANT.IDADHERANT as id_entreprise',
                'ADHERANT.ADHERANT as nom_entreprise',
                DB::raw('SUM(COTISE.MTCOTISE) as total_du'),
                DB::raw("SUM(COTISE.MTREGLE) as total_paye"),
                DB::raw("SUM(COTISE.MTCOTISE - COTISE.MTREGLE) as total_impaye")
            )
            ->groupBy('ADHERANT.IDADHERANT', 'ADHERANT.ADHERANT')
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
