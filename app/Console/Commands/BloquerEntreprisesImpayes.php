<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entreprise;
use App\Models\Cotisation;
use App\Models\AyantDroit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BloquerEntreprisesImpayes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'entreprises:bloquer-impayes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bloque les entreprises ayant des cotisations impayées après le 10 du mois et réactive celles qui ont régularisé.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des cotisations impayées...');

        $now = Carbon::now();

        // Si la date du mois en cours dépasse le 10, alors la cotisation du mois en cours devient exigible et en retard si non payée
        $isLateInCurrentMonth = $now->day > 10;
        
        $entreprises = Entreprise::with('salaries')->get();
        $countBlocked = 0;
        $countReactivated = 0;

        foreach ($entreprises as $entreprise) {
            // Rechercher s'il y a un retard
            $hasUnpaidLateCotisations = Cotisation::where('ADCLEUNIK', $entreprise->id)
                ->whereRaw('MTREGLE < MTCOTISE')
                ->where(function($query) use ($now, $isLateInCurrentMonth) {
                    // Les années précédentes
                    $query->where('ANNEECOTISE', '<', $now->year)
                          // L'année en cours, mais les mois précédents
                          ->orWhere(function($q) use ($now) {
                              $q->where('ANNEECOTISE', $now->year)
                                ->where('MOISCOTISE', '<', $now->month);
                          });
                          
                    // Le mois en cours si on est après le 10
                    if ($isLateInCurrentMonth) {
                        $query->orWhere(function($q) use ($now) {
                            $q->where('ANNEECOTISE', $now->year)
                              ->where('MOISCOTISE', '=', $now->month);
                        });
                    }
                })->exists();

            if ($hasUnpaidLateCotisations) {
                // On bloque l'entreprise si elle n'est pas déjà suspendue (2) ou radiée (0)
                // ADACTIF = 1 (actif)
                if ($entreprise->ADACTIF == 1) {
                    DB::transaction(function() use ($entreprise) {
                        $entreprise->ADACTIF = 2; // Suspendu
                        $entreprise->save();

                        // Bloquer les salariés (on ne bloque que ceux qui étaient actifs pour pouvoir les réactiver ensuite, mais dans l'idée on bloque tout le monde)
                        foreach ($entreprise->salaries as $salarie) {
                            if ($salarie->PARACTIF == 1) {
                                $salarie->PARACTIF = 0;
                                $salarie->save();
                            }
                            // Bloquer les ayants droit
                            AyantDroit::where('id_salarie', $salarie->id)->update(['statut' => 0]);
                        }
                    });
                    $countBlocked++;
                    $this->line("Entreprise bloquée : {$entreprise->raison_sociale} (ID: {$entreprise->id})");
                }
            } else {
                // Si l'entreprise n'a pas d'impayé en retard, et qu'elle était suspendue pour impayé (2)
                if ($entreprise->ADACTIF == 2) {
                    DB::transaction(function() use ($entreprise) {
                        $entreprise->ADACTIF = 1; // Actif
                        $entreprise->save();

                        // On réactive uniquement les salariés qui ne sont pas marqués comme partis (DEPART=1)
                        $salaries = $entreprise->salaries()->where(function($q) {
                            $q->where('DEPART', 0)->orWhereNull('DEPART');
                        })->get();

                        foreach ($salaries as $salarie) {
                            $salarie->PARACTIF = 1;
                            $salarie->save();

                            // Réactiver les ayants droit
                            AyantDroit::where('id_salarie', $salarie->id)->update(['statut' => 1]);
                        }
                    });
                    $countReactivated++;
                    $this->line("Entreprise réactivée : {$entreprise->raison_sociale} (ID: {$entreprise->id})");
                }
            }
        }

        $this->info("Opération terminée. {$countBlocked} bloquées, {$countReactivated} réactivées.");
    }
}
