<?php

namespace App\Http\Controllers;

use App\Models\ParametreCouverture;
use App\Models\TypePrestation;
use Illuminate\Http\Request;

class ParametreCouvertureController extends Controller
{
    /**
     * Liste des règles et paramètres de couverture par acte.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = ParametreCouverture::with(['typePrestation.typesActes', 'typeActe']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('typePrestation', function($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $parametres = $query->latest('id_parametre')->paginate($perPage)->withQueryString();
        return view('parametres-couverture.index', compact('parametres'));
    }

    /**
     * Formulaire de création d'une règle de couverture pour un acte.
     */
    public function create()
    {
        // Proposer les actes qui n'ont pas encore de règle configurée
        $actesConfigures = ParametreCouverture::pluck('id_type_prestation');
        $typesPrestation = TypePrestation::whereNotIn('id_type_prestation', $actesConfigures)
            ->where('statut', 'actif')
            ->with('typesActes')
            ->orderBy('libelle')
            ->get();

        return view('parametres-couverture.create', compact('typesPrestation'));
    }

    /**
     * Enregistrement d'un paramètre de couverture.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation|unique:parametre_couverture,id_type_prestation',
            'id_type_acte' => 'nullable|exists:type_acte,id_type_acte',
            'type_calcul' => 'required|string|in:POURCENTAGE,MONTANT_REFERENTIEL,FORFAIT,TARIF_JOURNALIER,EXCLUSION,100_PERCENT_PARTICIPANT,SOUS_COUVERT',
            'taux_prise_charge' => 'nullable|numeric|min:0|max:100',
            'montant_referentiel' => 'nullable|numeric|min:0',
            'part_ipm_reference' => 'nullable|numeric|min:0',
            'montant_forfait' => 'nullable|numeric|min:0',
            'tarif_journalier' => 'nullable|numeric|min:0',
            'montant_plafond' => 'nullable|numeric|min:0',
            'taux_participant' => 'nullable|numeric|min:0|max:100',
            'conditions_particulieres' => 'nullable|string|max:255',
            'est_exclusion' => 'nullable|boolean',
            'motif_exclusion' => 'nullable|string|max:255',
        ]);

        // Calcul automatique de la Part IPM de référence = Montant référentiel × Taux IPM
        if (!empty($validated['montant_referentiel']) && isset($validated['taux_prise_charge'])) {
            $taux = (float)$validated['taux_prise_charge'];
            $ref = (float)$validated['montant_referentiel'];
            $validated['part_ipm_reference'] = round(($ref * $taux) / 100, 2);
            $validated['ticket_moderateur'] = $validated['part_ipm_reference'];
        } elseif (!empty($validated['part_ipm_reference'])) {
            $validated['ticket_moderateur'] = $validated['part_ipm_reference'];
        }

        if ($validated['type_calcul'] === 'SOUS_COUVERT') {
            $validated['taux_prise_charge'] = 100.00;
        } elseif ($validated['type_calcul'] === 'EXCLUSION' || $validated['type_calcul'] === '100_PERCENT_PARTICIPANT') {
            $validated['taux_prise_charge'] = 0.00;
            $validated['est_exclusion'] = true;
        }

        ParametreCouverture::create($validated);

        return redirect()->route('parametres-couverture.index')
            ->with('success', "Le paramètre de couverture pour cet acte a été créé avec succès.");
    }

    /**
     * Formulaire d'édition d'une règle de couverture.
     */
    public function edit(ParametreCouverture $parametres_couverture)
    {
        $parametres_couverture->load(['typePrestation.typesActes', 'typeActe']);
        return view('parametres-couverture.edit', compact('parametres_couverture'));
    }

    /**
     * Mise à jour d'un paramètre de couverture.
     */
    public function update(Request $request, ParametreCouverture $parametres_couverture)
    {
        $validated = $request->validate([
            'id_type_acte' => 'nullable|exists:type_acte,id_type_acte',
            'type_calcul' => 'required|string|in:POURCENTAGE,MONTANT_REFERENTIEL,FORFAIT,TARIF_JOURNALIER,EXCLUSION,100_PERCENT_PARTICIPANT,SOUS_COUVERT',
            'taux_prise_charge' => 'nullable|numeric|min:0|max:100',
            'montant_referentiel' => 'nullable|numeric|min:0',
            'part_ipm_reference' => 'nullable|numeric|min:0',
            'montant_forfait' => 'nullable|numeric|min:0',
            'tarif_journalier' => 'nullable|numeric|min:0',
            'montant_plafond' => 'nullable|numeric|min:0',
            'taux_participant' => 'nullable|numeric|min:0|max:100',
            'conditions_particulieres' => 'nullable|string|max:255',
            'est_exclusion' => 'nullable|boolean',
            'motif_exclusion' => 'nullable|string|max:255',
        ]);

        // Calcul automatique de la Part IPM de référence = Montant référentiel × Taux IPM
        if (!empty($validated['montant_referentiel']) && isset($validated['taux_prise_charge'])) {
            $taux = (float)$validated['taux_prise_charge'];
            $ref = (float)$validated['montant_referentiel'];
            $validated['part_ipm_reference'] = round(($ref * $taux) / 100, 2);
            $validated['ticket_moderateur'] = $validated['part_ipm_reference'];
        } elseif (!empty($validated['part_ipm_reference'])) {
            $validated['ticket_moderateur'] = $validated['part_ipm_reference'];
        }

        if ($validated['type_calcul'] === 'SOUS_COUVERT') {
            $validated['taux_prise_charge'] = 100.00;
        } elseif ($validated['type_calcul'] === 'EXCLUSION' || $validated['type_calcul'] === '100_PERCENT_PARTICIPANT') {
            $validated['taux_prise_charge'] = 0.00;
            $validated['est_exclusion'] = true;
        }

        $parametres_couverture->update($validated);

        return redirect()->route('parametres-couverture.index')
            ->with('success', "Le paramètre de couverture a été mis à jour avec succès.");
    }

    /**
     * Suppression d'un paramètre de couverture.
     */
    public function destroy(ParametreCouverture $parametres_couverture)
    {
        $parametres_couverture->delete();

        return redirect()->route('parametres-couverture.index')
            ->with('success', 'Le paramètre de couverture a été supprimé avec succès.');
    }
}
