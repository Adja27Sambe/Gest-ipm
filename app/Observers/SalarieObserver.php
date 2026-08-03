<?php

namespace App\Observers;

use App\Models\Salarie;
use App\Models\CarteAssure;
use App\Events\SalarieRadie;

class SalarieObserver
{
    /**
     * Handle the Salarie "creating" event.
     */
    public function creating(Salarie $salarie): void
    {
        if (empty($salarie->matricule)) {
            $entreprise = \App\Models\Entreprise::find($salarie->id_entreprise);
            
            if ($entreprise && !empty($entreprise->code_adherent)) {
                // Supprimer le préfixe ADH ou ADH- s'il existe
                $codeAdherent = preg_replace('/^ADH-?/i', '', $entreprise->code_adherent);
                
                // Récupérer le dernier salarié de cette entreprise pour s'appuyer sur son matricule
                $lastSalarie = \App\Models\Salarie::where('id_entreprise', $salarie->id_entreprise)
                    ->whereNotNull('matricule')
                    ->where('matricule', 'like', $codeAdherent . '%')
                    ->orderByRaw('LENGTH(matricule) DESC')
                    ->orderBy('matricule', 'desc')
                    ->first();

                if ($lastSalarie && $lastSalarie->matricule !== $codeAdherent) {
                    $lastMatricule = $lastSalarie->matricule;
                    $numberPart = substr($lastMatricule, strlen($codeAdherent));
                    
                    if (is_numeric($numberPart)) {
                        $nextNumber = intval($numberPart) + 1;
                        // On garde le format s'il y a des zéros au début (optionnel mais propre)
                        $padLength = strlen($numberPart);
                        if ($padLength > 0 && $numberPart[0] === '0') {
                            $salarie->matricule = $codeAdherent . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                        } else {
                            $salarie->matricule = $codeAdherent . $nextNumber;
                        }
                    } else {
                        // Fallback de sécurité (incrémentation pure PHP sur chaîne ou count)
                        $count = \App\Models\Salarie::where('id_entreprise', $salarie->id_entreprise)->count();
                        $salarie->matricule = $codeAdherent . ($count + 1);
                    }
                } else {
                    // C'est le premier participant pour ce code_adhérent
                    $salarie->matricule = $codeAdherent . '1';
                }
            }
        }
    }

    /**
     * Handle the Salarie "created" event.
     */
    public function created(Salarie $salarie): void
    {
        // Règle métier : création automatique de la CarteAssure via le service
        app(\App\Services\CarteAssureService::class)->creerCarte($salarie);
    }

    /**
     * Handle the Salarie "updated" event.
     */
    public function updated(Salarie $salarie): void
    {
        \Log::info("Salarie updated observer triggered", [
            'statut' => $salarie->statut,
            'wasChanged' => $salarie->wasChanged('statut')
        ]);
        
        // Cascade de statut
        if ($salarie->wasChanged('statut') && $salarie->statut === 'radie') {
            \Log::info("Dispatching SalarieRadie event");
            event(new SalarieRadie($salarie));
        }
    }
}
