<?php

namespace App\Listeners;

use App\Events\SalarieRadie;

class UpdateAyantDroitStatut
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SalarieRadie $event): void
    {
        $salarie = $event->salarie;

        // Cascade status to 'inactif' for all related AyantDroit
        $salarie->ayantsDroit()->update(['statut' => 'inactif']);
    }
}
