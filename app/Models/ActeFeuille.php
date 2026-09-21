<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class ActeFeuille extends Model
{
    use MapsIntersecColumns;

    protected $table = 'AC_FEUIL';
    protected $primaryKey = 'ACCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'date_acte'          => 'DATEACTE',
        'type_acte'          => 'TYPEACTE',
        'description'        => 'DESACTE',
        'montant_ht'         => 'MONTACT_HT',
        'montant'            => 'MONTACT',
        'part_ipm'           => 'PARTIPM',
        'part_participant'   => 'PARTPARTICIPANT',
        'id_feuille'         => 'FECLEUNIK',
        'taux'               => 'TAUX2',
        'id_acte'            => 'IDActe',
        'forfait'            => 'Forfait',
        'plafond'            => 'Plafond',
    ];

    public function feuilleMaladie()
    {
        return $this->belongsTo(FeuilMa::class, 'FECLEUNIK', 'FECLEUNIK');
    }

    public function acte()
    {
        return $this->belongsTo(Acte::class, 'IDActe', 'IDActe');
    }
}
