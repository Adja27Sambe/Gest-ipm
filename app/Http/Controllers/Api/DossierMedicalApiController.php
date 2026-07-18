<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoriqueMedical;
use App\Models\HistoriqueMouvement;
use Illuminate\Http\Request;

class DossierMedicalApiController extends Controller
{
    /**
     * Obtenir le dossier médical chronologique d'un bénéficiaire
     */
    public function history(Request $request, $beneficiaireType, $idBeneficiaire)
    {
        // 1. Vérification stricte des permissions via la Policy manuellement 
        // ou via $this->authorize('viewAny', HistoriqueMedical::class);
        if (!auth()->user()->hasPermission('consulter_dossier_medical')) {
            return response()->json(['message' => 'Accès interdit. Vous n\'avez pas la permission de consulter les dossiers médicaux.'], 403);
        }

        // 2. Journalisation OBLIGATOIRE de la LECTURE
        HistoriqueMouvement::create([
            'date_heure' => now(),
            'module' => 'Dossier Médical',
            'action' => 'LECTURE',
            'description' => "Consultation du dossier médical complet. (Type: $beneficiaireType, ID: $idBeneficiaire)",
            'adresse_ip' => $request->ip(),
            'id_utilisateur' => auth()->id(),
        ]);

        // Mapping du type pour le polymorphisme
        $modelClass = $beneficiaireType === 'salarie' ? \App\Models\Salarie::class : \App\Models\AyantDroit::class;

        // 3. Eager Loading pour éviter le N+1
        $query = HistoriqueMedical::with(['prescriptions', 'prestataire', 'pathologie'])
            ->where('beneficiaire_type', $modelClass)
            ->where('id_beneficiaire', $idBeneficiaire);

        // Filtrage optionnel
        if ($request->has('id_prestataire')) {
            $query->where('id_prestataire', $request->id_prestataire);
        }
        
        if ($request->has('id_pathologie')) {
            $query->where('id_pathologie', $request->id_pathologie);
        }

        if ($request->has('date_debut')) {
            $query->where('date_consultation', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->where('date_consultation', '<=', $request->date_fin);
        }

        $dossier = $query->orderBy('date_consultation', 'desc')->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'beneficiaire' => [
                'type' => $beneficiaireType,
                'id' => $idBeneficiaire
            ],
            'data' => $dossier
        ]);
    }

    /**
     * Ajouter une entrée au dossier médical
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('consulter_dossier_medical')) {
            return response()->json(['message' => 'Accès interdit.'], 403);
        }

        $validated = $request->validate([
            'date_consultation' => 'required|date',
            'diagnostic' => 'required|string',
            'traitement' => 'nullable|string',
            'observation' => 'nullable|string',
            'beneficiaire_type' => 'required|in:salarie,ayant_droit',
            'id_beneficiaire' => 'required|integer',
            'id_prestataire' => 'nullable|exists:prestataire,id_prestataire',
            'id_pathologie' => 'nullable|exists:pathologie,id_pathologie',
            'prescriptions' => 'nullable|array',
            'prescriptions.*.medicament' => 'required|string',
            'prescriptions.*.posologie' => 'nullable|string',
            'prescriptions.*.duree' => 'nullable|string',
        ]);

        $modelClass = $validated['beneficiaire_type'] === 'salarie' ? \App\Models\Salarie::class : \App\Models\AyantDroit::class;

        $historique = HistoriqueMedical::create([
            'date_consultation' => $validated['date_consultation'],
            'diagnostic' => $validated['diagnostic'],
            'traitement' => $validated['traitement'] ?? null,
            'observation' => $validated['observation'] ?? null,
            'beneficiaire_type' => $modelClass,
            'id_beneficiaire' => $validated['id_beneficiaire'],
            'id_prestataire' => $validated['id_prestataire'] ?? null,
            'id_pathologie' => $validated['id_pathologie'] ?? null,
        ]);

        if (!empty($validated['prescriptions'])) {
            $historique->prescriptions()->createMany($validated['prescriptions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Entrée ajoutée au dossier médical.',
            'data' => $historique->load('prescriptions')
        ], 201);
    }
}
