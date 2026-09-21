<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class Specialite extends Model
{
    use MapsIntersecColumns;

    protected $table = 'SPECIALI';
    protected $primaryKey = 'SPCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];

    protected $columnMap = [
        'id'               => 'SPCLEUNIK',
        'id_specialite'    => 'SPCLEUNIK',
        'libelle'          => 'Specialite',
        'specialite'       => 'Specialite',
        'code'             => 'CodeSpecialite',
        'compte_comptable' => 'COMPTE_COMPTABLE',
        'dernum_praticien' => 'DERNUM_PRATICIEN',
    ];

    public function praticiens()
    {
        return $this->hasMany(Praticien::class, 'SPCLEUNIK', 'SPCLEUNIK');
    }
}
