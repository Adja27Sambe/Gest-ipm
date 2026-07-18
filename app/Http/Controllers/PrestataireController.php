<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use App\Http\Requests\StorePrestataireRequest;
use App\Http\Requests\UpdatePrestataireRequest;

class PrestataireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Prestataire::with('type')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrestataireRequest $request)
    {
        $prestataire = Prestataire::create($request->validated());
        return response()->json(['success' => true, 'data' => $prestataire], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $prestataire = Prestataire::with(['type', 'conventions'])->findOrFail($id);
        return response()->json($prestataire);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrestataireRequest $request, string $id)
    {
        $prestataire = Prestataire::findOrFail($id);
        $prestataire->update($request->validated());
        return response()->json(['success' => true, 'data' => $prestataire]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $prestataire = Prestataire::findOrFail($id);
        $prestataire->delete();
        return response()->json(['success' => true, 'message' => 'Prestataire supprimé']);
    }
}
