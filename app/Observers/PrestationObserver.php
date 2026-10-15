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

        $parametre = \App\Models\ParametreCouverture::where('id_type_prestation', $prestation->id_type_prestation)->first();
        
        if (!$parametre) {
            // fallback to basic calculation
            if ($prestation->montant !== null && $prestation->taux_prise_charge !== null) {
                $priseEnCharge = ($prestation->montant * $prestation->taux_prise_charge) / 100;
                $prestation->reste_a_charge = max(0, $prestation->montant - $priseEnCharge);
            }
            return;
        }

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
                
                $partIpm = 0;
                $partParticipant = $totalLigne;

                $estExclusion = (bool) ($article['est_exclusion'] ?? false);
                $typeCalculLigne = $article['type_calcul'] ?? $parametre->type_calcul ?? 'POURCENTAGE';
                
                if ($estExclusion || $typeCalculLigne === 'EXCLUSION' || $typeCalculLigne === '100_PERCENT_PARTICIPANT') {
                    $partIpm = 0;
                    $partParticipant = $totalLigne;
                } elseif ($typeCalculLigne === 'SOUS_COUVERT') {
                    $partIpm = $totalLigne;
                    $partParticipant = 0;
                } else {
                    switch ($typeCalculLigne) {
                        case 'MONTANT_REFERENTIEL':
                            $ref = (float) ($article['montant_referentiel'] ?? $parametre->montant_referentiel ?? $parametre->ticket_moderateur ?? 0);
                            if ($ref > 0) {
                                if ($totalLigne <= $ref) {
                                    $partIpm = $totalLigne;
                                    $partParticipant = 0;
                                } else {
                                    $partIpm = $ref;
                                    $partParticipant = max(0, $totalLigne - $partIpm);
                                }
                            } else {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            }
                            break;

                        case 'TARIF_JOURNALIER':
                            // Ex: chambre d'hospitalisation (ex: 20 000 FCFA/jour)
                            $tarifPlafond = (float) ($article['tarif_journalier'] ?? $parametre->tarif_journalier ?? 20000);
                            $plafondTotal = $tarifPlafond * $quantite;
                            if ($totalLigne <= $plafondTotal) {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $plafondTotal;
                                $partParticipant = max(0, $totalLigne - $partIpm);
                            }
                            break;
                            
                        case 'PLAFOND':
                            // Ex: optique médicale (ex: 30 000 FCFA/paire)
                            $plafond = (float) ($article['montant_plafond'] ?? $parametre->montant_plafond ?? 30000);
                            if ($totalLigne <= $plafond) {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $plafond;
                                $partParticipant = max(0, $totalLigne - $partIpm);
                            }
                            break;
                            
                        case 'FORFAIT':
                            // Ex: forfait maternité sans complication (ex: 50 000 FCFA)
                            $forfait = (float) ($article['montant_forfait'] ?? $parametre->montant_forfait ?? 50000);
                            if ($totalLigne <= $forfait) {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $forfait;
                                $partParticipant = max(0, $totalLigne - $partIpm);
                            }
                            break;
                            
                        case 'POURCENTAGE':
                        default:
                            $tauxIpm = isset($article['taux']) ? (float)$article['taux'] : (float)($parametre->taux_prise_charge ?? $prestation->taux_prise_charge ?? 0);
                            $partIpm = round(($totalLigne * $tauxIpm) / 100, 2);
                            $partParticipant = max(0, $totalLigne - $partIpm);
                            break;
                    }
                }

                $article['part_ipm'] = $partIpm;
                $article['part_participant'] = $partParticipant;
                
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
            $typeCalcul = $parametre->type_calcul ?? 'POURCENTAGE';
            $partIpm = 0;
            $partParticipant = $total;
            
            if ($parametre->est_exclusion || $typeCalcul === 'EXCLUSION' || $typeCalcul === '100_PERCENT_PARTICIPANT') {
                $partIpm = 0;
                $partParticipant = $total;
            } elseif ($typeCalcul === 'SOUS_COUVERT') {
                $partIpm = $total;
                $partParticipant = 0;
            } else {
                switch ($typeCalcul) {
                    case 'MONTANT_REFERENTIEL':
                        $ref = (float) ($parametre->montant_referentiel ?? $parametre->ticket_moderateur ?? 0);
                        if ($ref > 0) {
                            if ($total <= $ref) {
                                $partIpm = $total;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $ref;
                                $partParticipant = max(0, $total - $partIpm);
                            }
                        } else {
                            $partIpm = $total;
                            $partParticipant = 0;
                        }
                        break;

                    case 'TARIF_JOURNALIER': 
                        $tarifPlafond = (float) ($parametre->tarif_journalier ?? 20000);
                        if ($total <= $tarifPlafond) {
                            $partIpm = $total;
                            $partParticipant = 0;
                        } else {
                            $partIpm = $tarifPlafond;
                            $partParticipant = max(0, $total - $partIpm);
                        }
                        break;
                        
                    case 'PLAFOND':
                        $plafond = (float) ($parametre->montant_plafond ?? 30000);
                        if ($total <= $plafond) {
                            $partIpm = $total;
                            $partParticipant = 0;
                        } else {
                            $partIpm = $plafond;
                            $partParticipant = max(0, $total - $partIpm);
                        }
                        break;
                        
                    case 'FORFAIT':
                        $forfait = (float) ($parametre->montant_forfait ?? 50000);
                        if ($total <= $forfait) {
                            $partIpm = $total;
                            $partParticipant = 0;
                        } else {
                            $partIpm = $forfait;
                            $partParticipant = max(0, $total - $partIpm);
                        }
                        break;
                        
                    case 'POURCENTAGE':
                    default:
                        $tauxIpm = (float) ($parametre->taux_prise_charge ?? $prestation->taux_prise_charge ?? 0);
                        $partIpm = round(($total * $tauxIpm) / 100, 2);
                        $partParticipant = max(0, $total - $partIpm);
                        break;
                }
            }
            
            $prestation->reste_a_charge = $partParticipant;
            if ($total > 0 && ($typeCalcul !== 'POURCENTAGE' || $prestation->taux_prise_charge === null)) {
                $prestation->taux_prise_charge = round(($partIpm / $total) * 100, 2);
            }
        }
    }
}
