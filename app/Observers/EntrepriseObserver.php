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
        if ($entreprise->wasChanged('statut') || $entreprise->wasChanged('ADACTIF')) {
            $rawOld = $entreprise->getOriginal('ADACTIF');
            $oldStatut = ($rawOld == 1 || $rawOld === '1') ? 'actif' : (($rawOld == 2 || $rawOld === '2') ? 'suspendu' : 'inactif');
            
            HistoriqueMouvement::create([
                'date_heure' => now(),
                'module' => 'Entreprise',
                'action' => 'Changement de statut',
                'description' => "Le statut de l'entreprise {$entreprise->raison_sociale} est passé de {$oldStatut} à {$entreprise->statut}.",
                'adresse_ip' => request()->ip(),
                'ancienne_valeur' => $oldStatut,
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
        $lastEntreprise = Entreprise::whereNotNull('CODEADHERANT')
            ->where('CODEADHERANT', 'like', 'ADH%')
            ->orderByRaw('LENGTH(CODEADHERANT) DESC')
            ->orderBy('CODEADHERANT', 'desc')
            ->first();

        if ($lastEntreprise && $lastEntreprise->code_adherent) {
            $numberPart = preg_replace('/^ADH-?/i', '', $lastEntreprise->code_adherent);
            if (is_numeric($numberPart)) {
                $nextNumber = intval($numberPart) + 1;
                $padLength = max(3, strlen($numberPart));
                $code = 'ADH' . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                $attempts = 0;
                while (Entreprise::where('CODEADHERANT', $code)->exists() && $attempts < 50) {
                    $nextNumber++;
                    $code = 'ADH' . str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
                    $attempts++;
                }
                return $code;
            }
        }

        $count = Entreprise::count() + 1;
        $code = 'ADH' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $attempts = 0;
        while (Entreprise::where('CODEADHERANT', $code)->exists() && $attempts < 50) {
            $count++;
            $code = 'ADH' . str_pad($count, 3, '0', STR_PAD_LEFT);
            $attempts++;
        }
        return $code;
    }
}
