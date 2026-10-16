<?php

namespace App\Observers;

use App\Models\Prestation;

class PrestationObserver
{
    /**
     * Handle the Prestation "creating" event.
     */
    public function creating(Prestation $prestation): void
    {
        if (empty($prestation->date_prestation)) {
            $prestation->date_prestation = \Carbon\Carbon::now()->toDateString();
        }
        $this->calculerResteACharge($prestation);
    }

    /**
     * Handle the Prestation "updating" event.
     */
    public function updating(Prestation $prestation): void
    {
        $this->calculerResteACharge($prestation);
    }

    private function calculerResteACharge(Prestation $prestation): void
    {
        // 1. Prise en charge SOUS COUVERT (100% IPM, 0% participant)
        $estSousCouvert = (bool) ($prestation->sous_couvert ?? false);
        if (!$estSousCouvert && !empty($prestation->id_demande)) {
            $demande = $prestation->demande ?? \App\Models\Demande::find($prestation->id_demande);
            if ($demande && !empty($demande->sous_couvert)) {
                $estSousCouvert = true;
                $prestation->sous_couvert = true;
            }
        }

        if ($estSousCouvert) {
            $prestation->taux_prise_charge = 100.00;
            $prestation->reste_a_charge = 0.00;

            if (is_array($prestation->details_articles) && count($prestation->details_articles) > 0) {
                $nouveauxArticles = [];
                $totalArticles = 0;
                foreach ($prestation->details_articles as $article) {
                    $pu = (float) ($article['montant'] ?? 0);
                    $qte = (float) ($article['quantite'] ?? 1);
                    if ($qte <= 0) $qte = 1;
                    $totalLigne = round($pu * $qte, 2);

                    $article['part_ipm'] = $totalLigne;
                    $article['part_participant'] = 0.00;
                    $article['reste_a_charge'] = 0.00;
                    $article['taux'] = 100.00;

                    $nouveauxArticles[] = $article;
                    $totalArticles += $totalLigne;
                }
                $prestation->details_articles = $nouveauxArticles;
                if ($totalArticles > 0) {
                    $prestation->montant = $totalArticles;
                }
            }
            return;
        }

        // Pré-charger tous les paramètres de couverture pour lookup rapide
        $parametresByType = \App\Models\ParametreCouverture::all()->keyBy('id_type_prestation');
        $parametre = $prestation->id_type_prestation ? ($parametresByType[$prestation->id_type_prestation] ?? null) : null;
        
        // Si des articles sont détaillés
        if (is_array($prestation->details_articles) && count($prestation->details_articles) > 0) {
            $totalPriseEnCharge = 0;
            $totalResteACharge = 0;
            $nouveauxArticles = [];
            
            foreach ($prestation->details_articles as $article) {
                $montantLigne = (float) ($article['montant'] ?? 0);
                $quantite = (float) ($article['quantite'] ?? 1);
                if ($quantite <= 0) $quantite = 1;
                $totalLigne = round($montantLigne * $quantite, 2);
                
                $partIpm = 0.00;
                $partParticipant = $totalLigne;

                $estExclusion = (bool) ($article['est_exclusion'] ?? false);
                $idTypeLigne = !empty($article['id_type_prestation']) ? (int)$article['id_type_prestation'] : (int)$prestation->id_type_prestation;
                $paramLigne = $idTypeLigne ? ($parametresByType[$idTypeLigne] ?? null) : $parametre;
                
                $typeCalculLigne = $article['type_calcul'] ?? $paramLigne?->type_calcul ?? 'POURCENTAGE';
                
                if ($estExclusion || $typeCalculLigne === 'EXCLUSION' || $typeCalculLigne === '100_PERCENT_PARTICIPANT' || ($paramLigne && $paramLigne->est_exclusion)) {
                    $partIpm = 0.00;
                    $partParticipant = $totalLigne;
                } elseif ($typeCalculLigne === 'SOUS_COUVERT') {
                    $partIpm = $totalLigne;
                    $partParticipant = 0.00;
                } else {
                    // Déterminer si l'acte est soumis à un montant référentiel avec Part IPM fixe
                    $isMontantRefAct = ($typeCalculLigne === 'MONTANT_REFERENTIEL') 
                        || ($paramLigne && $paramLigne->type_calcul === 'MONTANT_REFERENTIEL')
                        || (isset($article['part_ipm_reference']) && is_numeric($article['part_ipm_reference']) && (float)$article['part_ipm_reference'] > 0);

                    $partIpmRef = null;
                    if ($isMontantRefAct) {
                        if (isset($article['part_ipm_reference']) && is_numeric($article['part_ipm_reference']) && (float)$article['part_ipm_reference'] > 0) {
                            $partIpmRef = (float)$article['part_ipm_reference'];
                        } elseif ($paramLigne && $paramLigne->part_ipm_reference !== null && (float)$paramLigne->part_ipm_reference > 0) {
                            $partIpmRef = (float)$paramLigne->part_ipm_reference;
                        } elseif ($paramLigne && $paramLigne->montant_referentiel > 0 && $paramLigne->taux_prise_charge > 0) {
                            $partIpmRef = round(($paramLigne->montant_referentiel * $paramLigne->taux_prise_charge) / 100, 2);
                        }
                    }

                    // Règle IPM : Part IPM fixe de référence, tout le reste au participant
                    // Sécurité : si montant facturé < Part IPM de référence -> Part IPM = montant facturé, Part participant = 0
                    if ($partIpmRef !== null && $partIpmRef > 0) {
                        $partIpmRefTotal = round($partIpmRef * $quantite, 2);
                        if ($totalLigne < $partIpmRefTotal) {
                            $partIpm = $totalLigne;
                            $partParticipant = 0.00;
                        } else {
                            $partIpm = $partIpmRefTotal;
                            $partParticipant = round($totalLigne - $partIpm, 2);
                        }
                        $article['part_ipm_reference'] = $partIpmRef;
                    } else {
                        switch ($typeCalculLigne) {
                            case 'TARIF_JOURNALIER':
                                $tarifPlafond = (float) ($article['tarif_journalier'] ?? $paramLigne?->tarif_journalier ?? 20000);
                                $plafondTotal = $tarifPlafond * $quantite;
                                if ($totalLigne <= $plafondTotal) {
                                    $partIpm = $totalLigne;
                                    $partParticipant = 0.00;
                                } else {
                                    $partIpm = $plafondTotal;
                                    $partParticipant = max(0, $totalLigne - $partIpm);
                                }
                                break;
                                
                            case 'PLAFOND':
                                $plafond = (float) ($article['montant_plafond'] ?? $paramLigne?->montant_plafond ?? 30000);
                                if ($totalLigne <= $plafond) {
                                    $partIpm = $totalLigne;
                                    $partParticipant = 0.00;
                                } else {
                                    $partIpm = $plafond;
                                    $partParticipant = max(0, $totalLigne - $partIpm);
                                }
                                break;
                                
                            case 'FORFAIT':
                                $forfait = (float) ($article['montant_forfait'] ?? $paramLigne?->montant_forfait ?? 50000);
                                if ($totalLigne <= $forfait) {
                                    $partIpm = $totalLigne;
                                    $partParticipant = 0.00;
                                } else {
                                    $partIpm = $forfait;
                                    $partParticipant = max(0, $totalLigne - $partIpm);
                                }
                                break;
                                
                            case 'POURCENTAGE':
                            default:
                                $tauxIpm = isset($article['taux']) ? (float)$article['taux'] : (float)($prestation->taux_prise_charge ?? $paramLigne?->taux_prise_charge ?? 0);
                                $partIpm = round(($totalLigne * $tauxIpm) / 100, 2);
                                $partParticipant = max(0, $totalLigne - $partIpm);
                                break;
                        }
                    }
                }

                $article['part_ipm'] = $partIpm;
                $article['part_participant'] = $partParticipant;
                $article['reste_a_charge'] = $partParticipant;
                
                $totalPriseEnCharge += $partIpm;
                $totalResteACharge += $partParticipant;
                
                $nouveauxArticles[] = $article;
            }
            
            $prestation->details_articles = $nouveauxArticles;
            $prestation->montant = $totalPriseEnCharge + $totalResteACharge;
            $prestation->reste_a_charge = $totalResteACharge;
            if ($prestation->montant > 0) {
                $prestation->taux_prise_charge = round(($totalPriseEnCharge / $prestation->montant) * 100, 2);
            }
            
        } else {
            // Pas de détails d'articles, on applique la règle globale au montant global
            $total = (float) ($prestation->montant ?? 0);
            $typeCalcul = $parametre?->type_calcul ?? 'POURCENTAGE';
            $partIpm = 0.00;
            $partParticipant = $total;
            
            if (($parametre && $parametre->est_exclusion) || $typeCalcul === 'EXCLUSION' || $typeCalcul === '100_PERCENT_PARTICIPANT') {
                $partIpm = 0.00;
                $partParticipant = $total;
            } elseif ($typeCalcul === 'SOUS_COUVERT') {
                $partIpm = $total;
                $partParticipant = 0.00;
            } else {
                // Déterminer la part IPM de référence pour le paramètre
                $isMontantRefAct = ($typeCalcul === 'MONTANT_REFERENTIEL')
                    || ($parametre && $parametre->type_calcul === 'MONTANT_REFERENTIEL');

                $partIpmRef = null;
                if ($isMontantRefAct && $parametre) {
                    if ($parametre->part_ipm_reference !== null && (float)$parametre->part_ipm_reference > 0) {
                        $partIpmRef = (float)$parametre->part_ipm_reference;
                    } elseif ($parametre->montant_referentiel > 0 && $parametre->taux_prise_charge > 0) {
                        $partIpmRef = round(($parametre->montant_referentiel * $parametre->taux_prise_charge) / 100, 2);
                    }
                }

                if ($partIpmRef !== null && $partIpmRef > 0) {
                    if ($total < $partIpmRef) {
                        $partIpm = $total;
                        $partParticipant = 0.00;
                    } else {
                        $partIpm = $partIpmRef;
                        $partParticipant = round($total - $partIpm, 2);
                    }
                } else {
                    switch ($typeCalcul) {
                        case 'TARIF_JOURNALIER': 
                            $tarifPlafond = (float) ($parametre?->tarif_journalier ?? 20000);
                            if ($total <= $tarifPlafond) {
                                $partIpm = $total;
                                $partParticipant = 0.00;
                            } else {
                                $partIpm = $tarifPlafond;
                                $partParticipant = max(0, $total - $partIpm);
                            }
                            break;
                            
                        case 'PLAFOND':
                            $plafond = (float) ($parametre?->montant_plafond ?? 30000);
                            if ($total <= $plafond) {
                                $partIpm = $total;
                                $partParticipant = 0.00;
                            } else {
                                $partIpm = $plafond;
                                $partParticipant = max(0, $total - $partIpm);
                            }
                            break;
                            
                        case 'FORFAIT':
                            $forfait = (float) ($parametre?->montant_forfait ?? 50000);
                            if ($total <= $forfait) {
                                $partIpm = $total;
                                $partParticipant = 0.00;
                            } else {
                                $partIpm = $forfait;
                                $partParticipant = max(0, $total - $partIpm);
                            }
                            break;
                            
                        case 'POURCENTAGE':
                        default:
                            $tauxIpm = (float) ($prestation->taux_prise_charge ?? $parametre?->taux_prise_charge ?? 0);
                            $partIpm = round(($total * $tauxIpm) / 100, 2);
                            $partParticipant = max(0, $total - $partIpm);
                            break;
                    }
                }
            }
            
            $prestation->reste_a_charge = $partParticipant;
            if ($total > 0 && ($typeCalcul !== 'POURCENTAGE' || $prestation->taux_prise_charge === null)) {
                $prestation->taux_prise_charge = round(($partIpm / $total) * 100, 2);
            }
        }
    }
}
