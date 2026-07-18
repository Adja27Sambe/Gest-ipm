<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalarieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_salarie' => $this->id_salarie,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'date_naissance' => $this->date_naissance,
            'sexe' => $this->sexe,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'salaire' => $this->salaire,
            'date_embauche' => $this->date_embauche,
            'statut' => $this->statut,
            
            // Loaded relations
            'entreprise' => new EntrepriseResource($this->whenLoaded('entreprise')),
            'carte_assure' => $this->whenLoaded('carteAssure'),
        ];
    }
}
