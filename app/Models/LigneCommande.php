<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class LigneCommande extends Model
{
    use MapsIntersecColumns;

    protected $table = 'LIGCMD';
    protected $primaryKey = 'LICLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'designation'       => 'DESIGNATION',
        'quantite'          => 'Quantite',
        'prix_unitaire'     => 'PrixUnitaire',
        'taux'              => 'TAUXLIG',
        'part_ipm'          => 'PARTIPM',
        'part_participant'  => 'PARTPARTICIPANT',
        'id_commande'       => 'CMCLEUNIK',
        'montant'           => 'MONTANT',
        'date'              => 'Date',
    ];

    public function commande()
    {
        return $this->belongsTo(CommandePharmacie::class, 'CMCLEUNIK', 'CMCLEUNIK');
    }
}
