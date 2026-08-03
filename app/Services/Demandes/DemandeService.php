<?php

namespace App\Services\Demandes;

use App\Models\Demande;
use App\Models\TypeDemande;
use App\Models\AyantDroit;
use App\Services\Demandes\Strategies\BonCommandeStrategy;
use App\Services\Demandes\Strategies\FeuilleMaladieStrategy;
use App\Services\Demandes\Strategies\LettreGarantieStrategy;
use Illuminate\Support\Facades\DB;

class DemandeService
{
    /**
     * @var DemandeStrategyInterface[]
     */
    protected array $strategies = [];

    public function __construct()
    {
        $this->strategies = [
            'BC' => new BonCommandeStrategy(),
            'FM' => new FeuilleMaladieStrategy(),
            'LG' => new LettreGarantieStrategy(),
        ];
    }

    /**
     * Traite la création d'une demande avec la stratégie appropriée
     */
    public function createDemande(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validation Ayant Droit
            if (!empty($data['id_ayant_droit'])) {
                $ayantDroit = AyantDroit::findOrFail($data['id_ayant_droit']);
                if (!$ayantDroit->isEligible()) {
                    throw new \Exception("Le bénéficiaire ayant droit a atteint la limite d'âge de 21 ans et n'est plus éligible.");
                }
            }

            $typeDemande = TypeDemande::findOrFail($data['id_type_demande']);
            $strategyCode = $this->getStrategyCodeFromLibelle($typeDemande->libelle);

            if (!isset($this->strategies[$strategyCode])) {
                throw new \Exception("Stratégie non définie pour le type de demande: " . $typeDemande->libelle);
            }

            $strategy = $this->strategies[$strategyCode];

            // Validation spécifique (Prestataire, Type Prestation, etc.)
            $strategy->validateSpecifics($data);

            // Création de la Demande centrale
            // Note: numero_demande est géré dans les stratégies ou on peut le passer ici si on le veut global
            $demandeData = [
                'date_demande' => now(), // Toujours now()
                'motif' => $data['motif'] ?? null,
                'statut' => $data['statut'] ?? 'en_attente',
                'id_type_demande' => $data['id_type_demande'],
                'id_salarie' => $data['id_salarie'],
                'id_ayant_droit' => $data['id_ayant_droit'] ?? null,
            ];

            // Assignation des nouveaux Foreign Keys
            if ($strategyCode === 'BC') {
                $demandeData['id_pharmacie'] = $data['id_pharmacie'];
                $demandeData['numero_demande'] = Demande::generateNumber('BC', 'numero_demande');
            } else {
                $demandeData['id_praticien'] = $data['id_praticien'];
                $demandeData['id_type_prestation'] = $data['id_type_prestation'] ?? null;
                $demandeData['numero_demande'] = Demande::generateNumber($strategyCode, 'numero_demande');
            }

            $demande = Demande::create($demandeData);

            // Traitement spécifique
            $specificModel = $strategy->process($demande, $data);

            return [
                'demande' => $demande,
                'specific' => $specificModel
            ];
        });
    }

    private function getStrategyCodeFromLibelle(string $libelle): string
    {
        $libelleLower = strtolower($libelle);
        if (str_contains($libelleLower, 'bon de commande') || str_contains($libelleLower, 'bon commande')) {
            return 'BC';
        }
        if (str_contains($libelleLower, 'feuille de maladie') || str_contains($libelleLower, 'feuille maladie')) {
            return 'FM';
        }
        if (str_contains($libelleLower, 'lettre de garantie') || str_contains($libelleLower, 'lettre garantie')) {
            return 'LG';
        }

        return 'UNKNOWN';
    }
}
