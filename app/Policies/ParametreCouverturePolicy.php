<?php

namespace App\Policies;

use App\Models\ParametreCouverture;
use App\Models\Utilisateur;

class ParametreCouverturePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Utilisateur $user): bool
    {
        return $user->hasPermission('gerer_parametres_couverture');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Utilisateur $user, ParametreCouverture $parametreCouverture): bool
    {
        return $user->hasPermission('gerer_parametres_couverture');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Utilisateur $user): bool
    {
        return $user->hasPermission('gerer_parametres_couverture');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Utilisateur $user, ParametreCouverture $parametreCouverture): bool
    {
        return $user->hasPermission('gerer_parametres_couverture');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Utilisateur $user, ParametreCouverture $parametreCouverture): bool
    {
        return $user->hasPermission('gerer_parametres_couverture');
    }
}
