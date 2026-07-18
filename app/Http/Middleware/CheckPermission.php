<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur possède la permission demandée
        if (!$request->user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Accès refusé. Vous ne possédez pas la permission requise.'
                ], 403);
            }
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
