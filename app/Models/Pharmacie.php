<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MapsIntersecColumns;

class Pharmacie extends Model
{
    use MapsIntersecColumns;

    protected $table = 'PHARMACI';
    protected $primaryKey = 'PHCLEUNIK';
    public $timestamps = false;
    protected $guarded = [];
    protected $keyType = 'int';

    protected $columnMap = [
        'id'               => 'PHCLEUNIK',
        'id_pharmacie'     => 'PHCLEUNIK',
        'nom'              => 'NOMPHARM',
        'adresse'          => 'ADRPHARM',
        'contact'          => 'CONTACTPHARM',
        'telephone'        => 'TELPHARM',
        'code_pharmacie'   => 'CODEPHARM',
        'compte_comptable' => 'COMPTE_COMPTABLE',
        'compte_general'   => 'COMPTE_GENERAL',
    ];

    public function facturesPharmacie()
    {
        return $this->hasMany(FacturePharmacie::class, 'PHCLEUNIK', 'PHCLEUNIK');
    }

    public function factures()
    {
        return $this->hasMany(FacturePharmacie::class, 'PHCLEUNIK', 'PHCLEUNIK');
    }

    public function commandes()
    {
        return $this->hasMany(CommandePharmacie::class, 'PHCLEUNIK', 'PHCLEUNIK');
    }

    public function conventions()
    {
        return $this->hasMany(Convention::class, 'id_pharmacie', 'PHCLEUNIK');
    }
}
