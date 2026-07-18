<?php

namespace App\Services;

use App\Models\Devis;
use App\Models\ValidationDevis;
use App\Enums\DevisStatut;
use App\Events\DevisStatutChange;
use Illuminate\Support\Facades\DB;
use Exception;

class DevisTransitionService
{
    /**
     * Change le statut d'un devis
     */
    public function transition(Devis $devis, DevisStatut $newStatut, int $userId, ?string $commentaire = null): Devis
    {
        return DB::transaction(function () use ($devis, $newStatut, $userId, $commentaire) {
            
            $oldStatut = $devis->statut;

            // Règles métier basiques
            if ($oldStatut === DevisStatut::REJETE && $newStatut === DevisStatut::VALIDE) {
                throw new Exception("Un devis rejeté ne peut pas être directement validé. Il doit d'abord être soumis ou mis en revue.");
            }

            if ($oldStatut === $newStatut) {
                throw new Exception("Le devis est déjà au statut {$newStatut->value}.");
            }

            // Mise à jour du statut
            $devis->update(['statut' => $newStatut]);

            // Historisation dans VALIDATION_DEVIS
            ValidationDevis::create([
                'date_validation' => now(),
                'decision' => $newStatut->value,
                'commentaire' => $commentaire,
                'id_devis' => $devis->id_devis,
                'id_utilisateur' => $userId
            ]);

            // Déclencher l'événement (pour notifier)
            event(new DevisStatutChange($devis, $oldStatut?->value ?? 'null', $newStatut->value));

            return $devis->refresh();
        });
    }
}
