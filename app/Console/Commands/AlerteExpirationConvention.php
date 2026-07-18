<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AlerteExpirationConvention extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conventions:alerter-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alerte les administrateurs des conventions expirant dans 30 jours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limitDate = \Carbon\Carbon::now()->addDays(30)->toDateString();
        $now = \Carbon\Carbon::now()->toDateString();

        $conventions = \App\Models\Convention::with('prestataire')
            ->where('statut', 'active')
            ->whereBetween('date_fin', [$now, $limitDate])
            ->get();

        if ($conventions->isEmpty()) {
            $this->info('Aucune convention n\'expire dans les 30 prochains jours.');
            return;
        }

        // Trouver les utilisateurs ayant le rôle Administrateur (ou une permission spécifique)
        // Pour simplifier, on prend l'admin
        $admins = \App\Models\Utilisateur::where('statut', 'actif')->whereHas('role', function ($q) {
            $q->where('libelle', 'Administrateur');
        })->get();

        foreach ($conventions as $convention) {
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\ConventionExpireeNotification($convention));
        }

        $this->info(count($conventions) . ' alertes d\'expiration envoyées.');
    }
}
