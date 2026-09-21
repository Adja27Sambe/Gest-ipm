<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventReadOnlyModifications
{
    /**
     * Handle an incoming request.
     *
     * Empêche toute modification, création ou suppression pour les utilisateurs
     * disposant d'un profil en lecture seule (comme le Superviseur / Lecteur).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isReadOnly') && $user->isReadOnly()) {
            $routeName = $request->route() ? $request->route()->getName() : null;

            // 1. Autoriser les requêtes d'authentification et de déconnexion
            if ($routeName === 'logout' || $request->is('logout')) {
                return $next($request);
            }

            // 2. Autoriser les routes de génération et de téléchargement de documents
            $allowedDocumentRoutes = [
                'cartes-assurees.generate',  // Générer la carte assuré
                'cartes-assurees.download',  // Télécharger la carte assuré PDF
                'demandes.pdf',              // Générer le PDF de prise en charge
                'pieces-jointes.download',   // Télécharger une pièce jointe
                'prestations.export',        // Exporter les prestations
                'audit.export',              // Exporter l'audit
            ];

            if ($routeName && in_array($routeName, $allowedDocumentRoutes)) {
                return $next($request);
            }

            // 3. Bloquer les requêtes de modification (POST, PUT, PATCH, DELETE)
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Action non autorisée',
                        'message' => 'Ce profil dispose uniquement d\'un rôle de lecteur (lecture seule).'
                    ], 403);
                }

                abort(403, "Action non autorisée : Ce profil dispose uniquement d'un rôle de lecteur (lecture seule).");
            }

            // 4. Bloquer l'accès aux pages de création et d'édition (GET sur *.create et *.edit)
            if ($routeName && (str_ends_with($routeName, '.create') || str_ends_with($routeName, '.edit'))) {
                abort(403, "Accès restreint : Ce profil dispose d'un accès en consultation seule (non éditeur).");
            }
        }

        return $next($request);
    }
}
