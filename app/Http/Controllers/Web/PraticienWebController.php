<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Praticien;
use App\Http\Requests\StorePraticienRequest;
use App\Http\Requests\UpdatePraticienRequest;
use Illuminate\Http\Request;

class PraticienWebController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $praticiens = Praticien::latest('id_praticien')->paginate($perPage)->withQueryString();
        return view('praticiens.index', compact('praticiens'));
    }

    public function store(StorePraticienRequest $request)
    {
        Praticien::create($request->validated());
        return redirect()->route('praticiens.index')->with('success', 'Praticien ajouté avec succès.');
    }

    public function update(UpdatePraticienRequest $request, Praticien $praticien)
    {
        $praticien->update($request->validated());
        return redirect()->route('praticiens.index')->with('success', 'Praticien mis à jour avec succès.');
    }

    public function destroy(Praticien $praticien)
    {
        $praticien->delete();
        return redirect()->route('praticiens.index')->with('success', 'Praticien supprimé avec succès.');
    }
}
