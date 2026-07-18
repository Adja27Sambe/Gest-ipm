<?php

namespace App\Services\Demandes\Strategies;

use App\Services\Demandes\DemandeStrategyInterface;
use App\Models\Demande;
use App\Models\FeuilleMaladie;
use App\Models\Prestataire;

class FeuilleMaladieStrategy implements DemandeStrategyInterface
{
    public function validateSpecifics(array $data): void
    {
        // Filtre praticien : Tous les praticiens enregistrés (excluant pharmacies et opticiens)
        if (isset($data['id_prestataire'])) {
            $prestataire = Prestataire::with('type')->find($data['id_prestataire']);
            
            if ($prestataire && $prestataire->type) {
                $typeLibelle = strtolower($prestataire->type->libelle);
                if (str_contains($typeLibelle, 'pharmacie') || str_contains($typeLibelle, 'opticien')) {
                    throw new \Exception("Une Feuille de Maladie ne peut pas être émise pour une Pharmacie ou un Opticien.");
                }
            }
        }
    }

    public function process(Demande $demande, array $data)
    {
        // Générer un numéro unique
        $numeroFeuille = 'FM-' . date('Ymd') . '-' . strtoupper(uniqid());

        return FeuilleMaladie::create([
            'id_demande' => $demande->id_demande,
            'numero_feuille' => $numeroFeuille,
            'date_emission' => now(),
            'diagnostic' => $data['diagnostic'] ?? null,
            'observations' => $data['observations'] ?? null,
        ]);
    }
}
