<?php

namespace App\Observers;

use App\Models\Demande;
use Illuminate\Support\Facades\Log;

class DemandeObserver
{
    /**
     * Handle the Demande "created" event.
     */
    public function created(Demande $demande): void
    {
        // Magie Laravel : Cet événement se déclenche automatiquement
        // après la création en base. Idéal pour envoyer un email ou logger.
        Log::info("Une nouvelle demande de prise en charge a été créée.", ['id' => $demande->id_demande]);
    }

    /**
     * Handle the Demande "updated" event.
     */
    public function updated(Demande $demande): void
    {
        if ($demande->wasChanged('statut')) {
            Log::info("Le statut de la demande {$demande->id_demande} est passé à {$demande->statut}");
        }
    }

    /**
     * Handle the Demande "deleted" event.
     */
    public function deleted(Demande $demande): void
    {
        Log::info("Demande supprimée.", ['id' => $demande->id_demande]);
    }
}
