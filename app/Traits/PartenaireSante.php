<?php

namespace App\Traits;

trait PartenaireSante
{
    /**
     * Obtenir les informations de contact formatées.
     */
    public function getContactAttribute()
    {
        return $this->telephone . ($this->email ? ' - ' . $this->email : '');
    }

    /**
     * Vérifier si le partenaire a une adresse email valide.
     */
    public function hasEmail()
    {
        return !empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }
}
