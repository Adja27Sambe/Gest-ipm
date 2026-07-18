<?php

namespace App\Observers;

use App\Models\PaiementPrestataire;

class PaiementPrestataireObserver
{
    /**
     * Handle the PaiementPrestataire "created" event.
     */
    public function created(PaiementPrestataire $paiement): void
    {
        $this->updateFactureStatus($paiement);
    }

    /**
     * Handle the PaiementPrestataire "updated" event.
     */
    public function updated(PaiementPrestataire $paiement): void
    {
        $this->updateFactureStatus($paiement);
    }

    /**
     * Handle the PaiementPrestataire "deleted" event.
     */
    public function deleted(PaiementPrestataire $paiement): void
    {
        $this->updateFactureStatus($paiement);
    }

    private function updateFactureStatus(PaiementPrestataire $paiement): void
    {
        $facture = $paiement->facture;
        if ($facture) {
            $soldeRestant = $facture->soldeRestant;

            if ($soldeRestant <= 0) {
                $facture->statut_paiement = 'soldee';
            } elseif ($soldeRestant < $facture->montant) {
                $facture->statut_paiement = 'partiellement_payee';
            } else {
                $facture->statut_paiement = 'en_attente';
            }
            
            $facture->save();
        }
    }
}
