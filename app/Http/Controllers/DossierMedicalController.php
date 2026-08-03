<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salarie;
use App\Models\AyantDroit;
use App\Models\HistoriqueMedical;
use App\Models\HistoriqueMouvement;
use App\Models\Prestataire;
use App\Models\Pathologie;

class DossierMedicalController extends Controller
{
    /**
     * Affiche l'écran de recherche de bénéficiaire pour le dossier médical.
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('consulter_dossier_medical')) {
            return redirect()->route('demandes.index')->with('error', 'Accès interdit au dossier médical.');
        }

        $salaries = Salarie::with('ayantsDroit')->get();
        return view('dossier-medical.index', compact('salaries'));
    }

    /**
     * Affiche le dossier médical chronologique d'un bénéficiaire précis.
     */
    public function show(Request $request, $type, $id)
    {
        if (!auth()->user()->hasPermission('consulter_dossier_medical')) {
            return redirect()->route('demandes.index')->with('error', 'Accès interdit au dossier médical.');
        }

        // 1. Validation du type
        if (!in_array($type, ['salarie', 'ayant_droit'])) {
            abort(404);
        }

        $modelClass = $type === 'salarie' ? Salarie::class : AyantDroit::class;
        $beneficiaire = $modelClass::findOrFail($id);

        // 2. Traçabilité OBLIGATOIRE de la LECTURE
        HistoriqueMouvement::create([
            'date_heure' => now(),
            'module' => 'Dossier Médical (Web)',
            'action' => 'LECTURE',
            'description' => "Consultation du dossier médical complet de: " . $beneficiaire->prenom . " " . $beneficiaire->nom . " (Type: $type, ID: $id)",
            'adresse_ip' => $request->ip(),
            'id_utilisateur' => auth()->id(),
        ]);

        // 3. Récupération optimisée du dossier (Eager Loading)
        $historique = HistoriqueMedical::with(['prescriptions', 'prestataire', 'pathologie'])
            ->where('beneficiaire_type', $modelClass)
            ->where('id_beneficiaire', $id)
            ->orderBy('date_consultation', 'desc')
            ->get();

        $prestataires = Prestataire::select('id_prestataire', 'nom')->get();
        $pathologies = Pathologie::select('id_pathologie', 'libelle')->get();

        return view('dossier-medical.show', compact('beneficiaire', 'type', 'historique', 'prestataires', 'pathologies'));
    }

    /**
     * Ajoute une nouvelle entrée dans le dossier médical.
     */
    public function store(Request $request, $type, $id)
    {
        if (!auth()->user()->hasPermission('consulter_dossier_medical')) {
            return redirect()->route('demandes.index')->with('error', 'Accès interdit au dossier médical.');
        }

        $validated = $request->validate([
            'date_consultation' => 'required|date',
            'diagnostic' => 'required|string',
            'traitement' => 'nullable|string',
            'observation' => 'nullable|string',
            'id_prestataire' => 'nullable|exists:prestataire,id_prestataire',
            'id_pathologie' => 'nullable|exists:pathologie,id_pathologie',
            'prescriptions' => 'nullable|array',
            'prescriptions.*.medicament' => 'required_with:prescriptions|string',
            'prescriptions.*.posologie' => 'nullable|string',
            'prescriptions.*.duree' => 'nullable|string',
        ]);

        $modelClass = $type === 'salarie' ? Salarie::class : AyantDroit::class;

        $entree = HistoriqueMedical::create([
            'date_consultation' => $validated['date_consultation'],
            'diagnostic' => $validated['diagnostic'],
            'traitement' => $validated['traitement'] ?? null,
            'observation' => $validated['observation'] ?? null,
            'beneficiaire_type' => $modelClass,
            'id_beneficiaire' => $id,
            'id_prestataire' => $validated['id_prestataire'] ?? null,
            'id_pathologie' => $validated['id_pathologie'] ?? null,
        ]);

        // Ajout des prescriptions
        if (!empty($validated['prescriptions'])) {
            // Filtrer les prescriptions vides (si le JS en envoie)
            $prescriptions = array_filter($validated['prescriptions'], function ($p) {
                return !empty($p['medicament']);
            });
            if (count($prescriptions) > 0) {
                $entree->prescriptions()->createMany($prescriptions);
            }
        }

        return redirect()->route('dossier-medical.show', ['type' => $type, 'id' => $id])
            ->with('success', 'Entrée ajoutée au dossier médical avec succès.');
    }
}
