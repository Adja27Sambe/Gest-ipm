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
            ->whereNotNull('ADCLEUNIK')
            ->orderBy('DATECOTISE', 'desc')
            ->paginate($perPage, ['*'], 'page_entreprises')
            ->withQueryString();

        // Cotisations Salariés
        $cotisationsSalaries = \App\Models\CotisationParticipant::with(['salarie', 'salarie.entreprise'])
            ->whereNotNull('PACLEUNIK')
            ->orderBy('DATECOTISE', 'desc')
            ->paginate($perPage, ['*'], 'page_salaries')
            ->withQueryString();

        return view('cotisations.index', compact('cotisationsEntreprises', 'cotisationsSalaries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entreprises = Entreprise::orderBy('ADHERANT')->get();
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

        $parsed = [];
        if (strpos($request->periode, '-') !== false) {
            $parts = explode('-', $request->periode);
            $parsed = ['annee' => $parts[0], 'mois' => $parts[1]];
        } elseif (strpos($request->periode, '/') !== false) {
            $parts = explode('/', $request->periode);
            $parsed = ['annee' => $parts[1], 'mois' => $parts[0]];
        } else {
            $parsed = ['annee' => date('Y'), 'mois' => date('m')];
        }

        if ($request->type_cotisation == 'entreprise') {
            $request->validate([
                'id_entreprise' => 'required|exists:entreprise,id_entreprise',
                'masse_salariale' => 'required|numeric|min:0',
            ]);

            $montant = ($request->masse_salariale * $request->taux) / 100;
            $montantRegle = $request->statut == 'payee' ? $montant : 0;

            Cotisation::create([
                'id_entreprise' => $request->id_entreprise,
                'mois' => $parsed['mois'],
                'annee' => $parsed['annee'],
                'masse_salariale' => $request->masse_salariale,
                'taux' => $request->taux,
                'montant' => $montant,
                'montant_regle' => $montantRegle,
                'date_cotisation' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
            ]);
        } else {
            $request->validate([
                'id_salarie' => 'required|exists:salarie,id_salarie',
                'salaire_base' => 'required|numeric|min:0',
            ]);

            $montant = ($request->salaire_base * $request->taux) / 100;
            $montantRegle = $request->statut == 'payee' ? $montant : 0;

            Cotisation::create([
                'id_salarie' => $request->id_salarie,
                'mois' => $parsed['mois'],
                'annee' => $parsed['annee'],
                'salaire_base' => $request->salaire_base,
                'taux' => $request->taux,
                'montant' => $montant,
                'montant_regle' => $montantRegle,
                'date_cotisation' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
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
        $entreprises = Entreprise::orderBy('ADHERANT')->get();
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

        $parsed = [];
        if (strpos($request->periode, '-') !== false) {
            $parts = explode('-', $request->periode);
            $parsed = ['annee' => $parts[0], 'mois' => $parts[1]];
        } elseif (strpos($request->periode, '/') !== false) {
            $parts = explode('/', $request->periode);
            $parsed = ['annee' => $parts[1], 'mois' => $parts[0]];
        } else {
            $parsed = ['annee' => date('Y'), 'mois' => date('m')];
        }

        if ($cotisation->id_entreprise) {
            $request->validate([
                'masse_salariale' => 'required|numeric|min:0',
            ]);
            $montant = ($request->masse_salariale * $request->taux) / 100;
            $montantRegle = $request->statut == 'payee' ? $montant : 0;
            $cotisation->update([
                'mois' => $parsed['mois'],
                'annee' => $parsed['annee'],
                'masse_salariale' => $request->masse_salariale,
                'taux' => $request->taux,
                'montant' => $montant,
                'montant_regle' => $montantRegle,
                'date_cotisation' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
            ]);
            
            if ($request->statut == 'payee' && $cotisation->id_entreprise) {
                $this->checkAndUnblockEntreprise($cotisation->id_entreprise);
            }
        } else {
            $request->validate([
                'salaire_base' => 'required|numeric|min:0',
            ]);
            $montant = ($request->salaire_base * $request->taux) / 100;
            $montantRegle = $request->statut == 'payee' ? $montant : 0;
            $cotisation->update([
                'mois' => $parsed['mois'],
                'annee' => $parsed['annee'],
                'salaire_base' => $request->salaire_base,
                'taux' => $request->taux,
                'montant' => $montant,
                'montant_regle' => $montantRegle,
                'date_cotisation' => $request->statut == 'payee' ? ($request->date_paiement ?? now()) : null,
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
            'montant_regle' => $cotisation->montant,
            'date_cotisation' => now()
        ]);

        if ($cotisation->id_entreprise) {
            $this->checkAndUnblockEntreprise($cotisation->id_entreprise);
        }

        return redirect()->back()->with('success', 'La cotisation a été marquée comme payée avec succès.');
    }

    /**
     * Vérifie si une entreprise peut être débloquée (plus de retard) et la réactive si nécessaire.
     */
    private function checkAndUnblockEntreprise($idEntreprise)
    {
        if (!$idEntreprise) return;

        $entreprise = Entreprise::find($idEntreprise);
        if (!$entreprise || $entreprise->ADACTIF != 2) return;

        $now = \Carbon\Carbon::now();
        $isLateInCurrentMonth = $now->day > 10;

        $hasUnpaidLateCotisations = Cotisation::where('ADCLEUNIK', $entreprise->id)
            ->whereRaw('MTREGLE < MTCOTISE')
            ->where(function($query) use ($now, $isLateInCurrentMonth) {
                $query->where('ANNEECOTISE', '<', $now->year)
                      ->orWhere(function($q) use ($now) {
                          $q->where('ANNEECOTISE', $now->year)
                            ->where('MOISCOTISE', '<', $now->month);
                      });
                      
                if ($isLateInCurrentMonth) {
                    $query->orWhere(function($q) use ($now) {
                        $q->where('ANNEECOTISE', $now->year)
                          ->where('MOISCOTISE', '=', $now->month);
                    });
                }
            })->exists();

        if (!$hasUnpaidLateCotisations) {
            DB::transaction(function() use ($entreprise) {
                $entreprise->ADACTIF = 1;
                $entreprise->save();

                $salaries = $entreprise->salaries()->where(function($q) {
                    $q->where('DEPART', 0)->orWhereNull('DEPART');
                })->get();

                foreach ($salaries as $salarie) {
                    $salarie->PARACTIF = 1;
                    $salarie->save();
                    \App\Models\AyantDroit::where('id_salarie', $salarie->id)->update(['statut' => 1]);
                }
            });
        }
    }
}
