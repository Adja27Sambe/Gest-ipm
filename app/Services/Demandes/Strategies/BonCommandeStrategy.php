<?php

namespace App\Services\Demandes\Strategies;

use App\Services\Demandes\DemandeStrategyInterface;
use App\Models\Demande;
use App\Models\BonCommande;
use App\Models\Prestataire;

class BonCommandeStrategy implements DemandeStrategyInterface
{
    public function validateSpecifics(array $data): void
    {
        // Filtre praticien : Uniquement pharmacies et opticiens
        $prestataire = Prestataire::with('type')->find($data['id_prestataire']);
        
        if (!$prestataire || !$prestataire->type) {
            throw new \Exception("Prestataire invalide.");
        }

        $typeLibelle = strtolower($prestataire->type->libelle);
        if (!str_contains($typeLibelle, 'pharmacie') && !str_contains($typeLibelle, 'opticien')) {
            throw new \Exception("Un Bon de Commande ne peut être émis que pour une Pharmacie ou un Opticien.");
        }
    }

    public function process(Demande $demande, array $data)
    {
        // Générer un numéro de bon unique
        $numeroBon = 'BC-' . date('Ymd') . '-' . strtoupper(uniqid());

        return BonCommande::create([
            'id_demande' => $demande->id_demande,
            'numero_bon' => $numeroBon,
            'date_emission' => now(),
            'date_ordonnance' => $data['date_ordonnance'] ?? null,
            'nombre_articles' => $data['nombre_articles'] ?? 1,
            // 'taux_prise_charge' et 'date_validite' peuvent être calculés ou définis ici
        ]);
    }
}
