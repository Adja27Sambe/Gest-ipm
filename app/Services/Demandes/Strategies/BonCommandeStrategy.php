<?php

namespace App\Services\Demandes\Strategies;

use App\Services\Demandes\DemandeStrategyInterface;
use App\Models\Demande;
use App\Models\BonCommande;
use Carbon\Carbon;

class BonCommandeStrategy implements DemandeStrategyInterface
{
    public function validateSpecifics(array $data): void
    {
        if (empty($data['id_pharmacie'])) {
            throw new \InvalidArgumentException("Une Pharmacie est obligatoire pour un Bon de commande.");
        }
        if (!empty($data['id_praticien'])) {
            throw new \InvalidArgumentException("Un Praticien ne peut pas être associé à un Bon de commande.");
        }
    }

    public function process(Demande $demande, array $data)
    {
        $parametre = \App\Models\ParametreCouverture::whereHas('typePrestation', function($q) {
            $q->where('libelle', 'like', '%pharmacie%');
        })->first();
        $taux = $parametre ? (float)$parametre->taux_prise_charge : 75.00;

        return BonCommande::create([
            'id_demande' => $demande->id_demande,
            'numero_bon' => $demande->numero_demande ?? ('BC-' . uniqid()),
            'date_emission' => Carbon::now(),
            'date_ordonnance' => $data['date_ordonnance'] ?? null,
            'nombre_articles' => $data['nombre_articles'] ?? 1,
            'taux_prise_charge' => $taux,
            'date_validite' => Carbon::now()->endOfMonth(),
        ]);
    }
}
