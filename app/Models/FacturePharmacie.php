<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class FacturePharmacie extends Model
{
    use MapsIntersecColumns;

    protected $table = 'FACT_PHA';
    protected $primaryKey = 'FACLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'id'               => 'FACLEUNIK',
        'id_facture'       => 'FACLEUNIK',
        'numero_facture'   => 'NUMERO_FACTURE',
        'date_facture'     => 'DATE_FACTURE',
        'montant'          => 'MONTANT_FACTURE',
        'montant_regle'    => 'MONTANT_REGLE',
        'montant_restant'  => 'MONTANT_RESTANT',
        'bon_a_payer'      => 'BON_A_PAYER',
        'date_echeance'    => 'DATE_ECHEANCE',
        'date_debut'       => 'DATEDEB',
        'date_fin'         => 'DATE_FIN',
        'id_pharmacie'     => 'PHCLEUNIK',
        'montant_ipm'      => 'MONTANT_IPM',
        'id_operateur'     => 'IDOPERATEUR',
        'date_saisie'      => 'Date_saisie',
        'heure'            => 'heure',
        'id_trcheque'      => 'IDTRCHEQUE',
    ];

    public function pharmacie()
    {
        return $this->belongsTo(Pharmacie::class, 'PHCLEUNIK', 'PHCLEUNIK');
    }

    public function getPartenaireAttribute()
    {
        return $this->pharmacie;
    }

    public function getStatutPaiementAttribute(): string
    {
        $restant = floatval($this->getAttribute('montant_restant') ?? 0);
        if ($restant <= 0) return 'payee';
        if ($restant < floatval($this->getAttribute('montant') ?? 0)) return 'partielle';
        return 'impayee';
    }
}
