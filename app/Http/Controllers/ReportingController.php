<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entreprise;
use App\Models\Salarie;
use App\Models\AyantDroit;
use App\Models\Demande;
use App\Models\Facture;
use App\Models\Praticien;
use App\Models\Pharmacie;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingController extends Controller
{
    public function index()
    {
        // 1. KPIs de base
        $totalEntreprises = Entreprise::count();
        $totalSalaries = Salarie::count();
        $totalAyantsDroit = AyantDroit::count();
        $totalBeneficiaires = $totalSalaries + $totalAyantsDroit;

        $totalFacture = Facture::sum('montant');
        
        // Optimisation N+1: Calculer le total payé pour les factures
        $totalPaye = Facture::withSum('paiementPrestataires', 'montant')->get()->sum('paiement_prestataires_sum_montant');
        $totalDu = $totalFacture - $totalPaye;

        // 2. Données pour le graphique de répartition des demandes par statut
        $demandesParStatut = Demande::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();
            
        $labelsStatut = [];
        $dataStatut = [];
        $colorsStatut = [];
        
        foreach ($demandesParStatut as $demande) {
            $labelsStatut[] = ucfirst($demande->statut);
            $dataStatut[] = $demande->total;
            
            // Attribution des couleurs
            if ($demande->statut == 'approuvee') $colorsStatut[] = '#198754'; // Success
            elseif ($demande->statut == 'rejetee') $colorsStatut[] = '#dc3545'; // Danger
            elseif ($demande->statut == 'en_attente') $colorsStatut[] = '#ffc107'; // Warning
            else $colorsStatut[] = '#6c757d'; // Secondary
        }

        // 3. Évolution des dépenses sur les 6 derniers mois
        $sixMoisAvant = Carbon::now()->subMonths(5)->startOfMonth();
        
        $facturesParMois = Facture::select(
            DB::raw('DATE_FORMAT(date_facture, "%Y-%m") as mois'),
            DB::raw('SUM(montant) as total')
        )
        ->where('date_facture', '>=', $sixMoisAvant)
        ->groupBy('mois')
        ->orderBy('mois')
        ->get();

        $labelsEvolution = [];
        $dataEvolution = [];
        
        // Initialiser les 6 derniers mois à 0
        for ($i = 5; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i)->format('Y-m');
            $labelsEvolution[] = Carbon::now()->subMonths($i)->translatedFormat('M Y');
            
            $facture = $facturesParMois->firstWhere('mois', $mois);
            $dataEvolution[] = $facture ? $facture->total : 0;
        }

        // 4. Aperçu des dernières factures
        $dernieresFactures = Facture::with(['praticien', 'pharmacie'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Partenaires
        $totalPraticiens = Praticien::count();
        $totalPharmacies = Pharmacie::count();

        return view('reporting.index', compact(
            'totalEntreprises',
            'totalBeneficiaires',
            'totalFacture',
            'totalPaye',
            'totalDu',
            'labelsStatut',
            'dataStatut',
            'colorsStatut',
            'labelsEvolution',
            'dataEvolution',
            'dernieresFactures',
            'totalPraticiens',
            'totalPharmacies'
        ));
    }
}
