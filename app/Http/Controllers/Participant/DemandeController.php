<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\Praticien;
use App\Models\Pharmacie;
use App\Services\DemandeService;

class DemandeController extends Controller
{
    /**
     * Show the form for creating a new demande.
     */
    public function create()
    {
        $salarie = Auth::guard('participant')->user();
        
        $typesDemande = TypeDemande::all();
        $praticiens = Praticien::orderBy('NOMPRAT')->get();
        $pharmacies = Pharmacie::orderBy('NOMPHARM')->get();
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
    public function store(Request $request, DemandeService $demandeService)
    {
        $salarie = Auth::guard('participant')->user();

        $typeDemandeId = $request->input('id_type_demande');
        $typeDemande = $typeDemandeId ? TypeDemande::find($typeDemandeId) : null;
        $libelle = $typeDemande ? strtolower($typeDemande->libelle) : '';

        $rules = [
            'id_type_demande' => 'required|exists:type_demande,id_type_demande',
            'beneficiaire' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ];

        if (str_contains($libelle, 'bon')) {
            $rules['id_pharmacie'] = 'required|exists:PHARMACI,PHCLEUNIK';
            $rules['date_ordonnance'] = 'required|date|before_or_equal:today|after_or_equal:' . now()->subMonths(6)->toDateString();
            $rules['nombre_articles'] = 'required|integer|min:1';
        } elseif (str_contains($libelle, 'feuille')) {
            $rules['id_praticien'] = 'required|exists:PRATICIE,PRCLEUNIK';
        } elseif (str_contains($libelle, 'lettre')) {
            $rules['id_praticien'] = 'required|exists:PRATICIE,PRCLEUNIK';
            $rules['type_acte'] = 'required';
        }

        $messages = [
            'id_type_demande.required' => 'Veuillez sélectionner un type de demande.',
            'id_pharmacie.required' => 'La sélection d\'une pharmacie conventionnée est obligatoire pour un Bon de Commande.',
            'id_pharmacie.exists' => 'La pharmacie sélectionnée est invalide.',
            'date_ordonnance.required' => 'La date de l\'ordonnance est obligatoire.',
            'date_ordonnance.before_or_equal' => 'La date de l\'ordonnance ne peut pas être dans le futur.',
            'date_ordonnance.after_or_equal' => 'L\'ordonnance est expirée (validité maximale de 6 mois).',
            'nombre_articles.required' => 'Le nombre d\'articles prescrits est obligatoire.',
            'nombre_articles.min' => 'Le nombre d\'articles doit être d\'au moins 1.',
            'id_praticien.required' => 'La sélection d\'un praticien ou établissement médical est obligatoire.',
            'id_praticien.exists' => 'Le praticien sélectionné est invalide.',
            'type_acte.required' => 'Veuillez préciser le type d\'acte médical pour la lettre de garantie.',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $idAyantDroit = null;
            if (str_starts_with($validated['beneficiaire'], 'ayant_droit_')) {
                $idAyantDroit = (int) str_replace('ayant_droit_', '', $validated['beneficiaire']);
            }

            $serviceData = [
                'id_type_demande' => $validated['id_type_demande'],
                'id_salarie' => $salarie->id_salarie ?? $salarie->IDPARTICIPANT,
                'id_ayant_droit' => $idAyantDroit,
                'id_pharmacie' => $request->input('id_pharmacie'),
                'id_praticien' => $request->input('id_praticien'),
                'date_ordonnance' => $request->input('date_ordonnance'),
                'nombre_articles' => $request->input('nombre_articles', 1),
                'choix_acte' => $request->input('type_acte') ?? $request->input('choix_acte'),
                'motif' => $request->input('description'),
                'observations' => $request->input('description'),
            ];

            $demandeService->traiterDemande($serviceData);

            return redirect()->route('participant.dashboard')->with('success', 'Votre demande a été soumise avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la soumission : ' . $e->getMessage())->withInput();
        }
    }
}
