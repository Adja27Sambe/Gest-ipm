<?php

namespace App\Actions;

use App\Models\Salarie;
use App\Models\CarteAssure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateSalarieAction
{
    /**
     * Crée un nouveau salarié et génère automatiquement sa carte d'assuré.
     * Approche "vibecoding" : une action dédiée pour encapsuler la logique métier complexe.
     *
     * @param array $data
     * @return Salarie
     */
    public function execute(array $data): Salarie
    {
        return DB::transaction(function () use ($data) {
            // 1. Création du salarié
            $salarie = Salarie::create([
                'matricule' => $data['matricule'] ?? 'MAT-' . strtoupper(Str::random(6)),
                'nom' => $data['nom'],
                'prenom' => $data['prenom'] ?? null,
                'date_naissance' => $data['date_naissance'] ?? null,
                'sexe' => $data['sexe'] ?? null,
                'telephone' => $data['telephone'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'salaire' => $data['salaire'] ?? null,
                'date_embauche' => $data['date_embauche'] ?? null,
                'statut' => $data['statut'] ?? 'Actif',
                'id_entreprise' => $data['id_entreprise'],
            ]);

            // 2. Création automatique de la carte d'assuré
            CarteAssure::create([
                'numero_carte' => 'IPM-' . date('Y') . '-' . str_pad($salarie->id, 6, '0', STR_PAD_LEFT),
                'matricule' => $salarie->matricule,
                'date_emission' => now(),
                'qr_code' => 'QR_DATA_PLACEHOLDER', // Pourrait être généré par un service
                'statut' => 'Valide',
                'id_salarie' => $salarie->id,
            ]);

            return $salarie->load('carteAssure');
        });
    }
}
