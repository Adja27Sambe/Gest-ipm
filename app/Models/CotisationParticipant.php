<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class CotisationParticipant extends Model
{
    use MapsIntersecColumns;

    protected $table = 'COT_PAR';
    protected $primaryKey = 'C0CLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'mois'            => 'MOISCOTISE',
        'annee'           => 'ANNEECOTISE',
        'date_cotisation' => 'DATECOTISE',
        'montant'         => 'MTCOTISE',
        'montant_base'    => 'MTBASE',
        'salaire_base'    => 'MTBASE',
        'id_participant'  => 'PACLEUNIK',
        'nb_mois'         => 'NBMOIS',
        'id_cotisation'   => 'COCLEUNIK',
    ];

    public function getPeriodeAttribute(): string
    {
        return str_pad($this->getAttribute('mois'), 2, '0', STR_PAD_LEFT) . '/' . $this->getAttribute('annee');
    }

    public function salarie()
    {
        return $this->belongsTo(Salarie::class, 'PACLEUNIK', 'IDPARTICIPANT');
    }

    public function cotisation()
    {
        return $this->belongsTo(Cotisation::class, 'COCLEUNIK', 'COCLEUNIK');
    }
}
