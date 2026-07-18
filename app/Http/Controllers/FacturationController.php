<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\PaiementPrestataire;
use App\Models\Prestataire;

class FacturationController extends Controller
{
    /**
     * Affiche le tableau de bord des factures (impayées / en attente).
     */
    public function index(Request $request)
    {
        $prestataires = Prestataire::select('id_prestataire', 'nom')->orderBy('nom')->get();
        
        $query = Facture::with(['prestataire'])
            ->withSum('paiementPrestataires', 'montant')
            ->whereIn('statut_paiement', ['en_attente', 'partiellement_payee']);
        
        if ($request->has('id_prestataire') && $request->id_prestataire != '') {
            $query->where('id_prestataire', $request->id_prestataire);
        }
        
        $factures = $query->orderBy('date_facture', 'asc')->paginate(15);
        
        // Optimisation majeure : Calcul du total du en utilisant l'agrégat SQL au lieu de l'accesseur N+1
        $totalDu = (clone $query)->get()->sum(function($facture) {
            return ($facture->montant ?? 0) - ($facture->paiement_prestataires_sum_montant ?? 0);
        });

        return view('factures.index', compact('factures', 'prestataires', 'totalDu'));
    }

    /**
     * Affiche les détails d'une facture spécifique.
     */
    public function show($id)
    {
        $facture = Facture::with(['prestataire', 'prestations', 'paiementPrestataires'])->findOrFail($id);
        return view('factures.show', compact('facture'));
    }

    /**
     * Enregistre un nouveau paiement pour la facture.
     */
    public function storePaiement(Request $request, $id)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|string',
            'reference_transaction' => 'nullable|string'
        ]);

        $facture = Facture::findOrFail($id);
        
        PaiementPrestataire::create([
            'id_facture' => $facture->id_facture,
            'date_paiement' => now(),
            'montant' => $request->montant,
            'mode_paiement' => $request->mode_paiement,
            'reference_transaction' => $request->reference_transaction
        ]);

        return redirect()->route('factures.show', $id)->with('success', 'Paiement enregistré avec succès.');
    }
}
