<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salarie;
use App\Http\Requests\StoreSalarieRequest;
use App\Http\Requests\UpdateSalarieRequest;
use App\Http\Resources\SalarieResource;
use App\Http\Resources\FamilleResource;
use Illuminate\Http\Request;

class SalarieController extends Controller
{
    public function index()
    {
        $salaries = Salarie::with(['entreprise', 'carteAssure'])->paginate(15);
        return SalarieResource::collection($salaries);
    }

    public function store(StoreSalarieRequest $request)
    {
        // CarteAssure will be created automatically by SalarieObserver
        $salarie = Salarie::create($request->validated());
        
        return new SalarieResource($salarie->load(['entreprise', 'carteAssure']));
    }

    public function show(Salarie $salarie)
    {
        return new SalarieResource($salarie->load(['entreprise', 'carteAssure', 'ayantsDroit']));
    }

    public function update(UpdateSalarieRequest $request, Salarie $salarie)
    {
        // Status cascade handles in Observer if it changes to "radie"
        $salarie->update($request->validated());
        
        return new SalarieResource($salarie->load(['entreprise', 'carteAssure']));
    }

    public function destroy(Salarie $salarie)
    {
        $salarie->delete();
        return response()->json(null, 204);
    }

    public function search(Request $request)
    {
        $query = Salarie::with(['entreprise', 'carteAssure']);

        if ($request->filled('matricule')) {
            $query->where('matricule', 'like', '%' . $request->matricule . '%');
        }

        if ($request->filled('nom')) {
            $query->where('nom', 'like', '%' . $request->nom . '%');
        }

        if ($request->filled('entreprise')) {
            $query->whereHas('entreprise', function ($q) use ($request) {
                $q->where('raison_sociale', 'like', '%' . $request->entreprise . '%');
            });
        }

        return SalarieResource::collection($query->paginate(15));
    }

    public function famille(Salarie $salarie)
    {
        // Load relationships needed for FamilleResource
        $salarie->load(['entreprise', 'carteAssure', 'ayantsDroit']);
        
        return new FamilleResource($salarie);
    }
}
