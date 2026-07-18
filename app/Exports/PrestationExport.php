<?php

namespace App\Exports;

use App\Models\Prestation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PrestationExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $prestataireId;
    protected $dateDebut;
    protected $dateFin;

    public function __construct($prestataireId = null, $dateDebut = null, $dateFin = null)
    {
        $this->prestataireId = $prestataireId;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function query()
    {
        $query = Prestation::query()->with(['prestataire', 'typePrestation', 'demande.salarie', 'demande.ayantDroit']);

        if ($this->prestataireId) {
            $query->where('id_prestataire', $this->prestataireId);
        }

        if ($this->dateDebut) {
            $query->where('date_prestation', '>=', $this->dateDebut);
        }

        if ($this->dateFin) {
            $query->where('date_prestation', '<=', $this->dateFin);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID Prestation',
            'Date',
            'Prestataire',
            'Bénéficiaire',
            'Type Acte',
            'Montant Total (FCFA)',
            'Taux Prise en Charge (%)',
            'Reste à Charge (FCFA)',
            'Montant Pris en Charge (FCFA)'
        ];
    }

    public function map($prestation): array
    {
        $beneficiaire = 'Inconnu';
        if ($prestation->demande) {
            if ($prestation->demande->ayantDroit) {
                $beneficiaire = $prestation->demande->ayantDroit->prenom . ' ' . $prestation->demande->ayantDroit->nom . ' (Ayant-droit)';
            } elseif ($prestation->demande->salarie) {
                $beneficiaire = $prestation->demande->salarie->prenom . ' ' . $prestation->demande->salarie->nom;
            }
        }

        $montantPEC = $prestation->montant - $prestation->reste_a_charge;

        return [
            $prestation->id_prestation,
            $prestation->date_prestation ? $prestation->date_prestation->format('d/m/Y') : '',
            $prestation->prestataire->nom ?? '',
            $beneficiaire,
            $prestation->typePrestation->libelle ?? '',
            $prestation->montant,
            $prestation->taux_prise_charge,
            $prestation->reste_a_charge,
            $montantPEC
        ];
    }
}
