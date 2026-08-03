<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\BonCommande;
use App\Models\FeuilleMaladie;
use App\Models\LettreGarantie;
use App\Models\ParametreCouverture;
use Illuminate\Support\Facades\DB;
use App\Traits\GenerateUniqueNumber;
use Carbon\Carbon;

class DemandeService
{
    use GenerateUniqueNumber;

    /**
     * Traite la création d'une demande et de son document associé
     */
    public function traiterDemande(array $data)
    {
        $typeDemande = TypeDemande::findOrFail($data['id_type_demande']);
        $libelleType = strtolower($typeDemande->libelle);

        $this->validerPartenaireStrict($libelleType, $data);

        $tauxPriseCharge = null;
        if (isset($data['id_type_prestation']) && !str_contains($libelleType, 'bon de commande')) {
            $parametre = ParametreCouverture::where('id_type_prestation', $data['id_type_prestation'])->first();
            if ($parametre) {
                $tauxPriseCharge = $parametre->taux_prise_charge;
            }
        }

        return DB::transaction(function () use ($data, $libelleType, $tauxPriseCharge, $typeDemande) {
            
            // Préfixe unique pour la demande
            $prefix = match (true) {
                str_contains($libelleType, 'bon de commande') => 'BC',
                str_contains($libelleType, 'feuille de maladie') => 'FM',
                str_contains($libelleType, 'lettre de garantie') => 'LG',
                default => 'DM'
            };

            // Création de la Demande de base
            $demande = Demande::create([
                'numero_demande' => $this->generateUniqueNumber($prefix, 'demande', 'numero_demande'),
                'date_demande' => Carbon::now(),
                'motif' => $data['motif'] ?? null,
                'statut' => 'en_attente',
                'id_type_demande' => $data['id_type_demande'],
                'id_salarie' => $data['id_salarie'],
                'id_ayant_droit' => $data['id_ayant_droit'] ?? null,
                'id_type_prestation' => str_contains($libelleType, 'bon de commande') ? null : ($data['id_type_prestation'] ?? null),
                'id_praticien' => str_contains($libelleType, 'bon de commande') ? null : ($data['id_praticien'] ?? null),
                'id_pharmacie' => str_contains($libelleType, 'bon de commande') ? ($data['id_pharmacie'] ?? null) : null,
            ]);

            // Routage vers le bon sous-document
            $document = match (true) {
                str_contains($libelleType, 'bon de commande') => $this->creerBonCommande($demande, $tauxPriseCharge, $data),
                str_contains($libelleType, 'feuille de maladie') => $this->creerFeuilleMaladie($demande, $data),
                str_contains($libelleType, 'lettre de garantie') => $this->creerLettreGarantie($demande, $tauxPriseCharge, $data),
                default => throw new \Exception("Type de demande non reconnu : {$typeDemande->libelle}"),
            };

            return [
                'demande' => $demande,
                'document' => $document
            ];
        });
    }

    private function validerPartenaireStrict(string $libelleType, array $data)
    {
        if (str_contains($libelleType, 'bon de commande')) {
            if (empty($data['id_pharmacie'])) {
                throw new \InvalidArgumentException("Une Pharmacie est obligatoire pour un Bon de commande.");
            }
            if (!empty($data['id_praticien'])) {
                throw new \InvalidArgumentException("Un Praticien ne peut pas être associé à un Bon de commande.");
            }
        } else {
            if (empty($data['id_praticien'])) {
                throw new \InvalidArgumentException("Un Praticien est obligatoire pour ce type de demande.");
            }
            if (!empty($data['id_pharmacie'])) {
                throw new \InvalidArgumentException("Une Pharmacie ne peut pas être associée à ce type de demande.");
            }
        }
    }

    private function creerBonCommande(Demande $demande, ?float $tauxPriseCharge, array $data)
    {
        return BonCommande::create([
            'date_emission' => Carbon::now(),
            'taux_prise_charge' => $tauxPriseCharge ?? 80.00,
            'date_validite' => Carbon::now()->endOfMonth(),
            'id_demande' => $demande->id_demande
        ]);
    }

    private function creerFeuilleMaladie(Demande $demande, array $data)
    {
        return FeuilleMaladie::create([
            'date_emission' => Carbon::now(),
            'diagnostic' => $data['diagnostic'] ?? null,
            'observations' => $data['observations'] ?? null,
            'id_demande' => $demande->id_demande
        ]);
    }

    private function creerLettreGarantie(Demande $demande, ?float $tauxPriseCharge, array $data)
    {
        return LettreGarantie::create([
            'date_emission' => Carbon::now(),
            'taux_prise_charge' => $tauxPriseCharge ?? 80.00,
            'date_validite' => Carbon::now()->endOfMonth(),
            'observations' => $data['observations'] ?? null,
            'choix_acte' => $data['choix_acte'] ?? null,
            'id_demande' => $demande->id_demande
        ]);
    }
}
