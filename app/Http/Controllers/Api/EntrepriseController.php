<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEntrepriseRequest;
use App\Http\Requests\UpdateEntrepriseRequest;
use App\Http\Resources\EntrepriseResource;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EntrepriseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Entreprise::withCount('salaries')->with('relances');

        if ($request->filled('raison_sociale')) {
            $query->where('raison_sociale', 'like', '%' . $request->raison_sociale . '%');
        }

        if ($request->filled('code_adherent')) {
            $query->where('code_adherent', $request->code_adherent);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return EntrepriseResource::collection($query->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEntrepriseRequest $request)
    {
        $entreprise = Entreprise::create($request->validated());
        // Load the count for the resource
        $entreprise->loadCount('salaries');

        return new EntrepriseResource($entreprise);
    }

    /**
     * Display the specified resource.
     */
    public function show(Entreprise $entreprise)
    {
        $entreprise->loadCount('salaries')->load('relances');
        return new EntrepriseResource($entreprise);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntrepriseRequest $request, Entreprise $entreprise)
    {
        $entreprise->update($request->validated());
        
        $entreprise->loadCount('salaries')->load('relances');
        return new EntrepriseResource($entreprise);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entreprise $entreprise): JsonResponse
    {
        if ($entreprise->salaries()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer cette entreprise car elle possède des salariés.'
            ], 409); // 409 Conflict
        }

        $entreprise->delete();

        return response()->json(null, 204);
    }

    /**
     * Met à jour uniquement le statut de l'entreprise.
     */
    public function updateStatus(Request $request, Entreprise $entreprise)
    {
        $validated = $request->validate([
            'statut' => 'required|string|in:actif,suspendu,résilié',
        ]);

        $entreprise->update(['statut' => $validated['statut']]);

        return new EntrepriseResource($entreprise->loadCount('salaries')->load('relances'));
    }
}
