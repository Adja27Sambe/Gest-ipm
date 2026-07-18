<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarteAssure;
use App\Models\Salarie;
use App\Services\CarteAssureService;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CarteAssureController extends Controller
{
    protected CarteAssureService $carteAssureService;

    public function __construct(CarteAssureService $carteAssureService)
    {
        $this->carteAssureService = $carteAssureService;
    }

    /**
     * Réémettre une carte pour un salarié (archive l'ancienne et en crée une nouvelle)
     */
    public function reemettre(Salarie $salarie): JsonResponse
    {
        $nouvelleCarte = $this->carteAssureService->reemettreCarte($salarie);

        return response()->json([
            'message' => 'Carte réémise avec succès',
            'carte' => $nouvelleCarte
        ], 201);
    }

    /**
     * Vérifier la validité d'une carte via son numéro
     */
    public function verifier(string $numero_carte): JsonResponse
    {
        $carte = CarteAssure::with(['salarie.entreprise', 'salarie.ayantsDroit'])
            ->where('numero_carte', $numero_carte)
            ->first();

        if (!$carte) {
            return response()->json(['message' => 'Carte introuvable'], 404);
        }

        return response()->json([
            'est_valide' => $carte->statut === CarteAssure::STATUT_ACTIF,
            'carte' => $carte,
        ]);
    }

    /**
     * Exporter la carte virtuelle en PDF
     */
    public function exportPdf(CarteAssure $carteAssure): Response
    {
        $carteAssure->load('salarie.entreprise');
        
        $pdf = Pdf::loadView('pdf.carte_assure', ['carte' => $carteAssure])
            ->setPaper('credit-card', 'landscape'); // Format type carte de crédit

        return $pdf->download('carte_assure_' . $carteAssure->numero_carte . '.pdf');
    }
}
