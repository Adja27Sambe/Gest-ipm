<?php

namespace App\Policies;

use App\Models\Devis;
use App\Models\Utilisateur;

class DevisPolicy
{
    /**
     * Autoriser si l'utilisateur a la permission de valider les devis ou s'il est admin.
     */
    public function transition(Utilisateur $user, Devis $devis): bool
    {
        return $user->hasPermission('Valider les devis');
    }
}
