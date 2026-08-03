<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the participant dashboard.
     */
    public function index()
    {
        $salarie = Auth::guard('participant')->user();
        
        // Eager load only what is needed for the salarie directly
        $salarie->load(['ayantsDroit', 'entreprise']);
        
        // Eager load relations on the paginated query
        $demandes = $salarie->demandes()
                            ->with(['typeDemande', 'ayantDroit', 'praticien', 'pharmacie'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        
        return view('participant.dashboard', compact('salarie', 'demandes'));
    }
}
