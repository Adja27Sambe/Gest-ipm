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
        if (empty($salarie->code_participant) && empty($salarie->CODE_PARTICIPANT)) {
            $maxCode = \App\Models\Salarie::max('CODE_PARTICIPANT') ?? 0;
            $salarie->CODE_PARTICIPANT = $maxCode + 1;
        }

        if (empty($salarie->matricule)) {
            $entrepriseId = $salarie->id_entreprise ?? $salarie->IDADHERANT;
            $entreprise = $entrepriseId ? \App\Models\Entreprise::find($entrepriseId) : null;
            
            $prefix = 'MAT-';
            if ($entreprise && !empty($entreprise->code_adherent)) {
                $stripped = preg_replace('/^(ADH|ENT)-?/i', '', $entreprise->code_adherent);
                $prefix = !empty($stripped) ? $stripped : $entreprise->code_adherent;
            }

            // Chercher le dernier salarié pour cette entreprise
            $query = \App\Models\Salarie::query();
            if ($entrepriseId) {
                $query->where('IDADHERANT', $entrepriseId);
            }
            $lastSalarie = $query->whereNotNull('MATRICULE')
                ->where('MATRICULE', 'like', $prefix . '%')
                ->orderByRaw('LENGTH(MATRICULE) DESC')
                ->orderBy('MATRICULE', 'desc')
                ->first();

            if ($lastSalarie && $lastSalarie->matricule !== $prefix) {
                $lastMatricule = $lastSalarie->matricule;
                $numberPart = substr($lastMatricule, strlen($prefix));
                
                if (is_numeric($numberPart)) {
                    $nextNumber = intval($numberPart) + 1;
                    $padLength = strlen($numberPart);
                    if ($padLength > 0 && $numberPart[0] === '0') {
                        $salarie->matricule = $prefix . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                    } else {
                        $salarie->matricule = $prefix . $nextNumber;
                    }
                } else {
                    $count = $entrepriseId ? \App\Models\Salarie::where('IDADHERANT', $entrepriseId)->count() : \App\Models\Salarie::count();
                    $salarie->matricule = $prefix . ($count + 1);
                }
            } else {
                $salarie->matricule = $prefix . '0001';
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
        if (($salarie->wasChanged('statut') || $salarie->wasChanged('PARACTIF')) && $salarie->statut === 'radie') {
            \Log::info("Dispatching SalarieRadie event");
            event(new SalarieRadie($salarie));
        }
    }
}
