<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prestataire;
use App\Models\Facture;
use App\Models\Prestation;
use Illuminate\Support\Facades\DB;

class GenerateFactureCommand extends Command
{
    protected $signature = 'factures:generer {--mois= : Le mois sous format Y-m} {--prestataire= : ID du prestataire optionnel}';
    protected $description = 'Génère les factures pour les prestations non facturées des prestataires';

    public function handle()
    {
        $mois = $this->option('mois') ?? now()->format('Y-m');
        $prestataireId = $this->option('prestataire');

        $query = Prestataire::query();
        if ($prestataireId) {
            $query->where('id_prestataire', $prestataireId);
        }

        $prestataires = $query->get();
        $facturesGenerees = 0;

        foreach ($prestataires as $prestataire) {
            // Trouver les prestations du mois qui ne sont pas encore liées à une facture
            $prestations = Prestation::where('id_prestataire', $prestataire->id_prestataire)
                ->where('date_prestation', 'like', "$mois-%")
                ->whereDoesntHave('factures')
                ->get();

            if ($prestations->isEmpty()) {
                continue;
            }

            DB::transaction(function () use ($prestataire, $prestations, &$facturesGenerees) {
                // Le montant dû par l'IPM est le montant total moins le reste à charge du bénéficiaire
                $montantIpm = $prestations->sum(function ($p) {
                    return $p->montant - $p->reste_a_charge;
                });

                $facture = Facture::create([
                    'numero_facture' => 'FAC-' . $prestataire->id_prestataire . '-' . date('YmdHis'),
                    'date_facture' => now(),
                    'statut_paiement' => 'en_attente',
                    'montant' => $montantIpm,
                    'id_prestataire' => $prestataire->id_prestataire,
                ]);

                // Attacher les prestations à la facture
                $facture->prestations()->attach($prestations->pluck('id_prestation'));
                $facturesGenerees++;
            });
        }

        $this->info("Opération terminée. $facturesGenerees factures générées pour la période $mois.");
    }
}
