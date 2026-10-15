<?php

namespace App\Http\Controllers;

use App\Models\PieceJointe;
use App\Models\CategorieDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Exports\PieceJointeExport;
use Maatwebsite\Excel\Facades\Excel;

class PieceJointeController extends Controller
{
    protected \App\Services\MediaManagerService $mediaManager;
    protected \App\Services\CloudBackupService $cloudBackup;

    public function __construct(\App\Services\MediaManagerService $mediaManager, \App\Services\CloudBackupService $cloudBackup)
    {
        $this->mediaManager = $mediaManager;
        $this->cloudBackup = $cloudBackup;
    }

    /**
     * Tableau de bord global de la gestion documentaire
     */
    public function index(Request $request)
    {
        $query = PieceJointe::with(['categorie', 'utilisateur', 'attachable'])->latest('date_ajout');

        if ($request->filled('id_categorie')) {
            $query->where('id_categorie', $request->id_categorie);
        }

        if ($request->filled('cloud_synced')) {
            $query->where('cloud_synced', $request->boolean('cloud_synced'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nom_fichier', 'like', "%{$search}%")
                  ->orWhereHas('utilisateur', function($uq) use ($search) {
                      $uq->where('login', 'like', "%{$search}%")
                         ->orWhere('nom', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 5);
        $pieces = $query->paginate($perPage)->withQueryString();
        $categories = CategorieDocument::orderBy('libelle')->get();
        $cloudStats = $this->cloudBackup->getSyncStats();

        return view('pieces-jointes.index', compact('pieces', 'categories', 'cloudStats'));
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

        $file = $request->file('fichier');
        
        // Optimisation et sauvegarde sécurisée avec déduplication et cloud backup auto
        $piece = $this->mediaManager->storeDocument($file, $validated);

        $cloudMessage = $piece->cloud_synced ? ' et sauvegardé sur le Cloud (Drive)' : '';
        return back()->with('success', "Document ajouté avec succès{$cloudMessage}.");
    }

    /**
     * Forcer la synchronisation d'un document spécifique sur le Cloud
     */
    public function syncCloud($id)
    {
        $piece = PieceJointe::findOrFail($id);
        $result = $this->cloudBackup->backupDocument($piece, true);

        if ($result['success']) {
            return back()->with('success', "Document #{$piece->id_piece} synchronisé avec succès sur le Cloud ({$piece->cloud_driver}).");
        }

        return back()->with('error', "Échec de la sauvegarde Cloud : " . ($result['message'] ?? 'Erreur'));
    }

    /**
     * Synchroniser tous les documents en attente sur le Cloud
     */
    public function syncAllCloud()
    {
        $result = $this->cloudBackup->backupAllDocuments();
        return back()->with('success', "Synchronisation Cloud terminée : {$result['synced']} document(s) sauvegardé(s).");
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

    /**
     * Exporter la liste des pièces jointes
     */
    public function export(Request $request)
    {
        $export = new PieceJointeExport($request->id_categorie);
        $filename = 'export_documents_' . date('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
}
