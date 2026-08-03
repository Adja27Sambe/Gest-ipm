<?php

namespace App\Services\Demandes\Strategies;

use App\Services\Demandes\DemandeStrategyInterface;
use App\Models\Demande;
use App\Models\LettreGarantie;
use App\Models\ParametreCouverture;
use Carbon\Carbon;

class LettreGarantieStrategy implements DemandeStrategyInterface
{
    public function validateSpecifics(array $data): void
    {
        if (empty($data['id_praticien'])) {
            throw new \InvalidArgumentException("Un Praticien est obligatoire pour une Lettre de garantie.");
        }
        if (!empty($data['id_pharmacie'])) {
            throw new \InvalidArgumentException("Une Pharmacie ne peut pas être associée à une Lettre de garantie.");
        }
        if (empty($data['choix_acte'])) {
            throw new \InvalidArgumentException("Le choix de l'acte est requis pour une Lettre de Garantie.");
        }
    }

    public function process(Demande $demande, array $data)
    {
        $tauxPriseCharge = 80.00;
        if (!empty($data['id_type_prestation'])) {
            $parametre = ParametreCouverture::where('id_type_prestation', $data['id_type_prestation'])->first();
            if ($parametre) {
                $tauxPriseCharge = $parametre->taux_prise_charge;
            }
        }

        return LettreGarantie::create([
            'id_demande' => $demande->id_demande,
            'numero_lettre' => $demande->numero_demande ?? ('LG-' . uniqid()),
            'date_emission' => Carbon::now(),
            'choix_acte' => is_array($data['choix_acte']) ? implode(', ', $data['choix_acte']) : $data['choix_acte'],
            'observations' => $data['observations'] ?? null,
            'taux_prise_charge' => $tauxPriseCharge,
            'date_validite' => Carbon::now()->endOfMonth(),
        ]);
    }
}
