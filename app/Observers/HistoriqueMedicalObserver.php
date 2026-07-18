<?php

namespace App\Observers;

use App\Models\HistoriqueMedical;
use App\Models\HistoriqueMouvement;

class HistoriqueMedicalObserver
{
    /**
     * Handle the HistoriqueMedical "created" event.
     */
    public function created(HistoriqueMedical $historiqueMedical): void
    {
        $this->logAction($historiqueMedical, 'AJOUT', 'Ajout d\'une nouvelle entrée au dossier médical.');
    }

    /**
     * Handle the HistoriqueMedical "updated" event.
     */
    public function updated(HistoriqueMedical $historiqueMedical): void
    {
        $this->logAction($historiqueMedical, 'MODIFICATION', 'Mise à jour d\'une entrée du dossier médical.');
    }

    private function logAction(HistoriqueMedical $historiqueMedical, string $action, string $description)
    {
        HistoriqueMouvement::create([
            'date_heure' => now(),
            'module' => 'Dossier Médical',
            'action' => $action,
            'description' => $description . " (Dossier ID: {$historiqueMedical->id_historique_medical}, Bénéficiaire: {$historiqueMedical->beneficiaire_type} - ID {$historiqueMedical->id_beneficiaire})",
            'adresse_ip' => request()->ip(),
            'id_utilisateur' => auth()->id(),
        ]);
    }
}
