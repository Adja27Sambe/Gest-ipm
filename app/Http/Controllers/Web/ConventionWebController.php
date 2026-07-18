<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Http\Requests\StoreConventionRequest;
use App\Http\Requests\UpdateConventionRequest;
use Illuminate\Http\Request;

class ConventionWebController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConventionRequest $request)
    {
        Convention::create($request->validated());
        return redirect()->back()->with('success', 'Convention ajoutée avec succès.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConventionRequest $request, Convention $convention)
    {
        $convention->update($request->validated());
        return redirect()->back()->with('success', 'Convention mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Convention $convention)
    {
        $convention->delete();
        return redirect()->back()->with('success', 'Convention supprimée avec succès.');
    }
}
