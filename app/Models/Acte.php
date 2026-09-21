<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class Acte extends Model
{
    use MapsIntersecColumns;

    protected $table = 'Acte';
    protected $primaryKey = 'IDActe';
    public $timestamps = false;
    protected $guarded = [];

    protected $columnMap = [
        'libelle'  => 'Acte',
        'taux'     => 'Taux',
        'plafond'  => 'Plafond',
        'code'     => 'CodeActe',
        'montant'  => 'MONTANT',
        'forfait'  => 'Forfait',
    ];
}
