<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Enums\DevisStatut;
use App\Services\DevisTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevisApiController extends Controller
{
    /**
     * Changer le statut d'un devis
     */
    public function transition(Request $request, $id)
    {
        $devis = Devis::findOrFail($id);
        
        // Sécurité via Policy : s'assure que l'utilisateur a le droit de valider
        // On contourne la policy stricte si on est juste un dev ou un admin avec le bon permission
        if (!auth()->user()->hasPermission('Valider les devis')) {
            return response()->json(['message' => 'Action non autorisée. Vous n\'avez pas la permission de valider les devis.'], 403);
        }

        $request->validate([
            'statut' => ['required', 'string'],
            'commentaire' => ['nullable', 'string']
        ]);

        $newStatut = DevisStatut::tryFrom($request->statut);
        if (!$newStatut) {
            return response()->json(['message' => 'Statut invalide.'], 400);
        }

        try {
            $service = new DevisTransitionService();
            $devisUpdated = $service->transition($devis, $newStatut, auth()->id(), $request->commentaire);

            return response()->json([
                'success' => true,
                'message' => "Le devis a été passé au statut {$newStatut->value}.",
                'data' => $devisUpdated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Statistiques : Somme des montants par statut sur une période (basée sur date_devis)
     */
    public function stats(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->input('date_fin', now()->endOfMonth()->toDateString());

        $stats = Devis::select('statut', DB::raw('SUM(montant) as total_montant'), DB::raw('COUNT(*) as total_devis'))
            ->whereBetween('date_devis', [$dateDebut, $dateFin])
            ->groupBy('statut')
            ->get();

        return response()->json([
            'success' => true,
            'periode' => [
                'debut' => $dateDebut,
                'fin' => $dateFin
            ],
            'data' => $stats
        ]);
    }
}
