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
        $parametre = \App\Models\ParametreCouverture::where('id_type_prestation', $prestation->id_type_prestation)->first();
        
        if (!$parametre) {
            // fallback to basic calculation
            if ($prestation->montant !== null && $prestation->taux_prise_charge !== null) {
                $priseEnCharge = ($prestation->montant * $prestation->taux_prise_charge) / 100;
                $prestation->reste_a_charge = $prestation->montant - $priseEnCharge;
            }
            return;
        }

        // Si des articles sont détaillés
        if (is_array($prestation->details_articles) && count($prestation->details_articles) > 0) {
            $totalPriseEnCharge = 0;
            $totalResteACharge = 0;
            $nouveauxArticles = [];
            
            foreach ($prestation->details_articles as $article) {
                $montantLigne = $article['montant'] ?? 0;
                $quantite = $article['quantite'] ?? 1;
                $totalLigne = $montantLigne * $quantite;
                
                $partIpm = 0;
                $partParticipant = $totalLigne;

                $estExclusion = $article['est_exclusion'] ?? false;
                // Si la ligne spécifie un type de calcul, on l'utilise. Sinon on utilise celui du paramètre global.
                $typeCalculLigne = $article['type_calcul'] ?? $parametre->type_calcul ?? 'POURCENTAGE';
                
                if ($estExclusion) {
                    $partIpm = 0;
                    $partParticipant = $totalLigne;
                } else {
                    switch ($typeCalculLigne) {
                        case 'TARIF_JOURNALIER':
                            // Ex: chambre d'hospitalisation
                            $tarifPlafond = $article['tarif_journalier'] ?? $parametre->tarif_journalier ?? 20000;
                            if ($montantLigne <= $tarifPlafond) {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $tarifPlafond * $quantite;
                                $partParticipant = $totalLigne - $partIpm;
                            }
                            break;
                            
                        case 'PLAFOND':
                            // Ex: optique médicale
                            $plafond = $article['montant_plafond'] ?? $parametre->montant_plafond ?? 30000;
                            if ($totalLigne <= $plafond) {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $plafond;
                                $partParticipant = $totalLigne - $partIpm;
                            }
                            break;
                            
                        case 'FORFAIT':
                            // Ex: maternité sans complication
                            $forfait = $article['montant_forfait'] ?? $parametre->montant_forfait ?? 50000;
                            if ($totalLigne <= $forfait) {
                                $partIpm = $totalLigne;
                                $partParticipant = 0;
                            } else {
                                $partIpm = $forfait;
                                $partParticipant = $totalLigne - $partIpm;
                            }
                            break;
                            
                        case '100_PERCENT_PARTICIPANT':
                        case 'EXCLUSION':
                            $partIpm = 0;
                            $partParticipant = $totalLigne;
                            break;
                            
                        case 'POURCENTAGE':
                        default:
                            $tauxIpm = $article['taux_prise_charge'] ?? $parametre->taux_prise_charge ?? $prestation->taux_prise_charge ?? 0;
                            $partIpm = ($totalLigne * $tauxIpm) / 100;
                            if (!empty($parametre->plafond_par_acte) && $partIpm > $parametre->plafond_par_acte) {
                                $partIpm = $parametre->plafond_par_acte;
                            }
                            $partParticipant = $totalLigne - $partIpm;
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
            // On met à jour le montant total pour qu'il corresponde à la somme des articles
            $prestation->montant = $totalPriseEnCharge + $totalResteACharge;
            $prestation->reste_a_charge = $totalResteACharge;
            // Calcul d'un taux moyen indicatif si besoin
            if ($prestation->montant > 0) {
                $prestation->taux_prise_charge = ($totalPriseEnCharge / $prestation->montant) * 100;
            }
            
        } else {
            // Pas de détails d'articles, on applique la règle globale au montant global
            $total = $prestation->montant ?? 0;
            $typeCalcul = $parametre->type_calcul ?? 'POURCENTAGE';
            $partIpm = 0;
            $partParticipant = $total;
            
            if ($parametre->est_exclusion) {
                $partIpm = 0;
                $partParticipant = $total;
            } else {
                switch ($typeCalcul) {
                    case 'TARIF_JOURNALIER': 
                        $tarifPlafond = $parametre->tarif_journalier ?? 20000;
                        if ($total <= $tarifPlafond) {
                            $partIpm = $total;
                            $partParticipant = 0;
                        } else {
                            $partIpm = $tarifPlafond; // Sans quantité, on suppose 1 jour si non fourni en détail
                            $partParticipant = $total - $partIpm;
                        }
                        break;
                        
                    case 'PLAFOND':
                        $plafond = $parametre->montant_plafond ?? 30000;
                        if ($total <= $plafond) {
                            $partIpm = $total;
                            $partParticipant = 0;
                        } else {
                            $partIpm = $plafond;
                            $partParticipant = $total - $partIpm;
                        }
                        break;
                        
                    case 'FORFAIT':
                        $forfait = $parametre->montant_forfait ?? 50000;
                        if ($total <= $forfait) {
                            $partIpm = $total;
                            $partParticipant = 0;
                        } else {
                            $partIpm = $forfait;
                            $partParticipant = $total - $partIpm;
                        }
                        break;
                        
                    case '100_PERCENT_PARTICIPANT':
                    case 'EXCLUSION':
                        $partIpm = 0;
                        $partParticipant = $total;
                        break;
                        
                    case 'POURCENTAGE':
                    default:
                        $tauxIpm = $parametre->taux_prise_charge ?? $prestation->taux_prise_charge ?? 0;
                        $partIpm = ($total * $tauxIpm) / 100;
                        // Application de l'ancien plafond par acte si défini
                        if (!empty($parametre->plafond_par_acte) && $partIpm > $parametre->plafond_par_acte) {
                            $partIpm = $parametre->plafond_par_acte;
                        }
                        $partParticipant = $total - $partIpm;
                        break;
                }
            }
            
            $prestation->reste_a_charge = $partParticipant;
            if ($total > 0 && ($typeCalcul !== 'POURCENTAGE' || $prestation->taux_prise_charge === null)) {
                $prestation->taux_prise_charge = ($partIpm / $total) * 100;
            }
        }
    }
}
