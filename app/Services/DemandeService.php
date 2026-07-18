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
        // 1. Récupération des données en lecture AVANT la transaction
        // Cela permet de réduire le temps de verrouillage (lock) dans la base de données.
        $typeDemande = TypeDemande::findOrFail($data['id_type_demande']);
        $libelleType = strtolower($typeDemande->libelle);

        $tauxPriseCharge = null;
        if (isset($data['id_type_prestation'])) {
            $parametre = ParametreCouverture::where('id_type_prestation', $data['id_type_prestation'])->first();
            if ($parametre) {
                $tauxPriseCharge = $parametre->taux_prise_charge;
            }
        }

        return DB::transaction(function () use ($data, $libelleType, $tauxPriseCharge, $typeDemande) {
            
            // 2. Création de la Demande de base
            $demande = Demande::create([
                'date_demande' => Carbon::now(),
                'motif' => $data['motif'] ?? null,
                'statut' => 'en_attente',
                'id_type_demande' => $data['id_type_demande'],
                'id_salarie' => $data['id_salarie'],
                'id_ayant_droit' => $data['id_ayant_droit'] ?? null,
                'id_type_prestation' => $data['id_type_prestation'] ?? null,
            ]);

            // 3. Routage vers le bon sous-document (Strategy/Match)
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

    private function creerBonCommande(Demande $demande, ?float $tauxPriseCharge, array $data)
    {
        return BonCommande::create([
            'numero_bon' => $this->generateUniqueNumber('BC', 'bon_commande', 'numero_bon'),
            'date_emission' => Carbon::now(),
            'taux_prise_charge' => $tauxPriseCharge ?? 80.00, // Défaut si non défini
            'date_validite' => Carbon::now()->addDays(30),
            'id_demande' => $demande->id_demande
        ]);
    }

    private function creerFeuilleMaladie(Demande $demande, array $data)
    {
        return FeuilleMaladie::create([
            'numero_feuille' => $this->generateUniqueNumber('FM', 'feuille_maladie', 'numero_feuille'),
            'date_emission' => Carbon::now(),
            'diagnostic' => $data['diagnostic'] ?? null,
            'observations' => $data['observations'] ?? null,
            'id_demande' => $demande->id_demande
        ]);
    }

    private function creerLettreGarantie(Demande $demande, ?float $tauxPriseCharge, array $data)
    {
        return LettreGarantie::create([
            'numero_lettre' => $this->generateUniqueNumber('LG', 'lettre_garantie', 'numero_lettre'),
            'date_emission' => Carbon::now(),
            'taux_prise_charge' => $tauxPriseCharge ?? 80.00,
            'date_validite' => Carbon::now()->addDays(30),
            'observations' => $data['observations'] ?? null,
            'id_demande' => $demande->id_demande
        ]);
    }
}
