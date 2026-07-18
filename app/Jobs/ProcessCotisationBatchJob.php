<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Salarie;
use App\Models\Cotisation;

class ProcessCotisationBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $salarieIds;
    protected $periode;
    protected $tauxCotisation;

    public function __construct(array $salarieIds, $periode, $tauxCotisation = 15)
    {
        $this->salarieIds = $salarieIds;
        $this->periode = $periode;
        $this->tauxCotisation = $tauxCotisation;
    }

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $salaries = Salarie::whereIn('id_salarie', $this->salarieIds)->get();
        $cotisationsToInsert = [];
        $now = now();

        foreach ($salaries as $salarie) {
            // Vérifier si la cotisation n'existe pas déjà pour cette période
            $exists = Cotisation::where('id_salarie', $salarie->id_salarie)
                                ->where('periode', $this->periode)
                                ->exists();

            if (!$exists) {
                // Si le salaire n'est pas rempli, on prend 0
                $salaireBase = $salarie->salaire ?? 0;
                $montant = ($salaireBase * $this->tauxCotisation) / 100;

                $cotisationsToInsert[] = [
                    'id_salarie' => $salarie->id_salarie,
                    'periode' => $this->periode,
                    'salaire_base' => $salaireBase,
                    'taux' => $this->tauxCotisation,
                    'montant' => $montant,
                    'statut' => 'impayee',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (count($cotisationsToInsert) > 0) {
            Cotisation::insert($cotisationsToInsert);
        }
    }
}
