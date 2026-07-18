<?php

namespace App\Traits;

use App\Models\PieceJointe;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPiecesJointes
{
    /**
     * Obtenir toutes les pièces jointes de cette entité.
     */
    public function piecesJointes(): MorphMany
    {
        return $this->morphMany(PieceJointe::class, 'attachable');
    }
}
