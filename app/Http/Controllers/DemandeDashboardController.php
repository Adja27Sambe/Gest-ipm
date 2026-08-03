<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;

class DemandeDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => Demande::count(),
            'approuvees' => Demande::where('statut', 'Approuvée')->count(),
            'en_cours' => Demande::whereIn('statut', ['En cours', 'en_attente'])->count(),
            'rejetes' => Demande::where('statut', 'Rejetée')->count(),
        ];

        // Récents
        $recentes = Demande::with(['typeDemande', 'salarie', 'praticien', 'pharmacie'])
            ->latest('date_demande')
            ->take(5)
            ->get();

        return view('demandes.dashboard', compact('stats', 'recentes'));
    }
}
