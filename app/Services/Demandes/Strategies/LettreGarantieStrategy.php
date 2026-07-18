<?php

namespace App\Services\Demandes\Strategies;

use App\Services\Demandes\DemandeStrategyInterface;
use App\Models\Demande;
use App\Models\LettreGarantie;
use App\Models\Prestataire;

class LettreGarantieStrategy implements DemandeStrategyInterface
{
    public function validateSpecifics(array $data): void
    {
        // Filtre praticien : Tous les praticiens enregistrés (excluant pharmacies et opticiens)
        if (isset($data['id_prestataire'])) {
            $prestataire = Prestataire::with('type')->find($data['id_prestataire']);
            
            if ($prestataire && $prestataire->type) {
                $typeLibelle = strtolower($prestataire->type->libelle);
                if (str_contains($typeLibelle, 'pharmacie') || str_contains($typeLibelle, 'opticien')) {
                    throw new \Exception("Une Lettre de Garantie ne peut pas être émise pour une Pharmacie ou un Opticien.");
                }
            }
        }

        // Choix de l'acte est requis
        if (empty($data['choix_acte'])) {
            throw new \Exception("Le choix de l'acte est requis pour une Lettre de Garantie.");
        }
    }

    public function process(Demande $demande, array $data)
    {
        // Générer un numéro unique
        $numeroLettre = 'LG-' . date('Ymd') . '-' . strtoupper(uniqid());

        return LettreGarantie::create([
            'id_demande' => $demande->id_demande,
            'numero_lettre' => $numeroLettre,
            'date_emission' => now(),
            'choix_acte' => $data['choix_acte'],
            'observations' => $data['observations'] ?? null,
        ]);
    }
}
