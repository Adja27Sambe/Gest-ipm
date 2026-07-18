<?php

namespace App\Policies;

use App\Models\HistoriqueMedical;
use App\Models\Utilisateur;

class HistoriqueMedicalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Utilisateur $user): bool
    {
        return $user->hasPermission('consulter_dossier_medical');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Utilisateur $user, HistoriqueMedical $historiqueMedical): bool
    {
        return $user->hasPermission('consulter_dossier_medical');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Utilisateur $user): bool
    {
        return $user->hasPermission('consulter_dossier_medical');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Utilisateur $user, HistoriqueMedical $historiqueMedical): bool
    {
        return $user->hasPermission('consulter_dossier_medical');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Utilisateur $user, HistoriqueMedical $historiqueMedical): bool
    {
        return false; // On ne supprime jamais un dossier médical
    }
}
