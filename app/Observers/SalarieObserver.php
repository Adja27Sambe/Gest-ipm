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
            
            if ($entreprise && !empty($entreprise->code_adherent)) {
                $codeAdherent = preg_replace('/^(ADH|ENT|MAT)-?/i', '', $entreprise->code_adherent);
                $originalCode = $entreprise->code_adherent;
                
                $maxNumber = \App\Models\Salarie::where('IDADHERANT', $entrepriseId)
                    ->whereNotNull('MATRICULE')
                    ->pluck('MATRICULE')
                    ->map(function ($m) use ($codeAdherent, $originalCode) {
                        $compteurStr = preg_replace('/^(' . preg_quote($originalCode, '/') . '|' . preg_quote($codeAdherent, '/') . ')/i', '', $m);
                        return intval(preg_replace('/[^0-9]/', '', $compteurStr));
                    })
                    ->max() ?? 0;
                    
                $salarie->matricule = $codeAdherent . str_pad($maxNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $statement = \Illuminate\Support\Facades\DB::select("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'PARTICIPANT'");
                $nextId = $statement[0]->AUTO_INCREMENT ?? (\App\Models\Salarie::max('IDPARTICIPANT') + 1);
                $salarie->matricule = str_pad($nextId, 4, '0', STR_PAD_LEFT);
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
