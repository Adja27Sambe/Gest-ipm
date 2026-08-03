<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Models\Salarie;
use Carbon\Carbon;

class DemandeController extends Controller
{
    /**
     * Show the form for creating a new demande.
     */
    public function create()
    {
        $salarie = Auth::guard('participant')->user();
        
        $typesDemande = TypeDemande::all();
        $praticiens = Praticien::all();
        $pharmacies = Pharmacie::all();
        $ayantsDroit = $salarie->ayantsDroit;

        return view('participant.demandes.create', compact(
            'typesDemande', 
            'praticiens', 
            'pharmacies', 
            'ayantsDroit',
            'salarie'
        ));
    }

    /**
     * Store a newly created demande in storage.
     */
    public function store(Request $request)
    {
        $salarie = Auth::guard('participant')->user();

        $validated = $request->validate([
            'id_type_demande' => 'required|exists:type_demande,id_type_demande',
            'beneficiaire' => 'required|string', // 'salarie' or 'ayant_droit_X'
            'id_praticien' => 'nullable|exists:praticien,id_praticien',
            'id_pharmacie' => 'nullable|exists:pharmacie,id_pharmacie',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $demande = new Demande();
            $demande->id_salarie = $salarie->id_salarie;
            $demande->numero_demande = Demande::generateNumber();
            $demande->id_type_demande = $validated['id_type_demande'];
            $demande->statut = 'En attente';
            $demande->date_demande = Carbon::now();

            if (str_starts_with($validated['beneficiaire'], 'ayant_droit_')) {
                $demande->id_ayant_droit = str_replace('ayant_droit_', '', $validated['beneficiaire']);
            }

            if (!empty($validated['id_praticien'])) {
                $demande->id_praticien = $validated['id_praticien'];
            }

            if (!empty($validated['id_pharmacie'])) {
                $demande->id_pharmacie = $validated['id_pharmacie'];
            }

            $demande->save();

            // Handle specific logic based on Type Demande
            // Note: This matches the constants in Demande model (if we use slugs or specific logic)
            // But we keep it simple here. The Admin backend can generate the actual PDF/document once approved.

            DB::commit();

            return redirect()->route('participant.dashboard')->with('success', 'Votre demande a été soumise avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la soumission de la demande.')->withInput();
        }
    }
}
