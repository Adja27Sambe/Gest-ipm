<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypePrestation;
use App\Models\TypeActe;
use App\Models\ParametreCouverture;
use Illuminate\Support\Str;

class ActeController extends Controller
{
    /**
     * Écran de paramétrage des actes généraux et de leurs types d'actes associés.
     */
    public function index(Request $request)
    {
        $query = TypePrestation::with(['typesActes', 'parametreCouverture']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('typesActes', function($tq) use ($search) {
                      $tq->where('libelle', 'like', "%{$search}%");
                  });
            });
        }

        $actes = $query->orderBy('id_type_prestation')->get();

        return view('actes.index', compact('actes'));
    }

    /**
     * Création d'un nouvel acte général.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:type_prestation,libelle',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'statut' => 'required|in:actif,inactif',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(Str::slug(substr($validated['libelle'], 0, 8), '_'));
        }

        $acte = TypePrestation::create($validated);

        return redirect()->route('actes.index')->with('success', "L'acte général '{$acte->libelle}' a été créé avec succès.");
    }

    /**
     * Mise à jour d'un acte général.
     */
    public function update(Request $request, TypePrestation $acte)
    {
        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:type_prestation,libelle,' . $acte->id_type_prestation . ',id_type_prestation',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'statut' => 'required|in:actif,inactif',
        ]);

        $acte->update($validated);

        return redirect()->route('actes.index')->with('success', "L'acte général '{$acte->libelle}' a été mis à jour.");
    }

    /**
     * Suppression d'un acte général.
     */
    public function destroy(TypePrestation $acte)
    {
        $acte->delete();
        return redirect()->route('actes.index')->with('success', "L'acte a été supprimé.");
    }

    /**
     * API : Recherche rapide et autocomplétion des types d'actes.
     */
    public function getTypesActes(Request $request)
    {
        $query = TypeActe::where('statut', 'actif');

        if ($request->filled('id_type_prestation')) {
            $query->where('id_type_prestation', $request->id_type_prestation);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where('libelle', 'like', "%{$search}%");
        }

        $results = $query->orderBy('libelle')->get(['id_type_acte', 'id_type_prestation', 'libelle', 'description']);

        return response()->json($results);
    }

    /**
     * API : Ajout dynamique / rapide d'un type d'acte (avec gestion anti-doublons).
     */
    public function storeTypeActe(Request $request)
    {
        $request->validate([
            'id_type_prestation' => 'required|exists:type_prestation,id_type_prestation',
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $typeActe = TypeActe::findOrCreateClean(
                (int)$request->id_type_prestation,
                $request->libelle,
                $request->description
            );

            return response()->json([
                'success' => true,
                'data' => $typeActe,
                'message' => "Type d'acte rattaché avec succès.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
