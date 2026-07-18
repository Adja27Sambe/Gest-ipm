<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Cotisation;
use App\Models\Relance;
use App\Models\Entreprise;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RelancerCotisationsImpayees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // On considère une cotisation en retard si l'échéance (10 jours après la fin du mois) est dépassée.
        $now = now();
        
        $impayees = Cotisation::with('salarie')
            ->where('statut', 'impayee')
            ->get()
            ->filter(function ($cotisation) use ($now) {
                try {
                    // Si periode est "2023-10", l'échéance est le 10 novembre 2023.
                    $dateEcheance = Carbon::createFromFormat('Y-m', $cotisation->periode)
                        ->addMonth()
                        ->addDays(10);
                    return $now->greaterThan($dateEcheance);
                } catch (\Exception $e) {
                    return false;
                }
            });

        // Group by id_entreprise
        $groupedByEntreprise = $impayees->groupBy(function ($cotisation) {
            return $cotisation->salarie->id_entreprise ?? null;
        });

        foreach ($groupedByEntreprise as $idEntreprise => $cotisations) {
            if (!$idEntreprise) continue;

            $totalDu = $cotisations->sum('montant');
            $periodes = $cotisations->pluck('periode')->unique()->implode(', ');

            // Vérifier s'il y a déjà eu une relance récente (ex: dans les 7 derniers jours)
            $relanceRecente = Relance::where('id_entreprise', $idEntreprise)
                ->where('date_relance', '>=', now()->subDays(7))
                ->exists();

            if (!$relanceRecente) {
                $entreprise = Entreprise::find($idEntreprise);
                
                Relance::create([
                    'date_relance' => $now,
                    'niveau_relance' => 1,
                    'commentaire' => "Relance automatique. Périodes : $periodes. Total dû : $totalDu FCFA.",
                    'id_entreprise' => $idEntreprise
                ]);

                // Notification métier fictive ou log
                Log::info("Relance automatique créée pour l'entreprise " . ($entreprise->raison_sociale ?? 'Inconnue') . " (ID: $idEntreprise) - Dû : $totalDu FCFA.");
            }
        }
    }
}
