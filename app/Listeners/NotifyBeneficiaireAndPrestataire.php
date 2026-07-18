<?php

namespace App\Listeners;

use App\Events\DevisStatutChange;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyBeneficiaireAndPrestataire
{
    /**
     * Handle the event.
     */
    public function handle(DevisStatutChange $event): void
    {
        $devis = $event->devis;
        
        // Logique de notification (insertion dans la table NOTIFICATION ou envoi d'email)
        // Par exemple, insertion simple :
        if ($event->newStatut === 'valide' || $event->newStatut === 'rejete') {
            
            // Notification Prestataire
            Notification::create([
                'type' => 'devis_statut',
                'contenu' => "Le devis n°{$devis->id_devis} a été {$event->newStatut}.",
                'date_notification' => now(),
                'statut' => 'non-lue',
                'id_prestataire' => $devis->id_prestataire,
            ]);

            // Notification Salarié (bénéficiaire)
            $idSalarie = null;
            if ($devis->beneficiaire_type === \App\Models\Salarie::class) {
                $idSalarie = $devis->id_beneficiaire;
            } elseif ($devis->beneficiaire_type === \App\Models\AyantDroit::class) {
                $ayantDroit = \App\Models\AyantDroit::find($devis->id_beneficiaire);
                $idSalarie = $ayantDroit ? $ayantDroit->id_salarie : null;
            }

            if ($idSalarie) {
                Notification::create([
                    'type' => 'devis_statut',
                    'contenu' => "Votre devis a été {$event->newStatut}.",
                    'date_notification' => now(),
                    'statut' => 'non-lue',
                    'id_salarie' => $idSalarie,
                ]);
            }
        }
    }
}
