<?php

namespace App\Observers;

use App\Models\Prestation;

class PrestationObserver
{
    /**
     * Handle the Prestation "creating" event.
     */
    public function creating(Prestation $prestation): void
    {
        $this->calculerResteACharge($prestation);
    }

    /**
     * Handle the Prestation "updating" event.
     */
    public function updating(Prestation $prestation): void
    {
        $this->calculerResteACharge($prestation);
    }

    private function calculerResteACharge(Prestation $prestation): void
    {
        if ($prestation->montant !== null && $prestation->taux_prise_charge !== null) {
            $priseEnCharge = ($prestation->montant * $prestation->taux_prise_charge) / 100;
            $prestation->reste_a_charge = $prestation->montant - $priseEnCharge;
        }
    }
}
