<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AyantDroitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_ayant_droit' => $this->id_ayant_droit,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'lien_parente' => $this->lien_parente,
            'date_naissance' => $this->date_naissance,
            'date_mariage' => $this->date_mariage,
            'sexe' => $this->sexe,
            'statut' => $this->statut,
        ];
    }
}
