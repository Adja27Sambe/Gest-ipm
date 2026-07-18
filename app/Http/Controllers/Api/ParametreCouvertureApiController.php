<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParametreCouverture;
use Illuminate\Http\Request;

class ParametreCouvertureApiController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parametres = ParametreCouverture::with('typePrestation')->get();
        return response()->json($parametres);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation|unique:parametre_couverture,id_type_prestation',
            'taux_prise_charge' => 'required|numeric|min:0|max:100',
            'plafond_annuel' => 'nullable|numeric|min:0',
            'plafond_par_acte' => 'nullable|numeric|min:0',
            'ticket_moderateur' => 'nullable|numeric|min:0',
        ]);

        $parametre = ParametreCouverture::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paramètre de couverture créé avec succès.',
            'data' => $parametre
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ParametreCouverture $parametres_couverture)
    {
        return response()->json($parametres_couverture->load('typePrestation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParametreCouverture $parametres_couverture)
    {
        $validated = $request->validate([
            'taux_prise_charge' => 'required|numeric|min:0|max:100',
            'plafond_annuel' => 'nullable|numeric|min:0',
            'plafond_par_acte' => 'nullable|numeric|min:0',
            'ticket_moderateur' => 'nullable|numeric|min:0',
        ]);

        $parametres_couverture->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Paramètre de couverture mis à jour avec succès.',
            'data' => $parametres_couverture
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParametreCouverture $parametres_couverture)
    {
        $parametres_couverture->delete();

        return response()->json([
            'success' => true,
            'message' => 'Paramètre de couverture supprimé avec succès.'
        ]);
    }

    /**
     * Simuler la prise en charge d'une prestation
     */
    public function simuler(Request $request)
    {
        // Pas d'autorisation stricte ici, on peut supposer que tout utilisateur 
        // authentifié peut simuler un tarif, ou on peut le restreindre. 
        // On restreint à la même permission pour être cohérent, ou pas.
        // Laissons-le ouvert aux utilisateurs authentifiés pour la saisie de prestation.
        
        $request->validate([
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation',
            'montant' => 'required|numeric|min:0',
        ]);

        $parametre = ParametreCouverture::where('id_type_prestation', $request->id_type_prestation)->first();

        if (!$parametre) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun paramètre de couverture défini pour ce type de prestation.',
            ], 404);
        }

        $montant = $request->montant;
        $tauxApplique = (float) $parametre->taux_prise_charge;

        // Calcul théorique
        $montantPrisEnCharge = $montant * ($tauxApplique / 100);

        // Application du plafond par acte si défini
        if ($parametre->plafond_par_acte !== null && $montantPrisEnCharge > $parametre->plafond_par_acte) {
            $montantPrisEnCharge = (float) $parametre->plafond_par_acte;
        }

        $resteACharge = $montant - $montantPrisEnCharge;

        return response()->json([
            'success' => true,
            'data' => [
                'taux_applique' => $tauxApplique,
                'montant_saisi' => $montant,
                'montant_pris_en_charge' => round($montantPrisEnCharge, 2),
                'reste_a_charge' => round($resteACharge, 2),
                'plafond_par_acte' => $parametre->plafond_par_acte,
                'plafond_annuel' => $parametre->plafond_annuel,
                'note' => 'Le plafond annuel restant n\'est pas calculé ici car il dépend de l\'historique du bénéficiaire.',
            ]
        ]);
    }
}
