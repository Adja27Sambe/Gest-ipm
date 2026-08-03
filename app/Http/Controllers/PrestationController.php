<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestation;
use App\Models\TypePrestation;
use App\Models\Prestataire;
use App\Models\Demande;
use App\Services\PlafondService;
use App\Exceptions\PlafondDepasseException;
use App\Exports\PrestationExport;
use Maatwebsite\Excel\Facades\Excel;

class PrestationController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestation::with(['typePrestation', 'prestataire', 'demande.salarie', 'demande.ayantDroit']);

        // Filtrage optionnel
        if ($request->has('id_prestataire') && $request->id_prestataire != '') {
            $query->where('id_prestataire', $request->id_prestataire);
        }

        $perPage = $request->input('per_page', 5);
        $prestations = $query->latest('date_prestation')->latest('id_prestation')->paginate($perPage)->withQueryString();
        
        $typesPrestation = TypePrestation::select('id_type_prestation', 'libelle')->get();
        $prestataires = Prestataire::select('id_prestataire', 'nom')->get();
        // Pour la saisie, on peut avoir besoin de lier à une demande existante.
        // On récupère les demandes "Approuvées"
        $demandes = Demande::with(['salarie:id_salarie,nom,prenom', 'ayantDroit:id_ayant_droit,nom,prenom'])
            ->where('statut', 'Approuvée')
            ->get(['id_demande', 'id_salarie', 'id_ayant_droit', 'date_demande']);

        return view('prestations.index', compact('prestations', 'typesPrestation', 'prestataires', 'demandes'));
    }

    public function store(Request $request, PlafondService $plafondService)
    {
        $validated = $request->validate([
            'date_prestation' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'taux_prise_charge' => 'required|numeric|min:0|max:100',
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation',
            'id_prestataire' => 'required|exists:prestataire,id_prestataire',
            'id_demande' => 'required|exists:demande,id_demande',
        ]);

        try {
            // Vérification des plafonds
            $plafondService->checkPlafonds(
                $validated['montant'], 
                $validated['id_type_prestation'], 
                $validated['id_demande']
            );

            Prestation::create($validated);

            return redirect()->route('prestations.index')->with('success', 'Prestation enregistrée avec succès.');
        } catch (PlafondDepasseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage() . " (Dépassement de " . number_format($e->getMontantDepassement(), 0, ',', ' ') . " FCFA)");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $export = new PrestationExport(
            $request->id_prestataire,
            $request->date_debut,
            $request->date_fin
        );

        $filename = 'export_prestations_' . date('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
}
