<?php

namespace App\Http\Controllers;

use App\Models\Convention;
use App\Http\Requests\StoreConventionRequest;
use App\Http\Requests\UpdateConventionRequest;

class ConventionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Convention::with('prestataire')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConventionRequest $request)
    {
        $convention = Convention::create($request->validated());
        return response()->json(['success' => true, 'data' => $convention], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $convention = Convention::with('prestataire')->findOrFail($id);
        return response()->json($convention);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConventionRequest $request, string $id)
    {
        $convention = Convention::findOrFail($id);
        $convention->update($request->validated());
        return response()->json(['success' => true, 'data' => $convention]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $convention = Convention::findOrFail($id);
        $convention->delete();
        return response()->json(['success' => true, 'message' => 'Convention supprimée']);
    }
}
