<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Salarie;
use App\Jobs\ProcessCotisationBatchJob;
use Illuminate\Support\Facades\Bus;

class GenererCotisationsMensuelles extends Command
{
    protected $signature = 'cotisations:generer-mensuelles {--periode= : La période au format Y-m} {--taux=15 : Taux en %}';
    protected $description = 'Génère les cotisations mensuelles pour tous les salariés actifs via des jobs en batch';

    public function handle()
    {
        $periode = $this->option('periode') ?? now()->format('Y-m');
        $taux = (float) $this->option('taux');

        $this->info("Début de la génération des cotisations pour la période : $periode (Taux: $taux%)");

        $salarieIds = Salarie::where('statut', 'Actif')->pluck('id_salarie')->toArray();

        if (empty($salarieIds)) {
            $this->warn('Aucun salarié actif trouvé.');
            return 0;
        }

        $chunks = array_chunk($salarieIds, 100);
        $jobs = [];

        foreach ($chunks as $chunk) {
            $jobs[] = new ProcessCotisationBatchJob($chunk, $periode, $taux);
        }

        try {
            $batch = Bus::batch($jobs)->name('Generation Cotisations ' . $periode)->dispatch();
            $this->info("Batch (ID: {$batch->id}) lancé avec " . count($jobs) . " jobs.");
        } catch (\Throwable $e) {
            // Fallback si la table job_batches n'existe pas, dispatch normal
            $this->warn("Bus::batch a échoué (table job_batches manquante ?). Dispatch classique des jobs en cours...");
            foreach ($jobs as $job) {
                dispatch($job);
            }
            $this->info("Jobs dispatchés sans batching.");
        }

        return 0;
    }
}
