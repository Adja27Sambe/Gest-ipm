<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prestataire;
use Illuminate\Support\Facades\DB;

class PrestataireRechercheController extends Controller
{
    /**
     * Recherche géolocalisée de prestataires avec conventions actives.
     */
    public function search(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'rayon' => 'nullable|numeric|min:1', // rayon en kilomètres, défaut 10km
            'id_type' => 'nullable|exists:type_prestataire,id_type',
            'specialite' => 'nullable|string'
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $rayon = $request->input('rayon', 10); // Rayon par défaut de 10 km

        // Requête Eloquent de base, filtrée par convention active
        $query = Prestataire::conventionActive()->with('type');

        // Filtre optionnel par type
        if ($request->has('id_type')) {
            $query->where('id_type', $request->input('id_type'));
        }

        // Filtre optionnel par spécialité
        if ($request->has('specialite')) {
            $query->where('specialite', 'LIKE', '%' . $request->input('specialite') . '%');
        }

        // Ajout du calcul de la distance (Formule de Haversine en SQL natif)
        $query->select('*')
              ->selectRaw(
                  "( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance",
                  [$latitude, $longitude, $latitude]
              )
              ->having('distance', '<', $rayon)
              ->orderBy('distance', 'asc');

        $prestataires = $query->get();

        return response()->json([
            'success' => true,
            'count' => $prestataires->count(),
            'rayon_recherche_km' => $rayon,
            'data' => $prestataires
        ]);
    }
}
