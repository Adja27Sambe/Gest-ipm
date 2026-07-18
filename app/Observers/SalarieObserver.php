<?php

namespace App\Observers;

use App\Models\Salarie;
use App\Models\CarteAssure;
use App\Events\SalarieRadie;

class SalarieObserver
{
    /**
     * Handle the Salarie "created" event.
     */
    public function created(Salarie $salarie): void
    {
        // Règle métier : création automatique de la CarteAssure via le service
        app(\App\Services\CarteAssureService::class)->creerCarte($salarie);
    }

    /**
     * Handle the Salarie "updated" event.
     */
    public function updated(Salarie $salarie): void
    {
        \Log::info("Salarie updated observer triggered", [
            'statut' => $salarie->statut,
            'wasChanged' => $salarie->wasChanged('statut')
        ]);
        
        // Cascade de statut
        if ($salarie->wasChanged('statut') && $salarie->statut === 'radie') {
            \Log::info("Dispatching SalarieRadie event");
            event(new SalarieRadie($salarie));
        }
    }
}
