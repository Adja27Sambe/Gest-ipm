<?php

namespace App\Exports;

use App\Models\PaiementPrestataire;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PaiementsExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $dateDebut;
    protected $dateFin;
    protected $idPrestataire;

    public function __construct($idPrestataire = null, $dateDebut = null, $dateFin = null)
    {
        $this->idPrestataire = $idPrestataire;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function view(): View
    {
        $query = PaiementPrestataire::with(['facture.prestataire']);

        if ($this->idPrestataire) {
            $query->whereHas('facture', function ($q) {
                $q->where('id_prestataire', $this->idPrestataire);
            });
        }

        if ($this->dateDebut) {
            $query->where('date_paiement', '>=', $this->dateDebut);
        }

        if ($this->dateFin) {
            $query->where('date_paiement', '<=', $this->dateFin);
        }

        return view('exports.paiements', [
            'paiements' => $query->orderBy('date_paiement', 'desc')->get()
        ]);
    }
}
