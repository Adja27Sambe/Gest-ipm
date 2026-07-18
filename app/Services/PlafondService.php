<?php

namespace App\Services;

use App\Models\Prestation;
use App\Models\ParametreCouverture;
use App\Exceptions\PlafondDepasseException;
use Illuminate\Support\Facades\DB;

class PlafondService
{
    /**
     * Vérifie si la prestation dépasse les plafonds
     *
     * @param float $montant
     * @param int $idTypePrestation
     * @param int $idDemande
     * @return void
     * @throws PlafondDepasseException
     */
    public function checkPlafonds(float $montant, int $idTypePrestation, int $idDemande): void
    {
        // 1. Récupération des paramètres de couverture pour ce type de prestation
        $parametre = ParametreCouverture::where('id_type_prestation', $idTypePrestation)->first();

        if (!$parametre) {
            // Blocage absolu : impossible d'enregistrer une prestation sans paramètre de couverture
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id_type_prestation' => "Impossible d'enregistrer la prestation : aucun paramètre de couverture n'est configuré pour ce type de prestation."
            ]);
        }

        // Vérification plafond par acte
        if ($parametre->plafond_par_acte !== null && $montant > $parametre->plafond_par_acte) {
            $depassement = $montant - $parametre->plafond_par_acte;
            throw new PlafondDepasseException(
                "Le montant de l'acte ($montant) dépasse le plafond autorisé par acte ({$parametre->plafond_par_acte}).",
                $depassement
            );
        }

        // 2. Vérification du plafond annuel
        if ($parametre->plafond_annuel !== null) {
            // Trouver le bénéficiaire via la demande
            $demande = \App\Models\Demande::find($idDemande);
            if (!$demande) return;

            // Cumul des prestations de l'année en cours pour le même type et le même bénéficiaire
            $anneeEnCours = date('Y');

            // On construit la requête pour trouver toutes les prestations associées au même bénéficiaire
            // via les demandes de ce bénéficiaire
            $cumulAnnuel = Prestation::where('id_type_prestation', $idTypePrestation)
                ->whereYear('date_prestation', $anneeEnCours)
                ->whereHas('demande', function ($q) use ($demande) {
                    if ($demande->id_ayant_droit) {
                        $q->where('id_ayant_droit', $demande->id_ayant_droit);
                    } else {
                        $q->where('id_salarie', $demande->id_salarie)
                          ->whereNull('id_ayant_droit');
                    }
                })
                ->sum('montant');

            $nouveauCumul = $cumulAnnuel + $montant;

            if ($nouveauCumul > $parametre->plafond_annuel) {
                $depassement = $nouveauCumul - $parametre->plafond_annuel;
                throw new PlafondDepasseException(
                    "Le plafond annuel ({$parametre->plafond_annuel}) pour ce type de soin est dépassé. Cumul actuel: $cumulAnnuel. Nouveau cumul: $nouveauCumul.",
                    $depassement
                );
            }
        }
    }
}
