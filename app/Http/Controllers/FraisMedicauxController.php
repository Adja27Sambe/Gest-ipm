<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\Prestation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class FraisMedicauxController extends Controller
{
    /**
     * Vue Globale : Liste des Adhérents (Entreprises) avec leurs totaux
     */
    public function index(Request $request)
    {
        // On récupère les entreprises actives.
        $query = Entreprise::whereIn('ADACTIF', [1, 2]); // actif ou suspendu

        if ($request->filled('search')) {
            $query->where('ADHERANT', 'like', '%' . $request->search . '%');
        }

        $entreprises = $query->orderBy('ADHERANT')->paginate(10)->withQueryString();

        // Calcul des totaux par entreprise
        foreach ($entreprises as $entreprise) {
            $entreprise->total_frais = Prestation::whereHas('demande.salarie', function($q) use ($entreprise) {
                $q->where('IDADHERANT', $entreprise->IDADHERANT);
            })->sum('montant');
            
            $entreprise->total_prise_charge = Prestation::whereHas('demande.salarie', function($q) use ($entreprise) {
                $q->where('IDADHERANT', $entreprise->IDADHERANT);
            })->sum(DB::raw('montant - reste_a_charge'));
            
            $entreprise->total_reste_charge = Prestation::whereHas('demande.salarie', function($q) use ($entreprise) {
                $q->where('IDADHERANT', $entreprise->IDADHERANT);
            })->sum('reste_a_charge');
        }

        return view('frais-medicaux.index', compact('entreprises'));
    }

    /**
     * Détail d'une entreprise (Liste de ses salariés et leurs totaux)
     */
    public function showEntreprise($id)
    {
        $entreprise = Entreprise::findOrFail($id);
        
        $salaries = Salarie::where('IDADHERANT', $id)->get();

        foreach ($salaries as $salarie) {
            $salarie->total_frais = Prestation::whereHas('demande', function($q) use ($salarie) {
                $q->where('id_salarie', $salarie->IDPARTICIPANT);
            })->sum('montant');
            
            $salarie->total_prise_charge = Prestation::whereHas('demande', function($q) use ($salarie) {
                $q->where('id_salarie', $salarie->IDPARTICIPANT);
            })->sum(DB::raw('montant - reste_a_charge'));
            
            $salarie->total_reste_charge = Prestation::whereHas('demande', function($q) use ($salarie) {
                $q->where('id_salarie', $salarie->IDPARTICIPANT);
            })->sum('reste_a_charge');
        }

        return view('frais-medicaux.entreprise', compact('entreprise', 'salaries'));
    }

    /**
     * Détail d'un salarié (Ligne par ligne des prestations)
     */
    public function showSalarie($id)
    {
        $salarie = Salarie::with('entreprise')->findOrFail($id);
        
        $prestations = Prestation::with(['demande', 'typePrestation', 'praticien', 'pharmacie'])
            ->whereHas('demande', function($q) use ($salarie) {
                $q->where('id_salarie', $salarie->IDPARTICIPANT);
            })
            ->orderByDesc('date_prestation')
            ->get();

        return view('frais-medicaux.salarie', compact('salarie', 'prestations'));
    }

    /**
     * Exportation PDF (Gère les 3 niveaux: Global, Entreprise, Salarie)
     */
    public function exportPdf(Request $request)
    {
        $type = $request->input('type', 'global');
        $data = [];

        if ($type === 'global') {
            $entreprises = Entreprise::whereIn('ADACTIF', [1, 2])->orderBy('ADHERANT')->get();
            foreach ($entreprises as $entreprise) {
                $entreprise->total_frais = Prestation::whereHas('demande.salarie', function($q) use ($entreprise) {
                    $q->where('IDADHERANT', $entreprise->IDADHERANT);
                })->sum('montant');
                
            $entreprise->total_prise_charge = Prestation::whereHas('demande.salarie', function($q) use ($entreprise) {
                $q->where('IDADHERANT', $entreprise->IDADHERANT);
            })->sum(DB::raw('montant - reste_a_charge'));
            }
            $data['entreprises'] = $entreprises;
            $data['title'] = 'Rapport Global des Frais Médicaux par Adhérent';
            $data['view_content'] = 'frais-medicaux.pdf.global';

        } elseif ($type === 'entreprise' && $request->has('id')) {
            $entreprise = Entreprise::findOrFail($request->id);
            $salaries = Salarie::where('IDADHERANT', $request->id)->get();
            foreach ($salaries as $salarie) {
                $salarie->total_frais = Prestation::whereHas('demande', function($q) use ($salarie) {
                    $q->where('id_salarie', $salarie->IDPARTICIPANT);
                })->sum('montant');
                $salarie->total_prise_charge = Prestation::whereHas('demande', function($q) use ($salarie) {
                    $q->where('id_salarie', $salarie->IDPARTICIPANT);
                })->sum(DB::raw('montant - reste_a_charge'));
            }
            $data['entreprise'] = $entreprise;
            $data['salaries'] = $salaries;
            $data['title'] = 'Frais Médicaux - Adhérent : ' . $entreprise->raison_sociale;
            $data['view_content'] = 'frais-medicaux.pdf.entreprise';

        } elseif ($type === 'salarie' && $request->has('id')) {
            $salarie = Salarie::with('entreprise')->findOrFail($request->id);
            $prestations = Prestation::with(['demande', 'typePrestation', 'praticien', 'pharmacie'])
                ->whereHas('demande', function($q) use ($salarie) {
                    $q->where('id_salarie', $salarie->IDPARTICIPANT);
                })
                ->orderByDesc('date_prestation')
                ->get();
                
            $data['salarie'] = $salarie;
            $data['prestations'] = $prestations;
            $data['title'] = 'Relevé des Frais Médicaux - ' . $salarie->nom_complet;
            $data['view_content'] = 'frais-medicaux.pdf.salarie';
        }

        $pdf = Pdf::loadView('frais-medicaux.pdf.template', $data);
        $pdf->setPaper('A4', 'landscape'); // Format paysage mieux pour les tableaux

        $filename = 'frais_medicaux_' . $type . '_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
