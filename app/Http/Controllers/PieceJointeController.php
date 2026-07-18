<?php

namespace App\Http\Controllers;

use App\Models\PieceJointe;
use App\Models\CategorieDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class PieceJointeController extends Controller
{
    /**
     * Tableau de bord global de la gestion documentaire
     */
    public function index(Request $request)
    {
        $query = PieceJointe::with(['categorie', 'utilisateur', 'attachable'])->latest('date_ajout');

        if ($request->filled('id_categorie')) {
            $query->where('id_categorie', $request->id_categorie);
        }

        $pieces = $query->paginate(20);
        $categories = CategorieDocument::orderBy('libelle')->get();

        return view('pieces-jointes.index', compact('pieces', 'categories'));
    }
    /**
     * Uploader une nouvelle pièce jointe et l'attacher à une entité existante.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fichier' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // max 10 Mo
            'id_categorie' => 'required|exists:categorie_document,id_categorie',
            'attachable_type' => 'required|string',
            'attachable_id' => 'required|integer'
        ]);

        // Vérifier que le type cible est autorisé et existe
        $allowedTypes = [
            'App\Models\Demande',
            'App\Models\Prestation',
            'App\Models\Salarie',
            'App\Models\Entreprise'
        ];

        if (!in_array($validated['attachable_type'], $allowedTypes)) {
            return back()->with('error', 'Type d\'entité non autorisé pour les pièces jointes.');
        }

        $entityClass = $validated['attachable_type'];
        $entity = $entityClass::find($validated['attachable_id']);

        if (!$entity) {
            return back()->with('error', 'L\'entité parente est introuvable.');
        }

        // Vérification de permission basique sur l'entité (peut être délégué à une Policy)
        // if (!auth()->user()->can('update', $entity)) {
        //     abort(403, 'Action non autorisée');
        // }

        $file = $request->file('fichier');
        
        // Sécurité : Stocker dans le dossier privé "documents" qui n'est pas exposé au public (pas dans storage/app/public)
        $path = $file->store('documents');

        PieceJointe::create([
            'nom_fichier' => $file->getClientOriginalName(),
            'type_fichier' => $file->getClientMimeType(),
            'chemin_fichier' => $path,
            'date_ajout' => now(),
            'id_categorie' => $validated['id_categorie'],
            'id_utilisateur' => Auth::id(),
            'attachable_type' => $validated['attachable_type'],
            'attachable_id' => $validated['attachable_id'],
        ]);

        return back()->with('success', 'Document ajouté avec succès.');
    }

    /**
     * Télécharger ou visualiser une pièce jointe (Accès sécurisé).
     */
    public function show($id)
    {
        $piece = PieceJointe::findOrFail($id);

        $entity = $piece->attachable;

        // Autorisation: L'utilisateur a-t-il accès à l'entité attachée ?
        // Par exemple: si c'est une demande, vérifier s'il peut voir cette demande.
        if ($entity) {
            // Exemple de logique d'autorisation (à adapter selon les Policies existantes)
            // if (!auth()->user()->can('view', $entity)) { abort(403); }
        } else {
            // Si l'entité n'existe plus ou est orpheline, seuls les admins peuvent voir
            // if (!auth()->user()->hasRole('Administrateur')) { abort(403); }
        }

        if (!Storage::exists($piece->chemin_fichier)) {
            abort(404, 'Fichier introuvable sur le serveur.');
        }

        $path = Storage::path($piece->chemin_fichier);

        // Afficher directement dans le navigateur (pour les pdf et images)
        return Response::file($path, [
            'Content-Type' => $piece->type_fichier,
            'Content-Disposition' => 'inline; filename="' . $piece->nom_fichier . '"'
        ]);
    }
    
    /**
     * Forcer le téléchargement du fichier
     */
    public function download($id)
    {
        $piece = PieceJointe::findOrFail($id);

        // Autorisation (idem que show)
        
        if (!Storage::exists($piece->chemin_fichier)) {
            abort(404, 'Fichier introuvable sur le serveur.');
        }

        return Storage::download($piece->chemin_fichier, $piece->nom_fichier);
    }
    
    /**
     * Supprimer une pièce jointe
     */
    public function destroy($id)
    {
        $piece = PieceJointe::findOrFail($id);
        
        // Autorisation
        // if (!auth()->user()->can('delete', $piece)) { abort(403); }

        if (Storage::exists($piece->chemin_fichier)) {
            Storage::delete($piece->chemin_fichier);
        }
        
        $piece->delete();

        return back()->with('success', 'Pièce jointe supprimée.');
    }
}
