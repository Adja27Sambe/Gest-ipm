<?php

namespace App\Observers;

use App\Models\Entreprise;
use App\Models\HistoriqueMouvement;

class EntrepriseObserver
{
    /**
     * Handle the Entreprise "updated" event.
     */
    public function updated(Entreprise $entreprise): void
    {
        if ($entreprise->wasChanged('statut')) {
            HistoriqueMouvement::create([
                'date_heure' => now(),
                'module' => 'Entreprise',
                'action' => 'Changement de statut',
                'description' => "Le statut de l'entreprise {$entreprise->raison_sociale} est passé de {$entreprise->getOriginal('statut')} à {$entreprise->statut}.",
                'adresse_ip' => request()->ip(),
                'ancienne_valeur' => $entreprise->getOriginal('statut'),
                'nouvelle_valeur' => $entreprise->statut,
                'id_utilisateur' => null, // Utilisateur authentifié à implémenter plus tard (ex: auth()->id())
            ]);
        }
    }
}
