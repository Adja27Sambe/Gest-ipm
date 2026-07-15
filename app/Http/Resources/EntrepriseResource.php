<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntrepriseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_entreprise' => $this->id_entreprise,
            'code_adherent' => $this->code_adherent,
            'code_comptable' => $this->code_comptable,
            'raison_sociale' => $this->raison_sociale,
            'adresse' => $this->adresse,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'date_adhesion' => $this->date_adhesion,
            'statut' => $this->statut,
            
            // Computed/Loaded relationships
            'salaries_count' => $this->whenCounted('salaries'),
            'latest_relance_statut' => $this->whenLoaded('relances', function () {
                // Returns the statut of the most recent relance, if any
                return $this->relances->sortByDesc('date_relance')->first()?->statut;
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
