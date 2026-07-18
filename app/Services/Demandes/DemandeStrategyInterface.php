<?php

namespace App\Services\Demandes;

use App\Models\Demande;
use Illuminate\Http\Request;

interface DemandeStrategyInterface
{
    /**
     * Valide les règles métier spécifiques à ce type de demande.
     * @param array $data Les données validées issues de la FormRequest.
     * @throws \Exception
     */
    public function validateSpecifics(array $data): void;

    /**
     * Traite et enregistre les informations spécifiques de la demande.
     * @param Demande $demande L'instance de la demande principale.
     * @param array $data Les données de la requête.
     * @return Model Le modèle spécifique créé (BonCommande, FeuilleMaladie, etc.)
     */
    public function process(Demande $demande, array $data);
}
