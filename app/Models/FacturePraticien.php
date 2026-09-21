<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class FacturePraticien extends Model
{
    use MapsIntersecColumns;

    protected $table = 'FACT_PRA';
    protected $primaryKey = 'F0CLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'id'               => 'F0CLEUNIK',
        'id_facture'       => 'F0CLEUNIK',
        'numero_facture'   => 'NUMERO_FACTURE',
        'date_facture'     => 'DATE_FACTURE',
        'montant'          => 'MONTANT_FACTURE',
        'montant_regle'    => 'MONTANT_REGLE',
        'montant_restant'  => 'MONTANT_RESTANT',
        'bon_a_payer'      => 'BON_A_PAYER',
        'date_echeance'    => 'DATE_ECHEANCE',
        'date_debut'       => 'DATEDEB',
        'date_fin'         => 'DATE_FIN',
        'id_praticien'     => 'PRCLEUNIK',
        'montant_ipm'      => 'MONTANT_IPM',
        'id_operateur'     => 'IDOPERATEUR',
        'date_saisie'      => 'Date_saisie',
        'heure'            => 'heure',
        'id_trcheque'      => 'IDTRCHEQUE',
    ];

    public function praticien()
    {
        return $this->belongsTo(Praticien::class, 'PRCLEUNIK', 'PRCLEUNIK');
    }

    public function getPartenaireAttribute()
    {
        return $this->praticien;
    }

    public function getStatutPaiementAttribute(): string
    {
        $restant = floatval($this->getAttribute('montant_restant') ?? 0);
        if ($restant <= 0) return 'payee';
        if ($restant < floatval($this->getAttribute('montant') ?? 0)) return 'partielle';
        return 'impayee';
    }

    public function getSoldeRestantAttribute()
    {
        return floatval($this->getAttribute('montant_restant') ?? 0);
    }

    public function reglements()
    {
        return $this->hasMany(ReglementPraticien::class, 'F0CLEUNIK', 'F0CLEUNIK');
    }
}
