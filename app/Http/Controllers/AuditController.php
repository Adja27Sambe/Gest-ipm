<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueMouvement;
use App\Models\Utilisateur;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Affiche l'historique des mouvements avec filtres.
     */
    public function index(Request $request)
    {
        $utilisateurs = Utilisateur::select('id_utilisateur', 'login', 'nom', 'prenom')->get();
        $query = HistoriqueMouvement::with('utilisateur')->orderBy('date_heure', 'desc');

        if ($request->has('id_utilisateur') && $request->id_utilisateur != '') {
            $query->where('id_utilisateur', $request->id_utilisateur);
        }

        if ($request->has('module') && $request->module != '') {
            $query->where('module', $request->module);
        }

        if ($request->has('action') && $request->action != '') {
            $query->where('action', $request->action);
        }

        if ($request->has('date_debut') && $request->date_debut != '') {
            $query->where('date_heure', '>=', $request->date_debut . ' 00:00:00');
        }

        if ($request->has('date_fin') && $request->date_fin != '') {
            $query->where('date_heure', '<=', $request->date_fin . ' 23:59:59');
        }

        $historiques = $query->paginate(20);
        $historiques->appends($request->all());

        $modules_disponibles = HistoriqueMouvement::select('module')->distinct()->whereNotNull('module')->pluck('module');
        $actions_disponibles = HistoriqueMouvement::select('action')->distinct()->whereNotNull('action')->pluck('action');

        return view('audit.index', compact('historiques', 'utilisateurs', 'modules_disponibles', 'actions_disponibles'));
    }

    /**
     * Export de l'audit au format CSV.
     */
    public function export(Request $request)
    {
        $query = HistoriqueMouvement::with('utilisateur')->orderBy('date_heure', 'desc');

        if ($request->has('id_utilisateur') && $request->id_utilisateur != '') {
            $query->where('id_utilisateur', $request->id_utilisateur);
        }

        if ($request->has('module') && $request->module != '') {
            $query->where('module', $request->module);
        }

        if ($request->has('action') && $request->action != '') {
            $query->where('action', $request->action);
        }

        if ($request->has('date_debut') && $request->date_debut != '') {
            $query->where('date_heure', '>=', $request->date_debut . ' 00:00:00');
        }

        if ($request->has('date_fin') && $request->date_fin != '') {
            $query->where('date_heure', '<=', $request->date_fin . ' 23:59:59');
        }

        $historiques = $query->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=audit_export_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ['ID', 'Date et Heure', 'Utilisateur', 'Module', 'Action', 'Adresse IP', 'Description', 'Ancienne Valeur', 'Nouvelle Valeur'];

        $callback = function() use($historiques, $columns) {
            $file = fopen('php://output', 'w');
            
            // BOM pour excel UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($historiques as $historique) {
                $row = [
                    $historique->id_historique,
                    $historique->date_heure,
                    $historique->utilisateur ? $historique->utilisateur->login : 'Système',
                    $historique->module,
                    $historique->action,
                    $historique->adresse_ip,
                    $historique->description,
                    $historique->ancienne_valeur,
                    $historique->nouvelle_valeur
                ];

                fputcsv($file, $row, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
