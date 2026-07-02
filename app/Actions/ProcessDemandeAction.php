<?php

namespace App\Actions;

use App\Models\Demande;
use App\Models\BonCommande;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessDemandeAction
{
    /**
     * Traite une nouvelle demande de prise en charge et génère le bon de commande associé.
     * Approche "vibecoding" : une action dédiée pour la logique métier de la prise en charge.
     *
     * @param array $data
     * @return Demande
     */
    public function execute(array $data): Demande
    {
        return DB::transaction(function () use ($data) {
            // 1. Enregistrement de la demande
            $demande = Demande::create([
                'date_demande' => now(),
                'motif' => $data['motif'] ?? 'Consultation médicale',
                'statut' => 'En cours',
                'id_type_demande' => $data['id_type_demande'],
                'id_salarie' => $data['id_salarie'],
                'id_ayant_droit' => $data['id_ayant_droit'] ?? null,
            ]);

            // 2. Si la demande est directement approuvée, générer le bon de commande
            if (isset($data['auto_approuver']) && $data['auto_approuver'] === true) {
                $demande->update(['statut' => 'Approuvée']);
                
                BonCommande::create([
                    'numero_bon' => 'BC-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'date_emission' => now(),
                    'taux_prise_charge' => $data['taux_prise_charge'] ?? 80.00, // ex: 80%
                    'date_validite' => now()->addDays(30),
                    'id_demande' => $demande->id,
                ]);
            }

            return $demande->load('bonCommande');
        });
    }
}
