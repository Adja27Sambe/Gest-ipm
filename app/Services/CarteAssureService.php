<?php

namespace App\Services;

use App\Models\CarteAssure;
use App\Models\Salarie;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CarteAssureService
{
    /**
     * Génère un numéro de carte unique séquentiel
     */
    public function genererNumero(): string
    {
        $annee = date('Y');
        
        // Trouver la dernière carte de l'année pour incrémenter
        $derniereCarte = CarteAssure::where('numero_carte', 'like', "IPM-{$annee}-%")
            ->orderBy('id_carte', 'desc')
            ->lockForUpdate()
            ->first();

        $sequence = 1;
        if ($derniereCarte) {
            $parts = explode('-', $derniereCarte->numero_carte);
            if (count($parts) === 3) {
                $sequence = (int) $parts[2] + 1;
            }
        }

        return sprintf("IPM-%s-%04d", $annee, $sequence);
    }

    /**
     * Génère un QR Code encodant les informations de la carte
     */
    public function genererQrCode(string $numero, Salarie $salarie): string
    {
        $data = json_encode([
            'numero' => $numero,
            'matricule' => $salarie->matricule,
            'id_salarie' => $salarie->id_salarie,
        ]);

        // Génère le SVG brut sous forme de chaîne
        return (string) QrCode::size(200)->generate($data);
    }

    /**
     * Crée une nouvelle carte pour un salarié
     */
    public function creerCarte(Salarie $salarie): CarteAssure
    {
        return DB::transaction(function () use ($salarie) {
            $numero = $this->genererNumero();
            $qrCode = $this->genererQrCode($numero, $salarie);

            return CarteAssure::create([
                'id_salarie' => $salarie->id_salarie,
                'numero_carte' => $numero,
                'matricule' => $salarie->matricule,
                'date_emission' => now(),
                'qr_code' => $qrCode,
                'statut' => CarteAssure::STATUT_ACTIF,
            ]);
        });
    }

    /**
     * Réémet une carte (archive l'ancienne et en crée une nouvelle)
     */
    public function reemettreCarte(Salarie $salarie): CarteAssure
    {
        return DB::transaction(function () use ($salarie) {
            // Archiver l'ancienne carte active s'il y en a une
            $ancienneCarte = $salarie->carteAssure;
            if ($ancienneCarte) {
                $ancienneCarte->update(['statut' => CarteAssure::STATUT_ANNULEE]);
            }

            // Créer la nouvelle
            return $this->creerCarte($salarie);
        });
    }
}
