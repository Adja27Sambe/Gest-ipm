<?php

namespace App\Http\Controllers;

use App\Models\ParametreCouverture;
use App\Models\TypePrestation;
use Illuminate\Http\Request;

class ParametreCouvertureController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parametres = ParametreCouverture::with('typePrestation')->paginate(15);
        return view('parametres-couverture.index', compact('parametres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // On ne veut proposer que les types de prestation qui n'ont pas encore de paramètre configuré
        $typesPrestationExistants = ParametreCouverture::pluck('id_type_prestation');
        $typesPrestation = TypePrestation::whereNotIn('id_type_prestation', $typesPrestationExistants)->get();

        return view('parametres-couverture.create', compact('typesPrestation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation|unique:parametre_couverture,id_type_prestation',
            'taux_prise_charge' => 'required|numeric|min:0|max:100',
            'plafond_annuel' => 'nullable|numeric|min:0',
            'plafond_par_acte' => 'nullable|numeric|min:0',
            'ticket_moderateur' => 'nullable|numeric|min:0',
        ]);

        ParametreCouverture::create($validated);

        return redirect()->route('parametres-couverture.index')
            ->with('success', 'Le paramètre de couverture a été créé avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParametreCouverture $parametres_couverture)
    {
        $parametres_couverture->load('typePrestation');
        return view('parametres-couverture.edit', compact('parametres_couverture'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParametreCouverture $parametres_couverture)
    {
        $validated = $request->validate([
            'taux_prise_charge' => 'required|numeric|min:0|max:100',
            'plafond_annuel' => 'nullable|numeric|min:0',
            'plafond_par_acte' => 'nullable|numeric|min:0',
            'ticket_moderateur' => 'nullable|numeric|min:0',
        ]);

        $parametres_couverture->update($validated);

        return redirect()->route('parametres-couverture.index')
            ->with('success', 'Le paramètre de couverture a été mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParametreCouverture $parametres_couverture)
    {
        $parametres_couverture->delete();

        return redirect()->route('parametres-couverture.index')
            ->with('success', 'Le paramètre de couverture a été supprimé avec succès.');
    }
}
