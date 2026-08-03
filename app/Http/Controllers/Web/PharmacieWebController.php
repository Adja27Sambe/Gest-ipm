<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pharmacie;
use App\Http\Requests\StorePharmacieRequest;
use App\Http\Requests\UpdatePharmacieRequest;
use Illuminate\Http\Request;

class PharmacieWebController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $pharmacies = Pharmacie::latest('id_pharmacie')->paginate($perPage)->withQueryString();
        return view('pharmacies.index', compact('pharmacies'));
    }

    public function store(StorePharmacieRequest $request)
    {
        Pharmacie::create($request->validated());
        return redirect()->route('pharmacies.index')->with('success', 'Pharmacie ajoutée avec succès.');
    }

    public function update(UpdatePharmacieRequest $request, Pharmacie $pharmacie)
    {
        $pharmacie->update($request->validated());
        return redirect()->route('pharmacies.index')->with('success', 'Pharmacie mise à jour avec succès.');
    }

    public function destroy(Pharmacie $pharmacie)
    {
        $pharmacie->delete();
        return redirect()->route('pharmacies.index')->with('success', 'Pharmacie supprimée avec succès.');
    }
}
