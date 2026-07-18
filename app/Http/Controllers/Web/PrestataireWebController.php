<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\TypePrestataire;
use App\Http\Requests\StorePrestataireRequest;
use App\Http\Requests\UpdatePrestataireRequest;
use Illuminate\Http\Request;

class PrestataireWebController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prestataires = Prestataire::with(['type', 'conventions'])->get();
        $types = \Illuminate\Support\Facades\Cache::rememberForever('types_prestataires_all', function() {
            return TypePrestataire::all();
        });
        return view('prestataires.index', compact('prestataires', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrestataireRequest $request)
    {
        Prestataire::create($request->validated());
        return redirect()->route('prestataires.index')->with('success', 'Prestataire ajouté avec succès.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrestataireRequest $request, Prestataire $prestataire)
    {
        $prestataire->update($request->validated());
        return redirect()->route('prestataires.index')->with('success', 'Prestataire mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prestataire $prestataire)
    {
        $prestataire->delete();
        return redirect()->route('prestataires.index')->with('success', 'Prestataire supprimé avec succès.');
    }
}
