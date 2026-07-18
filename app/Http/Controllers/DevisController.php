<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Devis;
use App\Enums\DevisStatut;
use App\Services\DevisTransitionService;
use Illuminate\Support\Facades\DB;

class DevisController extends Controller
{
    public function index(Request $request)
    {
        // Statistiques globales du mois
        $dateDebut = now()->startOfMonth()->toDateString();
        $dateFin = now()->endOfMonth()->toDateString();

        $stats = Devis::select('statut', DB::raw('SUM(montant) as total_montant'), DB::raw('COUNT(*) as total_devis'))
            ->whereBetween('date_devis', [$dateDebut, $dateFin])
            ->groupBy('statut')
            ->get();

        // Récupérer les devis avec les relations
        $query = Devis::with(['prestataire', 'beneficiaire', 'validations.utilisateur']);
        
        // Filtrage simple par statut si nécessaire
        if ($request->has('statut') && $request->statut !== 'all') {
            $query->where('statut', $request->statut);
        }

        $devis = $query->latest('date_devis')->paginate(15);

        return view('devis.index', compact('devis', 'stats'));
    }

    public function transition(Request $request, $id)
    {
        $devis = Devis::findOrFail($id);
        
        // Vérification de sécurité
        if (!auth()->user()->hasPermission('Valider les devis')) {
            return redirect()->back()->with('error', 'Action non autorisée. Vous n\'avez pas la permission de valider les devis.');
        }

        $request->validate([
            'statut' => ['required', 'string'],
            'commentaire' => ['nullable', 'string']
        ]);

        $newStatut = DevisStatut::tryFrom($request->statut);
        if (!$newStatut) {
            return redirect()->back()->with('error', 'Statut invalide.');
        }

        try {
            $service = new DevisTransitionService();
            $service->transition($devis, $newStatut, auth()->id(), $request->commentaire);

            return redirect()->back()->with('success', "Le devis a été passé au statut {$newStatut->value} avec succès.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
}
