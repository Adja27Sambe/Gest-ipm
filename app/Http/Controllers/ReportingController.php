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
use App\Models\PaiementPrestataire;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingController extends Controller
{
    public function index()
    {
        $canSeeFacturation = auth()->check() && auth()->user()->canViewFacturationStats();

        // 1. KPIs globaux (rapides et nécessaires pour tous les utilisateurs)
        $totalEntreprises = Entreprise::count();
        $totalSalaries = Salarie::count();
        $totalAyantsDroit = AyantDroit::count();
        $totalBeneficiaires = $totalSalaries + $totalAyantsDroit;

        // Partenaires de santé
        $totalPraticiens = Praticien::count();
        $totalPharmacies = Pharmacie::count();

        // 2. Répartition des demandes par statut (optimisé avec pluck direct)
        $demandesParStatut = Demande::select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $statusConfig = [
            'approuvee'   => ['label' => 'Approuvée',   'color' => '#198754'],
            'rejetee'     => ['label' => 'Rejetée',     'color' => '#dc3545'],
            'en_attente'  => ['label' => 'En attente',  'color' => '#ffc107'],
        ];

        $labelsStatut = [];
        $dataStatut = [];
        $colorsStatut = [];

        foreach ($demandesParStatut as $statut => $total) {
            $config = $statusConfig[$statut] ?? [
                'label' => ucfirst(str_replace('_', ' ', (string) $statut)),
                'color' => '#6c757d'
            ];
            $labelsStatut[] = $config['label'];
            $dataStatut[] = (int) $total;
            $colorsStatut[] = $config['color'];
        }

        // 3. Données financières (chargées UNIQUEMENT si l'utilisateur est autorisé)
        $totalFacture = 0;
        $totalPaye = 0;
        $totalDu = 0;
        $labelsEvolution = [];
        $dataEvolution = [];
        $dernieresFactures = collect();

        if ($canSeeFacturation) {
            $totalFacture = floatval(Facture::sum('montant'));
            $totalPaye = floatval(PaiementPrestataire::sum('montant'));
            $totalDu = max(0, $totalFacture - $totalPaye);

            // Évolution des dépenses sur les 6 derniers mois
            $sixMoisAvant = Carbon::now()->subMonths(5)->startOfMonth();
            
            $driver = DB::connection()->getDriverName();
            $dateSelect = $driver === 'sqlite' 
                ? 'strftime("%Y-%m", date_facture) as mois' 
                : 'DATE_FORMAT(date_facture, "%Y-%m") as mois';

            $facturesParMois = Facture::select(
                DB::raw($dateSelect),
                DB::raw('SUM(montant) as total')
            )
            ->where('date_facture', '>=', $sixMoisAvant)
            ->groupBy('mois')
            ->pluck('total', 'mois');

            // Initialiser les 6 derniers mois (recherche O(1) par clé associative)
            for ($i = 5; $i >= 0; $i--) {
                $dateMois = Carbon::now()->subMonths($i);
                $cleMois = $dateMois->format('Y-m');
                $labelsEvolution[] = $dateMois->translatedFormat('M Y');
                $dataEvolution[] = floatval($facturesParMois[$cleMois] ?? 0);
            }

            // Aperçu des 5 dernières factures
            $dernieresFactures = Facture::with(['praticien', 'pharmacie'])
                ->orderBy('date_facture', 'desc')
                ->orderBy('id_facture', 'desc')
                ->take(5)
                ->get();
        }

        return view('reporting.index', compact(
            'canSeeFacturation',
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
