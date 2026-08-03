<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;

class ValidationDemandeController extends Controller
{
    /**
     * Affiche la liste des demandes en attente de validation.
     */
    public function index()
    {
        $perPage = request('per_page', 5);
        $demandes = Demande::with(['typeDemande', 'salarie.entreprise', 'ayantDroit', 'bonCommande', 'feuilleMaladie', 'lettreGarantie'])
            ->where('statut', 'en_attente')
            ->orderBy('date_demande', 'desc')
            ->orderBy('id_demande', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('demandes.validation.index', compact('demandes'));
    }

    /**
     * Approuve une demande.
     */
    public function approuver(Demande $demande)
    {
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $demande->update(['statut' => 'validée']);

        return back()->with('success', 'La demande a été validée avec succès.');
    }

    /**
     * Rejette une demande.
     */
    public function rejeter(Demande $demande)
    {
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $demande->update(['statut' => 'rejetée']);

        return back()->with('success', 'La demande a été rejetée.');
    }
}
