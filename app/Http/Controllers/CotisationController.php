<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotisation;
use App\Models\Salarie;
use App\Models\Entreprise;
use Illuminate\Support\Facades\DB;

class CotisationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);

        // Cotisations Entreprises
        $cotisationsEntreprises = Cotisation::with('entreprise')
            ->whereNotNull('id_entreprise')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page_entreprises')
            ->withQueryString();

        // Cotisations Salariés
        $cotisationsSalaries = Cotisation::with(['salarie', 'salarie.entreprise'])
            ->whereNotNull('id_salarie')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page_salaries')
            ->withQueryString();

        return view('cotisations.index', compact('cotisationsEntreprises', 'cotisationsSalaries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entreprises = Entreprise::orderBy('raison_sociale')->get();
        $salaries = Salarie::with('entreprise')->orderBy('nom')->get();

        return view('cotisations.create', compact('entreprises', 'salaries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_cotisation' => 'required|in:entreprise,salarie',
            'periode' => 'required|string|max:50',
            'taux' => 'required|numeric|min:0',
            'statut' => 'required|in:payee,impayee',
            'date_paiement' => 'nullable|date',
        ]);

        if ($request->type_cotisation == 'entreprise') {
            $request->validate([
                'id_entreprise' => 'required|exists:entreprise,id_entreprise',
                'masse_salariale' => 'required|numeric|min:0',
            ]);

            Cotisation::create([
                'id_entreprise' => $request->id_entreprise,
                'periode' => $request->periode,
                'masse_salariale' => $request->masse_salariale,
                'taux' => $request->taux,
                'statut' => $request->statut,
                'date_paiement' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
            ]);
        } else {
            $request->validate([
                'id_salarie' => 'required|exists:salarie,id_salarie',
                'salaire_base' => 'required|numeric|min:0',
            ]);

            Cotisation::create([
                'id_salarie' => $request->id_salarie,
                'periode' => $request->periode,
                'salaire_base' => $request->salaire_base,
                'taux' => $request->taux,
                'statut' => $request->statut,
                'date_paiement' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
            ]);
        }

        return redirect()->route('cotisations.index')->with('success', 'Cotisation enregistrée avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $cotisation = Cotisation::findOrFail($id);
        $entreprises = Entreprise::orderBy('raison_sociale')->get();
        $salaries = Salarie::with('entreprise')->orderBy('nom')->get();

        return view('cotisations.edit', compact('cotisation', 'entreprises', 'salaries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cotisation = Cotisation::findOrFail($id);

        $request->validate([
            'periode' => 'required|string|max:50',
            'taux' => 'required|numeric|min:0',
            'statut' => 'required|in:payee,impayee',
            'date_paiement' => 'nullable|date',
        ]);

        if ($cotisation->id_entreprise) {
            $request->validate([
                'masse_salariale' => 'required|numeric|min:0',
            ]);
            $cotisation->update([
                'periode' => $request->periode,
                'masse_salariale' => $request->masse_salariale,
                'taux' => $request->taux,
                'statut' => $request->statut,
                'date_paiement' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
            ]);
        } else {
            $request->validate([
                'salaire_base' => 'required|numeric|min:0',
            ]);
            $cotisation->update([
                'periode' => $request->periode,
                'salaire_base' => $request->salaire_base,
                'taux' => $request->taux,
                'statut' => $request->statut,
                'date_paiement' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
            ]);
        }

        return redirect()->route('cotisations.index')->with('success', 'Cotisation mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cotisation = Cotisation::findOrFail($id);
        $cotisation->delete();

        return redirect()->route('cotisations.index')->with('success', 'Cotisation supprimée avec succès.');
    }

    /**
     * Marque une cotisation comme payée manuellement.
     */
    public function payer($id)
    {
        $cotisation = Cotisation::findOrFail($id);
        $cotisation->update([
            'statut' => 'payee',
            'date_paiement' => now()
        ]);

        return redirect()->back()->with('success', 'La cotisation a été marquée comme payée avec succès.');
    }
}
