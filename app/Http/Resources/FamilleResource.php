<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'salarie' => new SalarieResource($this),
            // Only return active ayants droit for the family view
            'ayants_droit_actifs' => AyantDroitResource::collection(
                $this->ayantsDroit->where('statut', 'actif')
            ),
        ];
    }
}
