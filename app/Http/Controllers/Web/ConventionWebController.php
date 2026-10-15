<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\Praticien;
use App\Models\PieceJointe;
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
        $data = $request->validated();
        unset($data['document']);

        $convention = Convention::create($data);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('conventions', 'public');

            PieceJointe::create([
                'attachable_type' => Convention::class,
                'attachable_id'   => $convention->id_convention,
                'nom_fichier'     => $file->getClientOriginalName(),
                'type_fichier'    => $file->getClientMimeType(),
                'chemin_fichier'  => $path,
                'date_ajout'      => now(),
                'id_utilisateur'  => auth()->id(),
            ]);
        }

        // Mettre à jour le statut a_convention du praticien si lié
        if (!empty($convention->id_praticien)) {
            $praticien = Praticien::find($convention->id_praticien);
            if ($praticien) {
                $praticien->update(['a_convention' => true]);
            }
        }

        return redirect()->back()->with('success', 'Convention ajoutée avec succès.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConventionRequest $request, Convention $convention)
    {
        $data = $request->validated();
        unset($data['document']);

        $convention->update($data);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('conventions', 'public');

            // On ajoute la nouvelle pièce jointe (ce qui conserve l'historique des anciennes pièces jointes)
            PieceJointe::create([
                'attachable_type' => Convention::class,
                'attachable_id'   => $convention->id_convention,
                'nom_fichier'     => $file->getClientOriginalName(),
                'type_fichier'    => $file->getClientMimeType(),
                'chemin_fichier'  => $path,
                'date_ajout'      => now(),
                'id_utilisateur'  => auth()->id(),
            ]);
        }

        if (!empty($convention->id_praticien)) {
            $praticien = Praticien::find($convention->id_praticien);
            if ($praticien) {
                $hasActive = Convention::where('id_praticien', $convention->id_praticien)->where('statut', 'active')->exists();
                $praticien->update(['a_convention' => $hasActive]);
            }
        }

        return redirect()->back()->with('success', 'Convention mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Convention $convention)
    {
        $idPrat = $convention->id_praticien;
        $convention->delete();

        if ($idPrat) {
            $hasActive = Convention::where('id_praticien', $idPrat)->where('statut', 'active')->exists();
            Praticien::where('PRCLEUNIK', $idPrat)->update(['A_CONVENTION' => $hasActive ? 1 : 0]);
        }

        return redirect()->back()->with('success', 'Convention supprimée avec succès.');
    }
}
