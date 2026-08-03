<?php

namespace App\Observers;

use App\Models\Entreprise;
use App\Models\HistoriqueMouvement;

class EntrepriseObserver
{
    /**
     * Handle the Entreprise "creating" event.
     */
    public function creating(Entreprise $entreprise): void
    {
        if (empty($entreprise->code_adherent)) {
            $entreprise->code_adherent = static::generateCodeAdherent();
        }
    }

    /**
     * Handle the Entreprise "updated" event.
     */
    public function updated(Entreprise $entreprise): void
    {
        if ($entreprise->wasChanged('statut')) {
            HistoriqueMouvement::create([
                'date_heure' => now(),
                'module' => 'Entreprise',
                'action' => 'Changement de statut',
                'description' => "Le statut de l'entreprise {$entreprise->raison_sociale} est passé de {$entreprise->getOriginal('statut')} à {$entreprise->statut}.",
                'adresse_ip' => request()->ip(),
                'ancienne_valeur' => $entreprise->getOriginal('statut'),
                'nouvelle_valeur' => $entreprise->statut,
                'id_utilisateur' => null, // Utilisateur authentifié à implémenter plus tard (ex: auth()->id())
            ]);
        }
    }

    /**
     * Génère un code adhérent unique séquentiel (ex: ADH001, ADH002).
     */
    public static function generateCodeAdherent(): string
    {
        $lastEntreprise = Entreprise::whereNotNull('code_adherent')
            ->where('code_adherent', 'like', 'ADH%')
            ->orderByRaw('LENGTH(code_adherent) DESC')
            ->orderBy('code_adherent', 'desc')
            ->first();

        if ($lastEntreprise && $lastEntreprise->code_adherent) {
            $numberPart = preg_replace('/^ADH-?/i', '', $lastEntreprise->code_adherent);
            if (is_numeric($numberPart)) {
                $nextNumber = intval($numberPart) + 1;
                $padLength = max(3, strlen($numberPart));
                $code = 'ADH' . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                while (Entreprise::where('code_adherent', $code)->exists()) {
                    $nextNumber++;
                    $code = 'ADH' . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                }
                return $code;
            }
        }

        $count = Entreprise::count() + 1;
        $code = 'ADH' . str_pad($count, 3, '0', STR_PAD_LEFT);
        while (Entreprise::where('code_adherent', $code)->exists()) {
            $count++;
            $code = 'ADH' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }
        return $code;
    }
}
