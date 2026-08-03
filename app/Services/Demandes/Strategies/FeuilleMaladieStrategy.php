<?php

namespace App\Services\Demandes\Strategies;

use App\Services\Demandes\DemandeStrategyInterface;
use App\Models\Demande;
use App\Models\FeuilleMaladie;
use App\Models\ParametreCouverture;
use Carbon\Carbon;

class FeuilleMaladieStrategy implements DemandeStrategyInterface
{
    public function validateSpecifics(array $data): void
    {
        if (empty($data['id_praticien'])) {
            throw new \InvalidArgumentException("Un Praticien est obligatoire pour une Feuille de maladie.");
        }
        if (!empty($data['id_pharmacie'])) {
            throw new \InvalidArgumentException("Une Pharmacie ne peut pas être associée à une Feuille de maladie.");
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

        return FeuilleMaladie::create([
            'id_demande' => $demande->id_demande,
            'numero_feuille' => $demande->numero_demande ?? ('FM-' . uniqid()),
            'date_emission' => Carbon::now(),
            'diagnostic' => $data['diagnostic'] ?? null,
            'observations' => $data['observations'] ?? null,
            'taux_prise_charge' => $tauxPriseCharge,
        ]);
    }
}
